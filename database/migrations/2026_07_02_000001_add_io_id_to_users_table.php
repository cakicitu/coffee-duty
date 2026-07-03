<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds the optional IO config id used for push notifications
     * (e.g. Siedle door intercom) and backfills the previously
     * hardcoded mapping.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('io_id')->nullable()->after('drank');
        });

        // Backfill the mapping that used to be hardcoded in UserController
        $legacyMap = [
            1 => '13',
            2 => '14',
            3 => '15',
            4 => '16',
            5 => '17',
            6 => '18',
            7 => '19',
            8 => '39',
        ];

        foreach ($legacyMap as $userId => $ioId) {
            \Illuminate\Support\Facades\DB::table('users')
                ->where('id', $userId)
                ->update(['io_id' => $ioId]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('io_id');
        });
    }
};
