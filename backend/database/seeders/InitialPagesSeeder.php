<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\PageSection;
use Illuminate\Database\Seeder;

class InitialPagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Home Page
        $homePage = Page::firstOrCreate(
            ['slug' => 'home'],
            [
                'title_ar' => 'الصفحة الرئيسية',
                'title_en' => 'Home Page',
                'meta_description_ar' => 'منصة مركتة - منصة التسويق بالذكاء الاصطناعي',
                'meta_description_en' => 'Marketa - AI-Powered Marketing Platform',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        // Create Hero Section for Home Page
        PageSection::firstOrCreate(
            [
                'page_id' => $homePage->id,
                'section_type' => 'hero'
            ],
            [
                'title_ar' => 'منصة التسويق الذكية للأعمال العربية',
                'title_en' => 'Smart Marketing Platform for Arab Businesses',
                'subtitle_ar' => 'أنشئ حملات تسويقية كاملة بالذكاء الاصطناعي في دقائق',
                'subtitle_en' => 'Create complete marketing campaigns with AI in minutes',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        // Create Features Section
        PageSection::firstOrCreate(
            [
                'page_id' => $homePage->id,
                'section_type' => 'features'
            ],
            [
                'title_ar' => 'مميزات المنصة',
                'title_en' => 'Platform Features',
                'subtitle_ar' => 'كل ما تحتاجه لتسويق ناجح',
                'subtitle_en' => 'Everything you need for successful marketing',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        // Create About Page
        $aboutPage = Page::firstOrCreate(
            ['slug' => 'about'],
            [
                'title_ar' => 'من نحن',
                'title_en' => 'About Us',
                'meta_description_ar' => 'تعرف على منصة مركتة',
                'meta_description_en' => 'Learn about Marketa platform',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        // Create Pricing Page
        $pricingPage = Page::firstOrCreate(
            ['slug' => 'pricing'],
            [
                'title_ar' => 'الباقات والأسعار',
                'title_en' => 'Pricing Plans',
                'meta_description_ar' => 'اختر الباقة المناسبة لعملك',
                'meta_description_en' => 'Choose the right plan for your business',
                'is_active' => true,
                'sort_order' => 3,
            ]
        );

        // Create Pricing Section
        PageSection::firstOrCreate(
            [
                'page_id' => $pricingPage->id,
                'section_type' => 'pricing'
            ],
            [
                'title_ar' => 'خطط بأسعار مناسبة للجميع',
                'title_en' => 'Pricing Plans for Everyone',
                'subtitle_ar' => 'ابدأ مجاناً وارتقِ حسب احتياجك',
                'subtitle_en' => 'Start free and upgrade as you grow',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        // Create FAQ Page
        $faqPage = Page::firstOrCreate(
            ['slug' => 'faq'],
            [
                'title_ar' => 'الأسئلة الشائعة',
                'title_en' => 'Frequently Asked Questions',
                'meta_description_ar' => 'إجابات لأكثر الأسئلة شيوعاً',
                'meta_description_en' => 'Answers to the most common questions',
                'is_active' => true,
                'sort_order' => 4,
            ]
        );

        // Create FAQ Section
        PageSection::firstOrCreate(
            [
                'page_id' => $faqPage->id,
                'section_type' => 'faq'
            ],
            [
                'title_ar' => 'الأسئلة الشائعة',
                'title_en' => 'FAQs',
                'subtitle_ar' => 'كل ما تحتاج معرفته',
                'subtitle_en' => 'Everything you need to know',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $this->command->info('Initial pages created successfully!');
    }
}
