<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of users (all users)
     */
    public function index(Request $request)
    {
        $query = \App\Models\User::query();

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        // Sorting
        $sortField = $request->get('sort', 'id');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Load relationships
        $query->with(['roles', 'organization']);

        // Pagination
        $perPage = $request->get('per_page', 10);
        $users = $query->paginate($perPage);

        return UserResource::collection($users);
    }

    /**
     * Display a listing of platform admins only
     */
    public function admins(Request $request)
    {
        $query = \App\Models\User::role('admin');

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Sorting
        $sortField = $request->get('sort', 'id');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Load relationships
        $query->with(['roles', 'organization']);

        // Pagination
        $perPage = $request->get('per_page', 10);
        $admins = $query->paginate($perPage);


        return UserResource::collection($admins);
    }

    /**
     * Display a listing of customers only (non-admin users)
     */
    public function customers(Request $request)
    {
        $query = \App\Models\User::whereDoesntHave('roles', function ($q) {
            $q->where('name', 'admin');
        });

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by plan
        if ($request->has('plan_id') && $request->plan_id) {
            $query->whereHas('activeSubscription', function ($q) use ($request) {
                $q->where('plan_id', $request->plan_id);
            });
        }

        // Filter by subscription status
        if ($request->has('subscription_status') && $request->subscription_status) {
            $query->whereHas('activeSubscription', function ($q) use ($request) {
                $q->where('status', $request->subscription_status);
            });
        }

        // Sorting
        $sortField = $request->get('sort', 'id');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Load relationships
        $query->with(['roles', 'organization', 'activeSubscription.plan']);

        // Pagination
        $perPage = $request->get('per_page', 10);
        $customers = $query->paginate($perPage);

        return UserResource::collection($customers);
    }

    /**
     * Get customer details (comprehensive)
     */
    public function details(string $uuid)
    {
        $customer = \App\Models\User::whereUuid($uuid)
            ->with([
                'roles',
                'organization',
                'activeSubscription.plan',
                // 'campaigns', // TODO: Add when Campaign model is ready
                // 'posts', // TODO: Add when Post model is ready
                // 'transactions', // TODO: Add when Transaction model is ready
            ])
            ->firstOrFail();

        return response()->json([
            'data' => new UserResource($customer),
        ]);
    }

    /**
     * Store a newly created user
     */
    public function store(StoreUserRequest $request)
    {
        try {
            $user = $this->userService->createUser($request->validated());

            return response()->json([
                'message' => 'User created successfully',
                'data' => new UserResource($user),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'User creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified user
     */
    public function show(string $uuid)
    {
        $user = $this->userService->getUserByUuid($uuid);

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Update the specified user
     */
    public function update(UpdateUserRequest $request, string $uuid)
    {
        try {
            $user = $this->userService->updateUser($uuid, $request->validated());

            if (!$user) {
                return response()->json([
                    'message' => 'User not found',
                ], 404);
            }

            return response()->json([
                'message' => 'User updated successfully',
                'data' => new UserResource($user),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'User update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified user
     */
    public function destroy(string $uuid)
    {
        try {
            $deleted = $this->userService->deleteUser($uuid);

            if (!$deleted) {
                return response()->json([
                    'message' => 'User not found',
                ], 404);
            }

            return response()->json([
                'message' => 'User deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'User deletion failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update user status
     */
    public function updateStatus(Request $request, string $uuid)
    {
        $request->validate([
            'status' => 'required|in:active,inactive,suspended',
        ]);

        try {
            $user = \App\Models\User::whereUuid($uuid)->firstOrFail();
            $user->update(['status' => $request->status]);

            return response()->json([
                'message' => 'Status updated successfully',
                'data' => new UserResource($user),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Status update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

