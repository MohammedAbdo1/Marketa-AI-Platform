<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // المستخدم الذي أنشأ الحملة
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade'); // اختياري للمؤسسات
            $table->foreignId('brand_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('business_type');
            $table->text('description')->nullable();
            $table->string('goal'); // awareness, sales, engagement, lead_generation, product_launch
            $table->string('mode')->default('quick'); // quick, advanced
            $table->string('seasonal_event')->nullable();
            $table->text('product_description')->nullable();
            $table->text('unique_selling_point')->nullable();
            $table->text('special_offer')->nullable();
            $table->json('target_audience')->nullable();
            $table->json('platforms'); // instagram, facebook, twitter, etc.
            $table->integer('duration_days');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('posts_per_week')->default(7);
            $table->json('posting_times')->nullable();
            $table->json('content_types')->nullable(); // text, image, video, carousel, story
            $table->string('tone_of_voice')->nullable(); // formal, friendly, humorous, inspirational
            $table->json('languages'); // ['ar'], ['en'], ['ar', 'en']
            $table->boolean('use_hashtags')->default(true);
            $table->json('call_to_actions')->nullable();
            $table->decimal('paid_ads_budget', 10, 2)->nullable();
            $table->json('ai_auto_filled')->nullable(); // البيانات التي ملأها AI
            $table->json('ai_generated_plans')->nullable(); // الخطط المولدة (2-3 خطط)
            $table->integer('selected_plan_index')->nullable();
            $table->json('ai_analysis')->nullable(); // تحليل AI للحملة
            $table->string('status')->default('draft'); // draft, pending_review, generating, ready, active, paused, completed, archived
            $table->enum('generation_status', ['pending', 'generating', 'completed', 'failed','generating'])->default('pending');
            $table->integer('generation_progress')->default(0);
            $table->string('generation_task_id')->nullable();
            $table->json('brand_override_colors')->nullable();
            
            // Draft Management (Wizard System)
            $table->integer('wizard_step')->default(1);
            $table->json('wizard_data')->nullable();
            $table->boolean('is_complete')->default(false);
            
            // Campaign Intelligence
            $table->json('campaign_strategy')->nullable();

            $table->string('ai_task_id')->nullable();
            $table->timestamp('generation_started_at')->nullable();
            $table->timestamp('generation_completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Performance Indexes
            $table->index('user_id', 'idx_campaigns_user');
            $table->index(['user_id', 'status'], 'idx_campaigns_user_status');
            $table->index('brand_id', 'idx_campaigns_brand');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
