<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

abstract class BaseService
{
    protected $repository;

    /**
     * Handle exceptions
     */
    protected function handleException(Exception $e, string $message = 'An error occurred')
    {
        Log::error($message . ': ' . $e->getMessage(), [
            'exception' => $e,
            'trace' => $e->getTraceAsString()
        ]);

        throw $e;
    }

    /**
     * Log activity
     */
    protected function logActivity(string $action, $model = null, array $data = [])
    {
        Log::info($action, [
            'model' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id ?? null : null,
            'data' => $data,
            'user' => auth()->id(),
        ]);
    }

    /**
     * Validate data
     */
    protected function validate(array $data, array $rules)
    {
        return validator($data, $rules)->validate();
    }
}

