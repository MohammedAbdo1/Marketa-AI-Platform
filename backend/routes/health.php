<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use App\Services\PythonAIService;

// Health check routes
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'service' => 'Laravel Backend',
        'timestamp' => now()->toISOString()
    ]);
});

Route::get('/health/queue', function () {
    try {
        // Check Redis connection
        Redis::ping();
        
        // Check queue status
        $queueLength = Redis::llen('queues:default');
        $campaignQueueLength = Redis::llen('queues:campaign-generation');
        
        return response()->json([
            'status' => 'healthy',
            'service' => 'Queue System',
            'redis_connected' => true,
            'default_queue_length' => $queueLength,
            'campaign_queue_length' => $campaignQueueLength,
            'timestamp' => now()->toISOString()
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status' => 'unhealthy',
            'service' => 'Queue System',
            'error' => $e->getMessage(),
            'timestamp' => now()->toISOString()
        ], 500);
    }
});

Route::get('/health/database', function () {
    try {
        DB::connection()->getPdo();
        
        return response()->json([
            'status' => 'healthy',
            'service' => 'Database',
            'connection' => 'connected',
            'timestamp' => now()->toISOString()
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status' => 'unhealthy',
            'service' => 'Database',
            'error' => $e->getMessage(),
            'timestamp' => now()->toISOString()
        ], 500);
    }
});

Route::get('/health/ai-service', function () {
    try {
        $aiService = app(PythonAIService::class);
        $health = $aiService->checkHealth();
        
        return response()->json([
            'status' => $health['status'] === 'healthy' ? 'healthy' : 'unhealthy',
            'service' => 'AI Service',
            'ai_service_status' => $health,
            'timestamp' => now()->toISOString()
        ], $health['status'] === 'healthy' ? 200 : 503);
    } catch (Exception $e) {
        return response()->json([
            'status' => 'unhealthy',
            'service' => 'AI Service',
            'error' => $e->getMessage(),
            'timestamp' => now()->toISOString()
        ], 500);
    }
});

