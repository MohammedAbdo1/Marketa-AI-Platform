<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompressResponse
{
    /**
     * Handle an incoming request and compress the response.
     * 
     * This middleware enables Gzip compression for JSON responses
     * to reduce bandwidth usage and improve performance.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only compress if client accepts gzip encoding
        $acceptEncoding = $request->header('Accept-Encoding', '');
        
        if (!str_contains($acceptEncoding, 'gzip')) {
            return $response;
        }

        // Only compress text-based content types
        $contentType = $response->headers->get('Content-Type', '');
        $compressibleTypes = [
            'text/html',
            'text/plain',
            'text/css',
            'text/javascript',
            'application/json',
            'application/javascript',
            'application/xml',
            'text/xml',
        ];

        $shouldCompress = false;
        foreach ($compressibleTypes as $type) {
            if (str_contains($contentType, $type)) {
                $shouldCompress = true;
                break;
            }
        }

        if (!$shouldCompress) {
            return $response;
        }

        // Get the original content
        $content = $response->getContent();
        
        // Don't compress if content is too small (overhead not worth it)
        if (strlen($content) < 1024) {
            return $response;
        }

        // Compress the content
        $compressed = gzencode($content, 6); // Level 6 is good balance of speed/size
        
        if ($compressed === false) {
            return $response;
        }

        // Update the response
        $response->setContent($compressed);
        $response->headers->set('Content-Encoding', 'gzip');
        $response->headers->set('Content-Length', strlen($compressed));
        $response->headers->set('Vary', 'Accept-Encoding', false);

        return $response;
    }
}

