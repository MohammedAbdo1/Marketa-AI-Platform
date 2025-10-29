<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // Users Management
            'users',
            'create_user',
            'edit_user',
            'delete_user',
            'show_user',
            
            // Roles Management
            'roles',
            'add_role',
            'edit_role',
            'delete_role',
            
            // Plans Management
            'plans',
            'create_plan',
            'edit_plan',
            'delete_plan',
            'show_plan',
            
            // Organizations Management
            'organizations',
            'create_organization',
            'edit_organization',
            'delete_organization',
            'show_organization',
            
            // Subscriptions Management
            'subscriptions',
            'create_subscription',
            'edit_subscription',
            'delete_subscription',
            'show_subscription',
            
            // Settings
            'settings',
            'edit_settings',

            // CMS
            'cms',
            'edit_pages',
            'manage_sections',
            'manage_content',

            // Testimonials
            'testimonials',
            'create_testimonial',
            'edit_testimonial',
            'delete_testimonial',

            // FAQs
            'faqs',
            'create_faq',
            'edit_faq',
            'delete_faq',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Assign all permissions to admin role
        $adminRole->syncPermissions(Permission::all());

        // Create a default admin user if not exists
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('123456'),
                'status' => 'active',
            ]
        );

        // Assign admin role to admin user
        if (!$adminUser->hasRole('admin')) {
            $adminUser->assignRole('admin');
        }

        // Create a test user
        $testUser = User::firstOrCreate(
            ['email' => 'user@test.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('123456'),
                'status' => 'active',
            ]
        );

        // Assign user role to test user
        if (!$testUser->hasRole('user')) {
            $testUser->assignRole('user');
        }
    }
}