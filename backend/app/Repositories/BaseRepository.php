<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get all records
     */
    public function all()
    {
        return $this->model->all();
    }

    /**
     * Find a record by ID
     */
    public function findById($id)
    {
        return $this->model->find($id);
    }

    /**
     * Find a record by UUID
     */
    public function findByUuid(string $uuid)
    {
        return $this->model->where('uuid', $uuid)->first();
    }

    /**
     * Create a new record
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update a record by ID
     */
    public function update($id, array $data)
    {
        $record = $this->findById($id);
        if ($record) {
            $record->update($data);
            return $record;
        }
        return null;
    }

    /**
     * Update a record by UUID
     */
    public function updateByUuid(string $uuid, array $data)
    {
        $record = $this->findByUuid($uuid);
        if ($record) {
            $record->update($data);
            return $record;
        }
        return null;
    }

    /**
     * Delete a record by ID
     */
    public function delete($id)
    {
        $record = $this->findById($id);
        if ($record) {
            return $record->delete();
        }
        return false;
    }

    /**
     * Delete a record by UUID
     */
    public function deleteByUuid(string $uuid)
    {
        $record = $this->findByUuid($uuid);
        if ($record) {
            return $record->delete();
        }
        return false;
    }

    /**
     * Get paginated records
     */
    public function paginate(int $perPage = 15)
    {
        return $this->model->paginate($perPage);
    }

    /**
     * Find records by specific field
     */
    public function findBy(string $field, $value)
    {
        return $this->model->where($field, $value)->get();
    }

    /**
     * Find first record by specific field
     */
    public function findOneBy(string $field, $value)
    {
        return $this->model->where($field, $value)->first();
    }
}

