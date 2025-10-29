<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user'); // Get UUID from route

        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email',
            'password' => 'nullable|string|min:6',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|string',
            'status' => 'nullable|in:active,inactive,suspended',
            'role' => 'nullable|string|exists:roles,name',
            'organization_id' => 'nullable|exists:organizations,id',
        ];
    }
}
