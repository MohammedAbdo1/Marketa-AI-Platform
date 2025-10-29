<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\OrganizationRepositoryInterface;
use Illuminate\Support\Str;

class OrganizationService extends BaseService
{
    protected OrganizationRepositoryInterface $organizationRepository;

    public function __construct(OrganizationRepositoryInterface $organizationRepository)
    {
        $this->organizationRepository = $organizationRepository;
    }

    /**
     * Get all organizations
     */
    public function getAllOrganizations()
    {
        return $this->organizationRepository->all();
    }

    /**
     * Get organization by UUID
     */
    public function getOrganizationByUuid(string $uuid)
    {
        return $this->organizationRepository->findByUuid($uuid);
    }

    /**
     * Create new organization
     */
    public function createOrganization(array $data, User $owner)
    {
        // Generate slug if not provided
        if (!isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $data['owner_id'] = $owner->id;
        $data['status'] = $data['status'] ?? 'trial';
        
        // Set trial period (14 days)
        if ($data['status'] === 'trial') {
            $data['trial_ends_at'] = now()->addDays(14);
        }

        $organization = $this->organizationRepository->create($data);

        // Update owner's organization_id
        $owner->update(['organization_id' => $organization->id]);

        $this->logActivity('Organization created', $organization, $data);

        return $organization->load('owner');
    }

    /**
     * Update organization
     */
    public function updateOrganization(string $uuid, array $data)
    {
        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $organization = $this->organizationRepository->updateByUuid($uuid, $data);

        $this->logActivity('Organization updated', $organization, $data);

        return $organization ? $organization->load('owner') : null;
    }

    /**
     * Delete organization
     */
    public function deleteOrganization(string $uuid)
    {
        $organization = $this->organizationRepository->findByUuid($uuid);
        
        if ($organization) {
            $this->logActivity('Organization deleted', $organization);
            return $this->organizationRepository->deleteByUuid($uuid);
        }

        return false;
    }

    /**
     * Get organization users
     */
    public function getOrganizationUsers($orgId)
    {
        return $this->organizationRepository->getOrganizationUsers($orgId);
    }
}

