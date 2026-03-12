<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('favorites')) {
            return;
        }

        $hasIndex = DB::selectOne("SHOW INDEX FROM `favorites` WHERE Key_name = 'favorites_user_tmdb_unique'");
        if ($hasIndex) {
            return;
        }

        $duplicates = DB::table('favorites')
            ->select('user_id', 'tmdb_id', DB::raw('MIN(id) as keep_id'), DB::raw('COUNT(*) as cnt'))
            ->groupBy('user_id', 'tmdb_id')
            ->having('cnt', '>', 1)
            ->get();

        foreach ($duplicates as $row) {
            DB::table('favorites')
                ->where('user_id', $row->user_id)
                ->where('tmdb_id', $row->tmdb_id)
                ->where('id', '!=', $row->keep_id)
                ->delete();
        }

        Schema::table('favorites', function (Blueprint $table) {
            $table->unique(['user_id', 'tmdb_id'], 'favorites_user_tmdb_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('favorites')) {
            return;
        }

        Schema::table('favorites', function (Blueprint $table) {
            $table->dropUnique('favorites_user_tmdb_unique');
        });
    }
};
