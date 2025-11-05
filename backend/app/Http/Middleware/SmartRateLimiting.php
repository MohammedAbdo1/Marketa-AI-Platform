<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class SmartRateLimiting
{
    /**
     * Handle an incoming request with smart rate limiting.
     * 
     * Different limits for different endpoint types:
     * - Auth: 5 requests/min
     * - Read operations: 60 requests/min
     * - Write operations: 20 requests/min
     * - AI operations: 10 requests/min
     */
    public function handle(Request $request, Closure $next, string $type = 'default'): Response
    {
        $user = $request->user();
        $key = $this->resolveRequestKey($request, $user, $type);
        
        // Get limits based on type
        [$maxAttempts, $decayMinutes] = $this->getLimits($type);
        
        // Check if too many attempts
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($key);
            
            return response()->json([
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $retryAfter,
            ], 429)
                ->withHeaders([
                    'X-RateLimit-Limit' => $maxAttempts,
                    'X-RateLimit-Remaining' => 0,
                    'Retry-After' => $retryAfter,
                    'X-RateLimit-Reset' => now()->addSeconds($retryAfter)->timestamp,
                ]);
        }
        
        // Increment attempts
        RateLimiter::hit($key, $decayMinutes * 60);
        
        $response = $next($request);
        
        // Add rate limit headers
        $remaining = max(0, $maxAttempts - RateLimiter::attempts($key));
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $remaining,
        ]);
        
        return $response;
    }
    
    /**
     * Resolve the rate limiting key for the request.
     */
    protected function resolveRequestKey(Request $request, $user, string $type): string
    {
        if ($user) {
            return "rate_limit:{$type}:user:{$user->id}";
        }
        
        // For guests, use IP address
        return "rate_limit:{$type}:ip:{$request->ip()}";
    }
    
    /**
     * Get rate limiting configuration based on type.
     * 
     * @return array [maxAttempts, decayMinutes]
     */
    protected function getLimits(string $type): array
    {
        return match($type) {
            'auth' => [500, 1],      // 5 requests per minute
            'read' => [600, 1],     // 60 requests per minute
            'write' => [200, 1],    // 20 requests per minute
            'ai' => [1000, 1],       // 10 requests per minute
            'strict' => [3000, 1],    // 3 requests per minute (very sensitive endpoints)
            default => [3000, 1],    // 30 requests per minute
        };
    }
}

