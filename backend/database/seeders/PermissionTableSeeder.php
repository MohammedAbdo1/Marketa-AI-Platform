<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionTableSeeder extends Seeder
{

    public function run(): void
    {
        $permissions = [
            // Users
            'users',
            'create_user',
            'edit_user',
            'delete_user',
            'show_user',

            // Roles
            'roles',
            'add_role',
            'edit_role',
            'delete_role',

            // Plans
            'plans',
            'create_plan',
            'edit_plan',
            'delete_plan',
            'show_plan',

            // Organizations
            'organizations',
            'create_organization',
            'edit_organization',
            'delete_organization',
            'show_organization',

            // Subscriptions
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
            Permission::create(['name' => $permission]);
        }
    }
}