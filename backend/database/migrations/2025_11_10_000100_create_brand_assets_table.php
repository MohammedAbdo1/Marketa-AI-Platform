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
        Schema::create('brand_assets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();

            $table->string('asset_type'); // logo, icon, font, palette, guideline, template
            $table->string('label')->nullable();
            $table->text('description')->nullable();

            $table->string('storage_path')->nullable();
            $table->string('original_filename')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();

            $table->json('metadata')->nullable(); // colors, typography, usage notes
            $table->json('tags')->nullable();

            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('display_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['brand_id', 'asset_type']);
            $table->index(['organization_id', 'asset_type']);
            $table->index('is_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brand_assets');
    }
};

