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
            // Add optional design reference for backward compatibility
            $table->foreignId('design_id')
                  ->nullable()
                  ->after('campaign_id')
                  ->constrained('designs')
                  ->nullOnDelete();
            
            $table->index('design_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_posts', function (Blueprint $table) {
            $table->dropForeign(['design_id']);
            $table->dropIndex(['design_id']);
            $table->dropColumn('design_id');
        });
    }
};

