<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService extends BaseService
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Login user
     */
    public function login(array $credentials)
    {
        $user = $this->userRepository->findByEmail($credentials['email']);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => ['Your account is not active.'],
            ]);
        }

        // Update last login
        $this->userRepository->update($user->id, [
            'last_login_at' => now(),
        ]);

        return [
            'user' => $user->load(['roles', 'permissions', 'organization', 'activeSubscription.plan']),
            'token' => $this->createToken($user),
        ];
    }

    /**
     * Register new user
     */
    public function register(array $data)
    {
        $data['password'] = Hash::make($data['password']);
        $data['status'] = 'active';

        $user = $this->userRepository->create($data);

        // Assign default role
        $user->assignRole('user');

        $this->logActivity('User registered', $user);

        return [
            'user' => $user->load(['roles', 'permissions']),
            'token' => $this->createToken($user),
        ];
    }

    /**
     * Logout user
     */
    public function logout(User $user)
    {
        // Revoke all tokens
        $user->tokens()->delete();

        $this->logActivity('User logged out', $user);

        return true;
    }

    /**
     * Create authentication token
     */
    public function createToken(User $user, string $tokenName = 'auth_token')
    {
        return $user->createToken($tokenName)->plainTextToken;
    }

    /**
     * Refresh token
     */
    public function refreshToken(User $user)
    {
        // Revoke old tokens
        $user->tokens()->delete();

        return $this->createToken($user, 'refreshed_token');
    }
}

