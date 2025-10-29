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
        Schema::create('ai_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('campaign_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('campaign_post_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('request_type'); // text_generation, image_generation, content_improvement
            $table->string('model_used'); // gpt-4, gpt-3.5-turbo, dall-e-3, etc.
            $table->text('prompt');
            $table->text('response')->nullable();
            $table->integer('tokens_used')->default(0);
            $table->decimal('cost', 8, 4)->default(0);
            $table->enum('status', ['success', 'failed', 'pending'])->default('pending');
            $table->text('error_message')->nullable();
            $table->integer('processing_time_ms')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_requests');
    }
};
