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
        Schema::create('designs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Basic info
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            
            // Type & Source
            $table->enum('design_type', [
                'social_post', 
                'story', 
                'presentation', 
                'banner', 
                'custom'
            ])->default('social_post');
            
            $table->enum('source_type', [
                'ai', 
                'manual', 
                'template', 
                'imported'
            ])->default('manual');
            
            $table->uuid('source_id')->nullable();
            $table->string('source_type_model')->nullable(); // 'ai_conversation', 'template', etc.
            
            // Design data
            $table->json('composition_data'); // Fabric.js layers - full tree structure
            $table->string('thumbnail_url')->nullable();
            $table->string('export_url')->nullable(); // Final exported image
            
            // Dimensions & Settings
            $table->integer('width')->default(1080);
            $table->integer('height')->default(1080);
            $table->json('canvas_settings')->nullable(); // Background, etc.
            
            // Metadata
            $table->json('metadata')->nullable(); // Tags, colors used, fonts, etc.
            $table->boolean('is_template')->default(false);
            $table->boolean('is_public')->default(false);
            
            // Context (optional link to campaign/project)
            $table->string('context_type')->nullable(); // 'campaign', 'project', null
            $table->unsignedBigInteger('context_id')->nullable();
            
            // Stats
            $table->integer('views_count')->default(0);
            $table->integer('used_count')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['user_id', 'design_type']);
            $table->index(['source_id', 'source_type_model']);
            $table->index(['context_type', 'context_id']);
            $table->index('is_template');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('designs');
    }
};

