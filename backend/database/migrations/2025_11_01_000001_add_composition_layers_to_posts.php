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
        Schema::table('campaign_posts', function (Blueprint $table) {
            // Composition support fields
            $table->json('composition_layers')->nullable()->after('media_urls');
            $table->string('base_image_url')->nullable()->after('composition_layers');
            $table->boolean('is_composed')->default(false)->after('base_image_url');
            $table->json('composition_analysis')->nullable()->after('is_composed');
        });

        Schema::table('post_versions', function (Blueprint $table) {
            // Same fields for versions
            $table->json('composition_layers')->nullable()->after('image_url');
            $table->string('base_image_url')->nullable()->after('composition_layers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_posts', function (Blueprint $table) {
            $table->dropColumn([
                'composition_layers',
                'base_image_url',
                'is_composed',
                'composition_analysis'
            ]);
        });

        Schema::table('post_versions', function (Blueprint $table) {
            $table->dropColumn([
                'composition_layers',
                'base_image_url'
            ]);
        });
    }
};

