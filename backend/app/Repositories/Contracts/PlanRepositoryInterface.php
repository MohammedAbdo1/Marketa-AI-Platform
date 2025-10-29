<?php

namespace App\Repositories\Contracts;

interface PlanRepositoryInterface
{
    public function all();
    public function findById($id);
    public function getActivePlans();
    public function getPopularPlans();
    public function findBySlug(string $slug);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
}

