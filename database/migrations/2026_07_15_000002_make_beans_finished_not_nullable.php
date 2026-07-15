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
        // A NULL finished value would hide the bean from the current-beans query
        DB::table('beans')->whereNull('finished')->update(['finished' => false]);

        Schema::table('beans', function (Blueprint $table) {
            $table->boolean('finished')->default(false)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beans', function (Blueprint $table) {
            $table->boolean('finished')->default(false)->nullable()->change();
        });
    }
};
