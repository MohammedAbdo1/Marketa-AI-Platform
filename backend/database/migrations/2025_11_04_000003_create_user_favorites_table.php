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
        Schema::create('user_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('design_id')->constrained()->onDelete('cascade');
            $table->foreignId('section_id')->nullable()->constrained('favorite_sections')->onDelete('set null');
            $table->integer('order')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['user_id', 'design_id']);
            $table->index(['user_id', 'section_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_favorites');
    }
};

