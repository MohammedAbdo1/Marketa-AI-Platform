<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions (grouped by module)
     */
    public function index()
    {
        $permissions = Permission::all();

        // Group permissions by module
        $grouped = $this->groupPermissionsByModule($permissions);

        return response()->json([
            'data' => $grouped,
        ]);
    }

    /**
     * Group permissions by module
     */
    private function groupPermissionsByModule($permissions)
    {
        $modules = [
            'users' => ['users', 'create_user', 'edit_user', 'delete_user', 'show_user'],
            'roles' => ['roles', 'add_role', 'edit_role', 'delete_role'],
            'plans' => ['plans', 'create_plan', 'edit_plan', 'delete_plan', 'show_plan'],
            'organizations' => ['organizations', 'create_organization', 'edit_organization', 'delete_organization', 'show_organization'],
            'subscriptions' => ['subscriptions', 'create_subscription', 'edit_subscription', 'delete_subscription', 'show_subscription'],
            'settings' => ['settings', 'edit_settings'],
        ];

        $grouped = [];
        foreach ($modules as $module => $perms) {
            $grouped[$module] = $permissions->whereIn('name', $perms)->values();
        }

        return $grouped;
    }
}

