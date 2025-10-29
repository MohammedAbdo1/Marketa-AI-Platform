<?php

namespace App\Services;

use App\Models\User;
use App\Models\UsageLog;
use Carbon\Carbon;

class UsageTrackingService extends BaseService
{
    /**
     * Track usage for a user
     */
    public function trackUsage(User $user, int $tokensUsed, float $costUsd)
    {
        $currentCycle = $this->getCurrentBillingCycle($user);

        $usageLog = UsageLog::updateOrCreate(
            [
                'user_id' => $user->id,
                'organization_id' => $user->organization_id,
                'subscription_id' => $user->activeSubscription->id ?? null,
                'billing_cycle_start' => $currentCycle['start'],
                'billing_cycle_end' => $currentCycle['end'],
            ],
            [
                'total_tokens_used' => \DB::raw("total_tokens_used + $tokensUsed"),
                'total_requests' => \DB::raw('total_requests + 1'),
                'total_cost_usd' => \DB::raw("total_cost_usd + $costUsd"),
            ]
        );

        $this->logActivity('Usage tracked', $usageLog, [
            'tokens_used' => $tokensUsed,
            'cost' => $costUsd,
        ]);

        return $usageLog;
    }

    /**
     * Check if user has exceeded their limit
     */
    public function checkLimit(User $user)
    {
        $subscription = $user->activeSubscription;

        if (!$subscription || !$subscription->plan) {
            throw new \Exception('No active subscription found');
        }

        $currentUsage = $this->getCurrentUsage($user);
        $limit = $subscription->plan->tokens_limit;

        if ($currentUsage['total_tokens_used'] >= $limit) {
            throw new \Exception('Token limit exceeded for your plan');
        }

        return true;
    }

    /**
     * Get current usage for a user
     */
    public function getCurrentUsage(User $user)
    {
        $currentCycle = $this->getCurrentBillingCycle($user);

        $usageLog = UsageLog::where('user_id', $user->id)
            ->where('billing_cycle_start', $currentCycle['start'])
            ->where('billing_cycle_end', $currentCycle['end'])
            ->first();

        if (!$usageLog) {
            return [
                'total_tokens_used' => 0,
                'total_requests' => 0,
                'total_cost_usd' => 0,
                'limit' => $user->activeSubscription->plan->tokens_limit ?? 0,
                'remaining' => $user->activeSubscription->plan->tokens_limit ?? 0,
            ];
        }

        $limit = $user->activeSubscription->plan->tokens_limit ?? 0;

        return [
            'total_tokens_used' => $usageLog->total_tokens_used,
            'total_requests' => $usageLog->total_requests,
            'total_cost_usd' => $usageLog->total_cost_usd,
            'limit' => $limit,
            'remaining' => max(0, $limit - $usageLog->total_tokens_used),
        ];
    }

    /**
     * Get current billing cycle dates
     */
    protected function getCurrentBillingCycle(User $user)
    {
        $subscription = $user->activeSubscription;

        if (!$subscription) {
            // Default to current month
            return [
                'start' => Carbon::now()->startOfMonth(),
                'end' => Carbon::now()->endOfMonth(),
            ];
        }

        $startDate = Carbon::parse($subscription->start_date);
        $now = Carbon::now();

        // Calculate billing cycle start (same day each month as subscription start)
        $cycleStart = $startDate->copy()->setDate($now->year, $now->month, $startDate->day);
        
        if ($cycleStart->greaterThan($now)) {
            $cycleStart->subMonth();
        }

        $cycleEnd = $cycleStart->copy()->addMonth()->subSecond();

        return [
            'start' => $cycleStart,
            'end' => $cycleEnd,
        ];
    }
}

