<?php

namespace App\Services;

use App\Repositories\Contracts\PlanRepositoryInterface;
use Illuminate\Support\Str;

class PlanService extends BaseService
{
    protected PlanRepositoryInterface $planRepository;

    public function __construct(PlanRepositoryInterface $planRepository)
    {
        $this->planRepository = $planRepository;
    }

    /**
     * Get all plans
     */
    public function getAllPlans()
    {
        return $this->planRepository->all();
    }

    /**
     * Get available (active) plans
     */
    public function getAvailablePlans()
    {
        return $this->planRepository->getActivePlans();
    }

    /**
     * Get popular plans
     */
    public function getPopularPlans()
    {
        return $this->planRepository->getPopularPlans();
    }

    /**
     * Get plan by ID
     */
    public function getPlanById($id)
    {
        return $this->planRepository->findById($id);
    }

    /**
     * Get plan details
     */
    public function getPlanDetails($id)
    {
        return $this->planRepository->findById($id);
    }

    /**
     * Create new plan
     */
    public function createPlan(array $data)
    {
        // Generate slug if not provided
        if (!isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name_en']);
        }

        $plan = $this->planRepository->create($data);

        $this->logActivity('Plan created', $plan, $data);

        return $plan;
    }

    /**
     * Update plan
     */
    public function updatePlan($id, array $data)
    {
        if (isset($data['name_en']) && !isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name_en']);
        }

        $plan = $this->planRepository->update($id, $data);

        $this->logActivity('Plan updated', $plan, $data);

        return $plan;
    }

    /**
     * Delete plan
     */
    public function deletePlan($id)
    {
        $plan = $this->planRepository->findById($id);
        
        if ($plan) {
            $this->logActivity('Plan deleted', $plan);
            return $this->planRepository->delete($id);
        }

        return false;
    }
}

