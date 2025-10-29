<?php

namespace App\Services;

use App\Models\DailyUsage;
use App\Models\User;
use Carbon\Carbon;

class DailyUsageService extends BaseService
{
    /**
     * Check if user has reached their daily limit
     */
    public function checkDailyLimit(int $userId): bool
    {
        $user = User::with('activeSubscription.plan')->findOrFail($userId);
        
        if (!$user->activeSubscription || !$user->activeSubscription->plan) {
            return false; // No subscription, deny access
        }
        
        $dailyLimit = $user->activeSubscription->plan->daily_requests_limit ?? 20;
        
        $usage = $this->getTodayUsage($userId);
        
        return $usage->requests_count < $dailyLimit;
    }

    /**
     * Get today's usage for a user
     */
    public function getTodayUsage(int $userId)
    {
        $user = User::findOrFail($userId);
        
        return DailyUsage::firstOrCreate(
            [
                'user_id' => $userId,
                'date' => today(),
            ],
            [
                'organization_id' => $user->organization_id,
                'requests_count' => 0,
                'tokens_used' => 0,
                'ai_cost' => 0,
            ]
        );
    }

    /**
     * Track AI usage
     */
    public function trackUsage(int $userId, int $tokensUsed, float $cost): void
    {
        $usage = $this->getTodayUsage($userId);
        
        $usage->increment('requests_count');
        $usage->increment('tokens_used', $tokensUsed);
        $usage->increment('ai_cost', $cost);
    }

    /**
     * Get remaining requests for today
     */
    public function getRemainingRequests(int $userId): int
    {
        $user = User::with('activeSubscription.plan')->findOrFail($userId);
        
        if (!$user->activeSubscription || !$user->activeSubscription->plan) {
            return 0;
        }
        
        $dailyLimit = $user->activeSubscription->plan->daily_requests_limit ?? 20;
        $usage = $this->getTodayUsage($userId);
        
        return max(0, $dailyLimit - $usage->requests_count);
    }

    /**
     * Get usage statistics for a user
     */
    public function getUsageStats(int $userId, int $days = 30)
    {
        $startDate = Carbon::now()->subDays($days);
        
        return DailyUsage::where('user_id', $userId)
            ->where('date', '>=', $startDate)
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Reset daily usage (to be called by cron at midnight)
     */
    public function resetDailyUsage(): void
    {
        // This is handled automatically by creating new records per day
        // But we can clean up old records here
        
        $thirtyDaysAgo = Carbon::now()->subDays(90);
        
        DailyUsage::where('date', '<', $thirtyDaysAgo)->delete();
    }

    /**
     * Get usage summary
     */
    public function getUsageSummary(int $userId)
    {
        $today = $this->getTodayUsage($userId);
        $user = User::with('activeSubscription.plan')->findOrFail($userId);
        
        $dailyLimit = $user->activeSubscription->plan->daily_requests_limit ?? 20;
        
        return [
            'today' => [
                'requests_count' => $today->requests_count,
                'tokens_used' => $today->tokens_used,
                'cost' => $today->ai_cost,
                'limit' => $dailyLimit,
                'remaining' => max(0, $dailyLimit - $today->requests_count),
                'percentage' => min(100, ($today->requests_count / $dailyLimit) * 100),
            ],
            'this_week' => $this->getWeeklyUsage($userId),
            'this_month' => $this->getMonthlyUsage($userId),
        ];
    }

    /**
     * Get weekly usage
     */
    private function getWeeklyUsage(int $userId)
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        
        return DailyUsage::where('user_id', $userId)
            ->where('date', '>=', $startOfWeek)
            ->selectRaw('SUM(requests_count) as total_requests, SUM(tokens_used) as total_tokens, SUM(ai_cost) as total_cost')
            ->first();
    }

    /**
     * Get monthly usage
     */
    private function getMonthlyUsage(int $userId)
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        
        return DailyUsage::where('user_id', $userId)
            ->where('date', '>=', $startOfMonth)
            ->selectRaw('SUM(requests_count) as total_requests, SUM(tokens_used) as total_tokens, SUM(ai_cost) as total_cost')
            ->first();
    }
}

