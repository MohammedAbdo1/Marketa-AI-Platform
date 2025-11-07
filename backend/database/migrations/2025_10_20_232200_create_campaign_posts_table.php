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
        Schema::create('campaign_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->onDelete('cascade');
            $table->string('platform'); // instagram, facebook, twitter, linkedin, tiktok
            $table->string('post_type'); // text, image, video, carousel, story
            
            // نظام محتوى مرن متعدد اللغات
            $table->json('content')->nullable(); // {'ar': '...', 'en': '...', 'fr': '...'}
            $table->string('primary_language')->nullable(); // ar, en, fr, etc.
            
            $table->text('hashtags')->nullable();
            $table->json('media_urls')->nullable();
            $table->json('media_prompts')->nullable(); // Prompts المستخدمة لتوليد الصور
            $table->date('scheduled_date')->nullable();
            $table->time('scheduled_time')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'published'])->default('pending');
            $table->text('ai_prompt_used')->nullable();
            $table->integer('ai_tokens_used')->default(0);
            $table->decimal('ai_cost', 8, 4)->default(0);
            $table->integer('order_number')->default(0);
            $table->integer('week_number')->nullable();
            $table->string('day_of_week')->nullable();
            
            // Content Brief & Day Tracking
            $table->json('content_brief')->nullable(); // التعليمات التفصيلية
            $table->integer('day_number')->nullable();
            $table->string('day_name')->nullable();
            $table->string('phase_name')->nullable();

            $table->integer('version_number')->default(1);
            
            $table->unsignedBigInteger('parent_post_id')->nullable();
            
            $table->enum('generation_method', ['ai', 'manual', 'uploaded'])->default('ai');            
            $table->text('image_prompt')->nullable();
            
            $table->foreign('parent_post_id')->references('id')->on('campaign_posts')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            
            // Performance Indexes
            $table->index('campaign_id', 'idx_posts_campaign');
            $table->index(['campaign_id', 'platform'], 'idx_posts_campaign_platform');
            $table->index(['campaign_id', 'status'], 'idx_posts_campaign_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_posts');
    }
};
