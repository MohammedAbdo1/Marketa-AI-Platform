<?php

namespace App\Services;

use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class UserService extends BaseService
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Get all users
     */
    public function getAllUsers()
    {
        return $this->userRepository->all();
    }

    /**
     * Get user by UUID
     */
    public function getUserByUuid(string $uuid)
    {
        return $this->userRepository->findByUuidWithRelations($uuid, [
            'organization',
            'roles',
            'permissions',
            'activeSubscription.plan'
        ]);
    }

    /**
     * Create new user
     */
    public function createUser(array $data)
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $data['status'] = $data['status'] ?? 'active';

        $user = $this->userRepository->create($data);

        // Assign role if provided
        if (isset($data['role'])) {
            $user->assignRole($data['role']);
        }

        $this->logActivity('User created', $user, $data);

        return $user->load(['roles', 'permissions', 'organization']);
    }

    /**
     * Update user
     */
    public function updateUser(string $uuid, array $data)
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user = $this->userRepository->updateByUuid($uuid, $data);

        if ($user && isset($data['role'])) {
            $user->syncRoles([$data['role']]);
        }

        $this->logActivity('User updated', $user, $data);

        return $user ? $user->load(['roles', 'permissions', 'organization']) : null;
    }

    /**
     * Delete user
     */
    public function deleteUser(string $uuid)
    {
        $user = $this->userRepository->findByUuid($uuid);
        
        if ($user) {
            $this->logActivity('User deleted', $user);
            return $this->userRepository->deleteByUuid($uuid);
        }

        return false;
    }

    /**
     * Get active users
     */
    public function getActiveUsers()
    {
        return $this->userRepository->getActiveUsers();
    }
}

