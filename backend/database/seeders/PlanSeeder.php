<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name_ar' => 'مجاني',
                'name_en' => 'Free',
                'slug' => 'free',
                'description_ar' => 'باقة مجانية للمبتدئين',
                'description_en' => 'Free plan for beginners',
                'price_monthly' => 0,
                'price_yearly' => 0,
                'tokens_limit' => 1000,
                'features' => [
                    'ar' => ['1000 طلب AI شهرياً', '1 مستخدم', 'دعم أساسي'],
                    'en' => ['1,000 AI requests/month', '1 user', 'Basic support'],
                ],
                'is_active' => true,
                'is_popular' => false,
                'sort_order' => 1,
            ],
            [
                'name_ar' => 'احترافي',
                'name_en' => 'Pro',
                'slug' => 'pro',
                'description_ar' => 'للمحترفين والشركات الصغيرة',
                'description_en' => 'For professionals and small businesses',
                'price_monthly' => 29.99,
                'price_yearly' => 299.99,
                'tokens_limit' => 50000,
                'features' => [
                    'ar' => ['50,000 طلب AI شهرياً', '5 مستخدمين', 'دعم أولوية', 'تقارير متقدمة'],
                    'en' => ['50,000 AI requests/month', '5 users', 'Priority support', 'Advanced reports'],
                ],
                'is_active' => true,
                'is_popular' => true,
                'sort_order' => 2,
            ],
            [
                'name_ar' => 'الوكالة',
                'name_en' => 'Agency',
                'slug' => 'agency',
                'description_ar' => 'للوكالات والشركات الكبرى',
                'description_en' => 'For agencies and large companies',
                'price_monthly' => 99.99,
                'price_yearly' => 999.99,
                'tokens_limit' => 200000,
                'features' => [
                    'ar' => ['200,000 طلب AI شهرياً', 'مستخدمين غير محدودين', 'دعم VIP', 'تقارير مخصصة', 'API خاص'],
                    'en' => ['200,000 AI requests/month', 'Unlimited users', 'VIP support', 'Custom reports', 'Private API'],
                ],
                'is_active' => true,
                'is_popular' => false,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::create($plan);
        }
    }
}
