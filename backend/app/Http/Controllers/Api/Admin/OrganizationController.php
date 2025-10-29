<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrganizationResource;
use App\Services\OrganizationService;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    protected OrganizationService $organizationService;

    public function __construct(OrganizationService $organizationService)
    {
        $this->organizationService = $organizationService;
    }

    /**
     * Display a listing of organizations
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search', '');
        $sort = $request->get('sort', 'id');
        $direction = $request->get('direction', 'desc');
        $status = $request->get('status', '');

        $query = \App\Models\Organization::query()
            ->with(['owner'])
            ->withCount(['users', 'activeSubscriptions']);

        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhereHas('owner', function ($q) use ($search) {
                        $q->where('email', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter
        if ($status) {
            $query->where('status', $status);
        }

        // Sort
        $query->orderBy($sort, $direction);

        $organizations = $query->paginate($perPage);

        return OrganizationResource::collection($organizations);
    }

    /**
     * Display the specified organization
     */
    public function show(string $uuid)
    {
        $organization = $this->organizationService->getOrganizationByUuid($uuid);

        if (!$organization) {
            return response()->json([
                'message' => 'Organization not found',
            ], 404);
        }

        return response()->json([
            'data' => new OrganizationResource($organization),
        ]);
    }

    /**
     * Update the specified organization
     */
    public function update(Request $request, string $uuid)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'nullable|string|unique:organizations,slug',
            'logo' => 'nullable|string',
            'website' => 'nullable|url',
            'status' => 'sometimes|in:active,suspended,trial',
            'settings' => 'nullable|array',
        ]);

        try {
            $organization = $this->organizationService->updateOrganization($uuid, $validated);

            if (!$organization) {
                return response()->json([
                    'message' => 'Organization not found',
                ], 404);
            }

            return response()->json([
                'message' => 'Organization updated successfully',
                'data' => new OrganizationResource($organization),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Organization update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified organization
     */
    public function destroy(string $uuid)
    {
        try {
            $deleted = $this->organizationService->deleteOrganization($uuid);

            if (!$deleted) {
                return response()->json([
                    'message' => 'Organization not found',
                ], 404);
            }

            return response()->json([
                'message' => 'Organization deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Organization deletion failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

