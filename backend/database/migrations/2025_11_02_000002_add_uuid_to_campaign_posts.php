<?php

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
        Schema::table('campaign_posts', function (Blueprint $table) {
            // Add as nullable first to allow backfill
            $table->uuid('uuid')->nullable()->after('id');
        });

        // Backfill existing rows with UUIDs using direct DB queries
        $posts = DB::table('campaign_posts')->whereNull('uuid')->get();
        foreach ($posts as $post) {
            DB::table('campaign_posts')
                ->where('id', $post->id)
                ->update(['uuid' => (string) Str::uuid()]);
        }

        Schema::table('campaign_posts', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->change();
            $table->unique('uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_posts', function (Blueprint $table) {
            $table->dropUnique(['uuid']);
            $table->dropColumn('uuid');
        });
    }
};

