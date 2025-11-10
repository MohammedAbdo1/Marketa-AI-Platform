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
        Schema::table('creative_assets', function (Blueprint $table) {
            $table->foreignId('brand_id')
                ->nullable()
                ->after('organization_id')
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('brand_asset_id')
                ->nullable()
                ->after('brand_id')
                ->constrained('brand_assets')
                ->nullOnDelete();

            $table->index(['brand_id', 'asset_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('creative_assets', function (Blueprint $table) {
            $table->dropIndex('creative_assets_brand_id_asset_type_index');
            $table->dropForeign(['brand_asset_id']);
            $table->dropForeign(['brand_id']);

            $table->dropColumn(['brand_asset_id', 'brand_id']);
        });
    }
};

