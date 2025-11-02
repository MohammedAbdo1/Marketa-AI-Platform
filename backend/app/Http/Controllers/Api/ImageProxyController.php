<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class ImageProxyController extends Controller
{
    /**
     * Proxy images from AI service with CORS headers
     */
    public function show($filename)
    {
        try {
            $aiServiceUrl = env('AI_SERVICE_URL', 'http://api:8001');
            $imageUrl = "{$aiServiceUrl}/static/images/{$filename}";
            
            $response = Http::timeout(30)->get($imageUrl);
            
            if ($response->successful()) {
                return response($response->body())
                    ->header('Content-Type', $response->header('Content-Type') ?? 'image/png')
                    ->header('Access-Control-Allow-Origin', '*')
                    ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
                    ->header('Cache-Control', 'public, max-age=86400');
            }
            
            return response()->json(['error' => 'Image not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load image'], 500);
        }
    }
}

