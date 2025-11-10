<?php

namespace App\Policies;

use App\Models\Brand;
use App\Models\User;

class BrandPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole(['admin'])) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return !is_null($user->organization_id);
    }

    public function view(User $user, Brand $brand): bool
    {
        return $user->organization_id === $brand->organization_id;
    }

    public function create(User $user): bool
    {
        return !is_null($user->organization_id);
    }

    public function update(User $user, Brand $brand): bool
    {
        return $user->organization_id === $brand->organization_id;
    }

    public function delete(User $user, Brand $brand): bool
    {
        return $user->organization_id === $brand->organization_id;
    }

    public function setDefault(User $user, Brand $brand): bool
    {
        return $user->organization_id === $brand->organization_id;
    }
}

