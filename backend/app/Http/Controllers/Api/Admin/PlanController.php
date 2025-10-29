<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlanResource;
use App\Services\PlanService;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    protected PlanService $planService;

    public function __construct(PlanService $planService)
    {
        $this->planService = $planService;
    }

    /**
     * Display a listing of plans
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search', '');
        $sort = $request->get('sort', 'sort_order');
        $direction = $request->get('direction', 'asc');
        $status = $request->get('status', '');
        $popular = $request->get('popular', '');

        $query = \App\Models\Plan::query()
            ->withCount(['subscriptions', 'activeSubscriptions']);

        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        // Popular filter
        if ($popular === 'yes') {
            $query->where('is_popular', true);
        } elseif ($popular === 'no') {
            $query->where('is_popular', false);
        }

        // Sort
        $query->orderBy($sort, $direction);

        $plans = $query->paginate($perPage);

        return PlanResource::collection($plans);
    }

    /**
     * Store a newly created plan
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:plans',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'tokens_limit' => 'required|integer|min:0',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
            'sort_order' => 'integer',
        ]);

        try {
            $plan = $this->planService->createPlan($validated);

            return response()->json([
                'message' => 'Plan created successfully',
                'data' => new PlanResource($plan),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Plan creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified plan
     */
    public function show($id)
    {
        $plan = $this->planService->getPlanById($id);

        if (!$plan) {
            return response()->json([
                'message' => 'Plan not found',
            ], 404);
        }

        return response()->json([
            'data' => new PlanResource($plan),
        ]);
    }

    /**
     * Update the specified plan
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name_ar' => 'sometimes|string|max:255',
            'name_en' => 'sometimes|string|max:255',
            'slug' => 'nullable|string|unique:plans,slug,' . $id,
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'price_monthly' => 'sometimes|numeric|min:0',
            'price_yearly' => 'sometimes|numeric|min:0',
            'tokens_limit' => 'sometimes|integer|min:0',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
            'sort_order' => 'integer',
        ]);

        try {
            $plan = $this->planService->updatePlan($id, $validated);

            if (!$plan) {
                return response()->json([
                    'message' => 'Plan not found',
                ], 404);
            }

            return response()->json([
                'message' => 'Plan updated successfully',
                'data' => new PlanResource($plan),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Plan update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified plan
     */
    public function destroy($id)
    {
        try {
            $deleted = $this->planService->deletePlan($id);

            if (!$deleted) {
                return response()->json([
                    'message' => 'Plan not found',
                ], 404);
            }

            return response()->json([
                'message' => 'Plan deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Plan deletion failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

