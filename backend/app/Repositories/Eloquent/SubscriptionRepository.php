<?php

namespace App\Repositories\Eloquent;

use App\Models\Subscription;
use App\Repositories\BaseRepository;
use App\Repositories\Contracts\SubscriptionRepositoryInterface;
use Carbon\Carbon;

class SubscriptionRepository extends BaseRepository implements SubscriptionRepositoryInterface
{
    public function __construct(Subscription $model)
    {
        parent::__construct($model);
    }

    /**
     * Get active subscription for a user
     */
    public function getActiveSubscription($userId)
    {
        return $this->model->where('user_id', $userId)
            ->active()
            ->with(['plan'])
            ->first();
    }

    /**
     * Get expiring subscriptions (within next 7 days)
     */
    public function getExpiringSubscriptions()
    {
        return $this->model->active()
            ->where('end_date', '<=', Carbon::now()->addDays(7))
            ->where('end_date', '>=', Carbon::now())
            ->get();
    }
}

