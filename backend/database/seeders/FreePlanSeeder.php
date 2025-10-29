<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class FreePlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if free plan already exists
        $freePlan = Plan::where('slug', 'free')->first();
        
        if (!$freePlan) {
            Plan::create([
                'name_ar' => 'باقة مجانية',
                'name_en' => 'Free Plan',
                'slug' => 'free',
                'description_ar' => 'باقة مجانية للبدء في إنشاء الحملات التسويقية بالذكاء الاصطناعي',
                'description_en' => 'Free plan to start creating AI-powered marketing campaigns',
                'price_monthly' => 0,
                'price_yearly' => 0,
                'tokens_limit' => 50000,
                'daily_requests_limit' => 20,
                'features' => json_encode([
                    'max_campaigns' => 3,
                    'max_posts_per_campaign' => 30,
                    'ai_model' => 'gpt-3.5-turbo',
                    'image_generation' => false,
                    'support' => 'email',
                ]),
                'features_limits' => json_encode([
                    'campaigns' => 3,
                    'brands' => 2,
                    'posts_per_month' => 100,
                ]),
                'is_active' => true,
                'is_popular' => false,
                'sort_order' => 1,
            ]);
            
            $this->command->info('Free plan created successfully!');
        } else {
            // Update daily_requests_limit if it doesn't exist
            if ($freePlan->daily_requests_limit === null) {
                $freePlan->update([
                    'daily_requests_limit' => 20,
                    'features_limits' => json_encode([
                        'campaigns' => 3,
                        'brands' => 2,
                        'posts_per_month' => 100,
                    ]),
                ]);
                
                $this->command->info('Free plan updated with daily limits!');
            } else {
                $this->command->info('Free plan already exists!');
            }
        }
    }
}
