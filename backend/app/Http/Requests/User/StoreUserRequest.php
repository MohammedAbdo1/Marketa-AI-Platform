<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|string',
            'status' => 'nullable|in:active,inactive,suspended',
            'role' => 'nullable|string|exists:roles,name',
            'organization_id' => 'nullable|exists:organizations,id',
        ];
    }
}
