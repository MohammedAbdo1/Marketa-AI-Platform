<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;

class CreateTestUser extends Command
{
    protected $signature = 'user:create-test';
    protected $description = 'Create a test user for testing';

    public function handle()
    {
        // Check if user already exists
        $existingUser = User::where('email', 'test@marketa.ai')->first();
        
        if ($existingUser) {
            $this->info('Test user already exists: test@marketa.ai');
            return;
        }

        // Create organization
        $organization = Organization::create([
            'name' => 'Test Organization',
            'status' => 'trial',
            'slug' => 'test-organization',
            'owner_id' => 1, // We'll update this after creating the user
            'trial_ends_at' => now()->addDays(30)
        ]);

        // Create user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@marketa.ai',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'organization_id' => $organization->id
        ]);

        // Update organization owner
        $organization->update(['owner_id' => $user->id]);

        $this->info('Test user created successfully!');
        $this->info('Email: test@marketa.ai');
        $this->info('Password: password123');
    }
}