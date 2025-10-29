<?php

namespace App\Repositories\Contracts;

interface UserRepositoryInterface
{
    public function all();
    public function findById($id);
    public function findByUuid(string $uuid);
    public function findByEmail(string $email);
    public function findByUuidWithRelations(string $uuid, array $relations = []);
    public function getActiveUsers();
    public function create(array $data);
    public function update($id, array $data);
    public function updateByUuid(string $uuid, array $data);
    public function delete($id);
    public function deleteByUuid(string $uuid);
}

