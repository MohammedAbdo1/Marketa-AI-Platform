<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use App\Services\Auth\EmailChangeVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;

class ProfileController extends Controller
{
    protected UserService $userService;
    protected EmailChangeVerificationService $emailChangeVerificationService;

    public function __construct(UserService $userService, EmailChangeVerificationService $emailChangeVerificationService)
    {
        $this->userService = $userService;
        $this->emailChangeVerificationService = $emailChangeVerificationService;
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
                'has_password' => ! empty($user->password),
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
            'password' => ['nullable', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
            'current_password' => 'nullable|string',
            'verification_token' => 'nullable|string',
        ]);

        $user = $request->user();
        $emailUpdated = false;
        $passwordUpdated = false;

        try {
            $payload = $validated;

            if (isset($payload['email']) && $payload['email'] !== $user->email) {
                if (empty($payload['verification_token'])) {
                    throw ValidationException::withMessages([
                        'verification_token' => [__('Verification token is required to change email.')],
                    ]);
                }

                $user = $this->emailChangeVerificationService
                    ->updateEmail($user, $payload['verification_token'], $payload['email']);

                $emailUpdated = true;
                unset($payload['email']);
            }

            unset($payload['verification_token']);

            if (isset($payload['password'])) {
                $currentPasswordInput = $request->input('current_password');

                if ($user->password) {
                    if (blank($currentPasswordInput)) {
                        throw ValidationException::withMessages([
                            'current_password' => [__('Current password is required.')],
                        ]);
                    }

                    if (! Hash::check($currentPasswordInput, $user->password)) {
                        throw ValidationException::withMessages([
                            'current_password' => [__('The current password is incorrect.')],
                        ]);
                    }
                }

                $passwordUpdated = true;
            } else {
                unset($payload['password']);
            }

            unset($payload['current_password']);

            if (! empty($payload)) {
                $user = $this->userService->updateUser($user->uuid, $payload) ?? $user;
            }

            $message = __('Profile updated successfully');

            if ($emailUpdated && $passwordUpdated) {
                $message = __('Email and password updated successfully.');
            } elseif ($passwordUpdated) {
                $message = __('Password updated successfully.');
            } elseif ($emailUpdated) {
                $message = __('Email updated successfully.');
            }
            
            // Invalidate profile cache after update
            $cacheKey = "user_profile_{$user->id}";
            Cache::forget($cacheKey);

            return response()->json([
                'message' => $message,
                'user' => new UserResource($user),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Profile update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function requestEmailChangeVerification(Request $request)
    {
        $this->emailChangeVerificationService->requestCode($request->user());

        return response()->json([
            'message' => __('Verification code sent to your current email.'),
        ]);
    }

    public function verifyEmailChangeCode(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|min:4|max:10',
        ]);

        $token = $this->emailChangeVerificationService->verifyCode($request->user(), $data['code']);

        return response()->json([
            'message' => __('Verification code confirmed.'),
            'token' => $token,
        ]);
    }
}

