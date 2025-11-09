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
        Schema::create('campaign_creative_asset', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('creative_asset_id')->constrained('creative_assets')->cascadeOnDelete();
            
            // Platform-specific settings
            $table->string('platform'); // facebook, instagram, twitter, linkedin, tiktok
            
            // Scheduling
            $table->date('scheduled_date')->nullable();
            $table->time('scheduled_time')->nullable();
            $table->timestamp('published_at')->nullable();
            
            // Status
            $table->enum('status', ['pending', 'scheduled', 'published', 'failed'])->default('pending');
            
            // Post content (platform-specific captions)
            $table->text('post_content_ar')->nullable();
            $table->text('post_content_en')->nullable();
            $table->text('hashtags')->nullable();
            
            // Ordering
            $table->integer('order')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['campaign_id', 'platform']);
            $table->unique(['campaign_id', 'creative_asset_id', 'platform']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_creative_asset');
    }
};

