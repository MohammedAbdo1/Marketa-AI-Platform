<?php

use App\Models\Campaign;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            // Add as nullable first to allow backfill
            $table->uuid('uuid')->nullable()->after('id');
        });

        // Backfill existing rows with UUIDs without firing model events
        Campaign::withoutEvents(function () {
            Campaign::whereNull('uuid')
                ->orderBy('id')
                ->chunkById(500, function ($chunk) {
                    foreach ($chunk as $campaign) {
                        DB::table('campaigns')
                            ->where('id', $campaign->id)
                            ->update(['uuid' => (string) Str::uuid()]);
                    }
                });
        });

        Schema::table('campaigns', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->change();
            $table->unique('uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropUnique(['uuid']);
            $table->dropColumn('uuid');
        });
    }
};


