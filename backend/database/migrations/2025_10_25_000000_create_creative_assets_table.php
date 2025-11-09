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
        Schema::create('creative_assets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();

            $table->string('asset_type'); // campaign_post, design, brand_asset, template, etc.
            $table->string('subtype')->nullable(); // e.g. social_post, story, banner
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('draft');

            $table->boolean('is_template')->default(false);
            $table->boolean('is_public')->default(false);

            // Ownership & source tracking
            $table->string('source_type')->nullable();       // e.g. ai, manual, template
            $table->uuid('source_id')->nullable();           // optional UUID for external references
            $table->string('source_model')->nullable();      // model class if needed

            // Context linkage (campaign, project, etc.)
            $table->string('context_type')->nullable();
            $table->unsignedBigInteger('context_id')->nullable();

            // Storage references
            $table->string('storage_path')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->string('preview_url')->nullable();
            $table->string('export_url')->nullable();

            // Canvas / dimension data (for editors)
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();

            // Legacy references for backfills / rollbacks
            $table->unsignedBigInteger('legacy_post_id')->nullable();

            // Flexible payloads
            $table->json('content')->nullable();   // e.g. composition layers, multilingual text
            $table->json('settings')->nullable();  // scheduling, AI prompts, post metadata
            $table->json('metadata')->nullable();  // colors, fonts, misc descriptors
            $table->json('tags')->nullable();

            // Lightweight usage metrics
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('used_count')->default(0);

            $table->timestamps();
            $table->softDeletes();
            $table->timestamp('trashed_at')->nullable();

            // Indexes for performance
            $table->index(['user_id', 'asset_type']);
            $table->index('asset_type');
            $table->index(['context_type', 'context_id']);
            $table->index('status');
            $table->index('legacy_post_id');
            $table->index(['user_id', 'trashed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('creative_assets');
    }
};

