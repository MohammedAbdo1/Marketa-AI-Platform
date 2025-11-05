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
        Schema::table('designs', function (Blueprint $table) {
            $table->timestamp('trashed_at')->nullable()->after('deleted_at');
            $table->index(['user_id', 'trashed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('designs', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'trashed_at']);
            $table->dropColumn('trashed_at');
        });
    }
};

