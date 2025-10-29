<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateAdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create the admin user first
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('123456'),
            'status' => 'active',
        ]);

        // Create admin organization
        $adminOrg = \App\Models\Organization::create([
            'name' => 'Admin Organization',
            'slug' => 'admin-org',
            'status' => 'active',
            'owner_id' => $admin->id,
        ]);

        // Update admin with organization
        $admin->update(['organization_id' => $adminOrg->id]);

        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);

        // Assign all permissions to admin role
        $permissions = Permission::all();
        $adminRole->syncPermissions($permissions);

        // Assign admin role to admin user
        $admin->assignRole($adminRole);

        // Create 9 more users
        for ($i = 1; $i < 10; $i++) {
            $user = User::create([
                'name' => 'User ' . $i,
                'email' => 'user' . $i . '@example.com',
                'password' => bcrypt('123456'),
                'status' => 'active',
            ]);

            $user->assignRole($userRole);
        }
    }
}
