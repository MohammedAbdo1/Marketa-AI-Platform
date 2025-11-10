<?php

namespace App\Policies;

use App\Models\BrandAsset;
use App\Models\User;

class BrandAssetPolicy
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

    public function view(User $user, BrandAsset $asset): bool
    {
        return $user->organization_id === $asset->organization_id;
    }

    public function create(User $user): bool
    {
        return !is_null($user->organization_id);
    }

    public function update(User $user, BrandAsset $asset): bool
    {
        return $user->organization_id === $asset->organization_id;
    }

    public function delete(User $user, BrandAsset $asset): bool
    {
        return $user->organization_id === $asset->organization_id;
    }

    public function reorder(User $user, BrandAsset $asset): bool
    {
        return $user->organization_id === $asset->organization_id;
    }
}

