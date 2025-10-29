<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\DailyUsage;
use App\Services\Auth\AuthService;
use App\Services\OrganizationService;

class RegisterController extends Controller
{
    protected AuthService $authService;
    protected OrganizationService $organizationService;

    public function __construct(
        AuthService $authService,
        OrganizationService $organizationService
    ) {
        $this->authService = $authService;
        $this->organizationService = $organizationService;
    }

    /**
     * Register new user
     */
    public function register(RegisterRequest $request)
    {
        try {
            $userData = $request->only(['name', 'email', 'password']);
            
            // Register user
            $result = $this->authService->register($userData);
            $user = $result['user'];

            // Create organization for the user
            $organizationData = [
                'name' => $request->organization_name ?? $user->name . ' Organization',
                'status' => 'trial',
            ];

            $organization = $this->organizationService->createOrganization($organizationData, $user);
            $user->refresh(); // Refresh to get updated organization_id

            // Auto-subscribe to Free Plan
            $freePlan = Plan::where('slug', 'free')->first();
            
            if ($freePlan) {
                Subscription::create([
                    'organization_id' => $organization->id,
                    'plan_id' => $freePlan->id,
                    'status' => 'active',
                    'starts_at' => now(),
                    'ends_at' => null, // Unlimited
                    'trial_ends_at' => null,
                ]);

                // Create initial daily usage record
                DailyUsage::create([
                    'organization_id' => $organization->id,
                    'user_id' => $user->id,
                    'date' => today(),
                    'requests_count' => 0,
                    'tokens_used' => 0,
                    'ai_cost' => 0,
                ]);
            }

            // Send email verification notification
            $user->sendEmailVerificationNotification();

            return response()->json([
                'message' => 'Registration successful. Please check your email to verify your account.',
                'user' => new UserResource($user->load(['organization', 'roles'])),
                'token' => $result['token'],
                'email_verification_sent' => true,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

