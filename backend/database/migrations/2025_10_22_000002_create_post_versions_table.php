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
        Schema::create('post_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->integer('version_number');
            $table->text('content_ar')->nullable();
            $table->text('content_en')->nullable();
            $table->string('image_url')->nullable();
            $table->text('image_prompt')->nullable();
            $table->json('hashtags')->nullable();
            $table->string('cta')->nullable();
            $table->enum('generation_method', ['ai', 'manual', 'uploaded'])->default('ai');
            $table->timestamps();
            
            $table->foreign('post_id')->references('id')->on('campaign_posts')->onDelete('cascade');
            $table->index(['post_id', 'version_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_versions');
    }
};
