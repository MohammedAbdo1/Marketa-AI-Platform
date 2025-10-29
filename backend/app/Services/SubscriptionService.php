<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\PlanRepositoryInterface;
use App\Repositories\Contracts\SubscriptionRepositoryInterface;
use Carbon\Carbon;

class SubscriptionService extends BaseService
{
    protected SubscriptionRepositoryInterface $subscriptionRepository;
    protected PlanRepositoryInterface $planRepository;

    public function __construct(
        SubscriptionRepositoryInterface $subscriptionRepository,
        PlanRepositoryInterface $planRepository
    ) {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->planRepository = $planRepository;
    }

    /**
     * Subscribe user to a plan
     */
    public function subscribe(User $user, $planId, array $data = [])
    {
        $plan = $this->planRepository->findById($planId);

        if (!$plan || !$plan->is_active) {
            throw new \Exception('Plan not found or not active');
        }

        // Cancel existing active subscription if any
        $existingSubscription = $this->subscriptionRepository->getActiveSubscription($user->id);
        if ($existingSubscription) {
            $this->cancel($existingSubscription->uuid);
        }

        $subscriptionData = [
            'user_id' => $user->id,
            'organization_id' => $user->organization_id,
            'plan_id' => $planId,
            'status' => 'active',
            'start_date' => now(),
            'end_date' => isset($data['billing_cycle']) && $data['billing_cycle'] === 'yearly'
                ? now()->addYear()
                : now()->addMonth(),
            'auto_renew' => $data['auto_renew'] ?? true,
            'payment_method' => $data['payment_method'] ?? null,
            'last_payment_at' => now(),
        ];

        $subscription = $this->subscriptionRepository->create($subscriptionData);

        $this->logActivity('User subscribed to plan', $subscription);

        return $subscription->load(['plan', 'user']);
    }

    /**
     * Cancel subscription
     */
    public function cancel(string $subscriptionUuid)
    {
        $subscription = $this->subscriptionRepository->updateByUuid($subscriptionUuid, [
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        $this->logActivity('Subscription cancelled', $subscription);

        return $subscription;
    }

    /**
     * Renew subscription
     */
    public function renew(string $subscriptionUuid)
    {
        $subscription = $this->subscriptionRepository->findByUuid($subscriptionUuid);

        if (!$subscription) {
            throw new \Exception('Subscription not found');
        }

        $endDate = Carbon::parse($subscription->end_date);
        $newEndDate = $endDate->addMonth();

        $subscription = $this->subscriptionRepository->updateByUuid($subscriptionUuid, [
            'status' => 'active',
            'end_date' => $newEndDate,
            'last_payment_at' => now(),
        ]);

        $this->logActivity('Subscription renewed', $subscription);

        return $subscription;
    }

    /**
     * Get user's active subscription
     */
    public function getActiveSubscription($userId)
    {
        return $this->subscriptionRepository->getActiveSubscription($userId);
    }

    /**
     * Get expiring subscriptions
     */
    public function getExpiringSubscriptions()
    {
        return $this->subscriptionRepository->getExpiringSubscriptions();
    }
}

