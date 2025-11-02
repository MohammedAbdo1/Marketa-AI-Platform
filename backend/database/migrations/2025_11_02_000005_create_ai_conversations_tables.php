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
        // AI Conversations table
        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            $table->string('title')->nullable(); // Auto-generated from first message
            $table->enum('design_type', [
                'social_post', 
                'story', 
                'presentation', 
                'banner', 
                'custom'
            ])->default('social_post');
            
            $table->timestamp('last_message_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['user_id', 'last_message_at']);
        });

        // AI Messages table
        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('conversation_id')
                  ->constrained('ai_conversations')
                  ->onDelete('cascade');
            
            $table->enum('role', ['user', 'assistant', 'system'])->default('user');
            $table->text('content');
            
            // Generated designs from this message (array of design UUIDs)
            $table->json('generated_designs')->nullable();
            
            // Suggestion chips shown to user
            $table->json('suggestions')->nullable();
            
            // Metadata (prompts used, tokens, cost, etc.)
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['conversation_id', 'created_at']);
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_messages');
        Schema::dropIfExists('ai_conversations');
    }
};

