<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProfileController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Get minimal user info (fast, no relationships)
     * Used for authentication checks and UI display
     */
    public function me(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'user' => [
                'uuid' => $user->uuid,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'status' => $user->status,
            ],
        ]);
    }

    /**
     * Get user profile (full with relationships)
     * Used for profile page with complete information
     * Cached for 5 minutes for performance
     */
    public function show(Request $request)
    {
        $userId = $request->user()->id;
        $cacheKey = "user_profile_{$userId}";
        
        // Cache the profile for 5 minutes (300 seconds)
        $user = Cache::remember($cacheKey, 300, function () use ($request) {
            return $request->user()->load([
                'roles',
                'permissions',
                'organization',
                'activeSubscription.plan'
            ]);
        });
        
        return response()->json([
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $request->user()->id,
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        try {
            $user = $this->userService->updateUser($request->user()->uuid, $validated);
            
            // Invalidate profile cache after update
            $cacheKey = "user_profile_{$request->user()->id}";
            Cache::forget($cacheKey);

            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => new UserResource($user),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Profile update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

