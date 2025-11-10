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
        Schema::table('brands', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
            $table->string('tagline')->nullable()->after('slug');

            $table->json('color_palette')->nullable()->after('accent_color');
            $table->json('typography_settings')->nullable()->after('font_english');

            $table->json('voice_attributes')->nullable()->after('brand_voice');
            $table->json('usage_guidelines')->nullable()->after('voice_attributes');

            $table->string('guideline_url')->nullable()->after('usage_guidelines');
            $table->json('keywords')->nullable()->after('guideline_url');

            $table->boolean('is_default')->default(false)->after('organization_id');
            $table->string('status')->default('active')->after('is_default');

            $table->timestamp('last_synced_at')->nullable()->after('updated_at');
            $table->softDeletes()->after('last_synced_at');

            $table->unique(['organization_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->dropUnique('brands_organization_id_slug_unique');

            $table->dropColumn([
                'slug',
                'tagline',
                'color_palette',
                'typography_settings',
                'voice_attributes',
                'usage_guidelines',
                'guideline_url',
                'keywords',
                'is_default',
                'status',
                'last_synced_at',
                'deleted_at',
            ]);
        });
    }
};

