<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DailyUsageService;
use Illuminate\Http\Request;

class UsageController extends Controller
{
    protected DailyUsageService $dailyUsageService;

    public function __construct(DailyUsageService $dailyUsageService)
    {
        $this->dailyUsageService = $dailyUsageService;
    }

    /**
     * Get today's usage statistics.
     */
    public function daily(Request $request)
    {
        $userId = $request->user()->id;
        $usage = $this->dailyUsageService->getTodayUsage($userId);
        $remaining = $this->dailyUsageService->getRemainingRequests($userId);

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $usage->date,
                'requests_count' => $usage->requests_count,
                'tokens_used' => $usage->tokens_used,
                'cost' => $usage->ai_cost,
                'remaining_requests' => $remaining,
                'resets_at' => now()->endOfDay()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get usage history.
     */
    public function history(Request $request)
    {
        $validated = $request->validate([
            'days' => 'sometimes|integer|min:1|max:90',
        ]);

        $userId = $request->user()->id;
        $days = $validated['days'] ?? 30;
        
        $history = $this->dailyUsageService->getUsageStats($userId, $days);

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    /**
     * Get comprehensive usage summary.
     */
    public function summary(Request $request)
    {
        $userId = $request->user()->id;
        $summary = $this->dailyUsageService->getUsageSummary($userId);

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }
}
