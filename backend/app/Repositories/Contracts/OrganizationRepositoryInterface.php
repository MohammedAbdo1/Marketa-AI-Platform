<?php

namespace App\Repositories\Contracts;

interface OrganizationRepositoryInterface
{
    public function all();
    public function findById($id);
    public function findByUuid(string $uuid);
    public function findBySlug(string $slug);
    public function getOrganizationUsers($orgId);
    public function create(array $data);
    public function update($id, array $data);
    public function updateByUuid(string $uuid, array $data);
    public function delete($id);
    public function deleteByUuid(string $uuid);
}

