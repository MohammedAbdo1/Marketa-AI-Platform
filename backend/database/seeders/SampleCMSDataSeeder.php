<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use App\Models\Faq;
use Illuminate\Database\Seeder;

class SampleCMSDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Sample Testimonials
        $testimonials = [
            [
                'name_ar' => 'أحمد محمد',
                'name_en' => 'Ahmed Mohammed',
                'position_ar' => 'مدير تسويق',
                'position_en' => 'Marketing Manager',
                'company_ar' => 'شركة النجاح',
                'company_en' => 'Success Company',
                'content_ar' => 'منصة رائعة ساعدتني في توفير الكثير من الوقت في إنشاء الحملات التسويقية',
                'content_en' => 'Amazing platform that saved me a lot of time in creating marketing campaigns',
                'rating' => 5,
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name_ar' => 'فاطمة علي',
                'name_en' => 'Fatima Ali',
                'position_ar' => 'صاحبة مشروع',
                'position_en' => 'Business Owner',
                'company_ar' => 'متجر الأناقة',
                'company_en' => 'Elegance Store',
                'content_ar' => 'الذكاء الاصطناعي في المنصة ممتاز ويفهم السوق العربي بشكل رائع',
                'content_en' => 'The AI in the platform is excellent and understands the Arab market very well',
                'rating' => 5,
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name_ar' => 'خالد السالم',
                'name_en' => 'Khaled Al-Salem',
                'position_ar' => 'مؤسس',
                'position_en' => 'Founder',
                'company_ar' => 'مطعم الذواقة',
                'company_en' => 'Gourmet Restaurant',
                'content_ar' => 'زادت مبيعاتنا بنسبة 40% بعد استخدام المنصة. أنصح بها بشدة!',
                'content_en' => 'Our sales increased by 40% after using the platform. Highly recommended!',
                'rating' => 5,
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($testimonials as $testimonialData) {
            Testimonial::firstOrCreate(
                ['name_ar' => $testimonialData['name_ar']],
                $testimonialData
            );
        }

        // Create Sample FAQs
        $faqs = [
            [
                'category_ar' => 'عام',
                'category_en' => 'General',
                'question_ar' => 'ما هي منصة مركتة؟',
                'question_en' => 'What is Marketa platform?',
                'answer_ar' => 'مركتة هي منصة تسويق ذكية تستخدم الذكاء الاصطناعي لإنشاء حملات تسويقية متكاملة للشركات والأفراد في الوطن العربي.',
                'answer_en' => 'Marketa is a smart marketing platform that uses AI to create complete marketing campaigns for businesses and individuals in the Arab world.',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'category_ar' => 'الباقات',
                'category_en' => 'Pricing',
                'question_ar' => 'هل توجد باقة مجانية؟',
                'question_en' => 'Is there a free plan?',
                'answer_ar' => 'نعم، نوفر باقة مجانية تتيح لك إنشاء حتى 3 حملات و 20 طلب يومياً للذكاء الاصطناعي.',
                'answer_en' => 'Yes, we offer a free plan that allows you to create up to 3 campaigns and 20 daily AI requests.',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'category_ar' => 'الاستخدام',
                'category_en' => 'Usage',
                'question_ar' => 'كيف أبدأ في إنشاء حملة تسويقية؟',
                'question_en' => 'How do I start creating a marketing campaign?',
                'answer_ar' => 'ببساطة، سجل دخولك ثم اضغط على "إنشاء حملة جديدة"، أدخل معلومات عملك البسيطة، ودع الذكاء الاصطناعي يقوم بالباقي!',
                'answer_en' => 'Simply log in, click "Create New Campaign", enter your basic business information, and let the AI do the rest!',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'category_ar' => 'الدعم',
                'category_en' => 'Support',
                'question_ar' => 'ما هي طرق الدعم المتاحة؟',
                'question_en' => 'What support options are available?',
                'answer_ar' => 'نوفر دعم عبر البريد الإلكتروني لجميع المستخدمين، ودعم أولوية عبر الدردشة الحية للباقات المدفوعة.',
                'answer_en' => 'We provide email support for all users, and priority live chat support for paid plans.',
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($faqs as $faqData) {
            Faq::firstOrCreate(
                ['question_ar' => $faqData['question_ar']],
                $faqData
            );
        }

        $this->command->info('Sample CMS data created successfully!');
    }
}
