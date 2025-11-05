<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar' => $this->avatar,
            'status' => $this->status,
            'last_login_at' => $this->last_login_at?->toISOString(),
            'organization' => new OrganizationResource($this->whenLoaded('organization')),
            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name
                    ];
                });
            }),
            // Only load permissions when roles are loaded (they're related)
            // Cache permissions for 1 hour since they change infrequently
            'permissions' => $this->whenLoaded('roles', function () {
                $cacheKey = "user_permissions_{$this->id}";
                return Cache::remember($cacheKey, 3600, function () {
                    return $this->getAllPermissions()->pluck('name');
                });
            }),
            'active_subscription' => new SubscriptionResource($this->whenLoaded('activeSubscription')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
