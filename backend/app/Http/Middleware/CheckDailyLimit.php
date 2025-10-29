<?php

namespace App\Http\Middleware;

use App\Services\DailyUsageService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDailyLimit
{
    protected DailyUsageService $dailyUsageService;

    public function __construct(DailyUsageService $dailyUsageService)
    {
        $this->dailyUsageService = $dailyUsageService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Check if user has reached daily limit
        if (!$this->dailyUsageService->checkDailyLimit($user->id)) {
            $remaining = $this->dailyUsageService->getRemainingRequests($user->id);
            $usage = $this->dailyUsageService->getTodayUsage($user->id);
            
            return response()->json([
                'message' => 'Daily request limit exceeded',
                'error' => 'DAILY_LIMIT_EXCEEDED',
                'data' => [
                    'requests_used' => $usage->requests_count,
                    'limit' => $usage->requests_count, // They've hit the limit
                    'remaining' => $remaining,
                    'resets_at' => now()->endOfDay()->toIso8601String(),
                ],
            ], 429);
        }

        return $next($request);
    }
}
