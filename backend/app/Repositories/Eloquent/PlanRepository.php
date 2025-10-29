<?php

namespace App\Repositories\Eloquent;

use App\Models\Plan;
use App\Repositories\BaseRepository;
use App\Repositories\Contracts\PlanRepositoryInterface;

class PlanRepository extends BaseRepository implements PlanRepositoryInterface
{
    public function __construct(Plan $model)
    {
        parent::__construct($model);
    }

    /**
     * Get active plans
     */
    public function getActivePlans()
    {
        return $this->model->active()->ordered()->get();
    }

    /**
     * Get popular plans
     */
    public function getPopularPlans()
    {
        return $this->model->popular()->active()->ordered()->get();
    }

    /**
     * Find plan by slug
     */
    public function findBySlug(string $slug)
    {
        return $this->model->where('slug', $slug)->first();
    }
}

