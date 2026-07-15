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
        // Remove existing duplicates before enforcing uniqueness
        $this->removeDuplicates('likes');
        $this->removeDuplicates('dislikes');

        Schema::table('likes', function (Blueprint $table) {
            $table->unique(['user_id', 'bean_id']);
        });

        Schema::table('dislikes', function (Blueprint $table) {
            $table->unique(['user_id', 'bean_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('likes', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'bean_id']);
        });

        Schema::table('dislikes', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'bean_id']);
        });
    }

    // Keeps only the oldest row per (user_id, bean_id) pair
    private function removeDuplicates(string $tableName): void
    {
        $rowIdsToKeep = DB::table($tableName)
            ->selectRaw('MIN(id) as keep_id')
            ->groupBy('user_id', 'bean_id')
            ->pluck('keep_id');

        DB::table($tableName)->whereNotIn('id', $rowIdsToKeep)->delete();
    }
};
