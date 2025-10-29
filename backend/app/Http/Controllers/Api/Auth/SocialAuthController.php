<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\DailyUsage;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\OrganizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    protected OrganizationService $organizationService;

    public function __construct(OrganizationService $organizationService)
    {
        $this->organizationService = $organizationService;
    }

    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->stateless()
            ->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Find or create user
            $user = User::where('email', $googleUser->email)->first();

            if ($user) {
                // User exists - just login
                $user->update(['last_login_at' => now()]);
                
                // Mark email as verified if not already
                if (!$user->hasVerifiedEmail()) {
                    $user->markEmailAsVerified();
                }
            } else {
                // Create new user
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'password' => Hash::make(Str::random(24)), // Random password
                    'email_verified_at' => now(), // Auto-verify for OAuth users
                    'avatar' => $googleUser->avatar,
                    'status' => 'active',
                ]);

                // Assign 'user' role
                $user->assignRole('user');

                // Create organization for new user
                $organizationData = [
                    'name' => $user->name . ' Organization',
                    'status' => 'trial',
                ];

                $organization = $this->organizationService->createOrganization($organizationData, $user);
                $user->refresh();

                // Auto-subscribe to Free Plan
                $freePlan = Plan::where('slug', 'free')->first();

                if ($freePlan) {
                    Subscription::create([
                        'organization_id' => $organization->id,
                        'plan_id' => $freePlan->id,
                        'status' => 'active',
                        'starts_at' => now(),
                        'ends_at' => null,
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
            }

            // Create token
            $token = $user->createToken('auth-token')->plainTextToken;

            // Redirect to frontend with token
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            $redirectUrl = $frontendUrl . '/auth/google/callback?token=' . $token;

            return redirect($redirectUrl);
        } catch (\Exception $e) {
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            $errorUrl = $frontendUrl . '/auth/login?error=oauth_failed';

            return redirect($errorUrl);
        }
    }
}
