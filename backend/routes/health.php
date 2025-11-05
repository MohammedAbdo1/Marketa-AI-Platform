<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use App\Services\PythonAIService;

// Comprehensive health check route
Route::get('/health', function () {
    $checks = [
        'overall_status' => 'healthy',
        'service' => 'Marketa AI Platform',
        'timestamp' => now()->toISOString(),
        'components' => []
    ];
    
    // Check Database
    try {
        $start = microtime(true);
        DB::connection()->getPdo();
        $queryTime = round((microtime(true) - $start) * 1000, 2);
        
        $checks['components']['database'] = [
            'status' => 'healthy',
            'response_time_ms' => $queryTime,
            'connection' => DB::connection()->getDatabaseName()
        ];
    } catch (Exception $e) {
        $checks['components']['database'] = [
            'status' => 'unhealthy',
            'error' => $e->getMessage()
        ];
        $checks['overall_status'] = 'degraded';
    }
    
    // Check Redis
    try {
        $start = microtime(true);
        Redis::ping();
        $latency = round((microtime(true) - $start) * 1000, 2);
        
        $info = Redis::info();
        $memoryUsed = isset($info['used_memory_human']) ? $info['used_memory_human'] : 'unknown';
        
        $checks['components']['redis'] = [
            'status' => 'healthy',
            'latency_ms' => $latency,
            'memory_used' => $memoryUsed,
            'connected_clients' => $info['connected_clients'] ?? 0
        ];
    } catch (Exception $e) {
        $checks['components']['redis'] = [
            'status' => 'unhealthy',
            'error' => $e->getMessage()
        ];
        $checks['overall_status'] = 'degraded';
    }
    
    // Check Queue Workers
    try {
        $defaultQueue = Redis::llen('queues:default');
        $campaignQueue = Redis::llen('queues:campaign-generation');
        $lowPriorityQueue = Redis::llen('queues:low-priority');
        
        $totalJobs = $defaultQueue + $campaignQueue + $lowPriorityQueue;
        $queueStatus = $totalJobs > 1000 ? 'warning' : 'healthy';
        
        $checks['components']['queue'] = [
            'status' => $queueStatus,
            'default_queue' => $defaultQueue,
            'campaign_queue' => $campaignQueue,
            'low_priority_queue' => $lowPriorityQueue,
            'total_pending' => $totalJobs
        ];
        
        if ($queueStatus === 'warning') {
            $checks['overall_status'] = 'degraded';
        }
    } catch (Exception $e) {
        $checks['components']['queue'] = [
            'status' => 'unhealthy',
            'error' => $e->getMessage()
        ];
        $checks['overall_status'] = 'degraded';
    }
    
    // Check Cache Performance
    try {
        $cacheKey = 'health_check_test_' . time();
        $start = microtime(true);
        cache()->put($cacheKey, 'test', 1);
        $writeTime = round((microtime(true) - $start) * 1000, 2);
        
        $start = microtime(true);
        cache()->get($cacheKey);
        $readTime = round((microtime(true) - $start) * 1000, 2);
        
        cache()->forget($cacheKey);
        
        $checks['components']['cache'] = [
            'status' => 'healthy',
            'driver' => config('cache.default'),
            'write_time_ms' => $writeTime,
            'read_time_ms' => $readTime
        ];
    } catch (Exception $e) {
        $checks['components']['cache'] = [
            'status' => 'unhealthy',
            'error' => $e->getMessage()
        ];
    }
    
    // Check Disk Space
    try {
        $storagePath = storage_path();
        $totalSpace = disk_total_space($storagePath);
        $freeSpace = disk_free_space($storagePath);
        $usedPercentage = round((($totalSpace - $freeSpace) / $totalSpace) * 100, 2);
        
        $diskStatus = $usedPercentage > 90 ? 'warning' : 'healthy';
        
        $checks['components']['disk'] = [
            'status' => $diskStatus,
            'total_gb' => round($totalSpace / 1024 / 1024 / 1024, 2),
            'free_gb' => round($freeSpace / 1024 / 1024 / 1024, 2),
            'used_percentage' => $usedPercentage
        ];
        
        if ($diskStatus === 'warning') {
            $checks['overall_status'] = 'degraded';
        }
    } catch (Exception $e) {
        $checks['components']['disk'] = [
            'status' => 'unhealthy',
            'error' => $e->getMessage()
        ];
    }
    
    $httpCode = match($checks['overall_status']) {
        'healthy' => 200,
        'degraded' => 200, // Still operational but with warnings
        default => 503
    };
    
    return response()->json($checks, $httpCode);
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

