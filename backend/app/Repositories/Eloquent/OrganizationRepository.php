<?php

namespace App\Repositories\Eloquent;

use App\Models\Organization;
use App\Repositories\BaseRepository;
use App\Repositories\Contracts\OrganizationRepositoryInterface;

class OrganizationRepository extends BaseRepository implements OrganizationRepositoryInterface
{
    public function __construct(Organization $model)
    {
        parent::__construct($model);
    }

    /**
     * Find organization by slug
     */
    public function findBySlug(string $slug)
    {
        return $this->model->where('slug', $slug)->first();
    }

    /**
     * Get organization users
     */
    public function getOrganizationUsers($orgId)
    {
        return $this->model->findOrFail($orgId)->users()->get();
    }
}

