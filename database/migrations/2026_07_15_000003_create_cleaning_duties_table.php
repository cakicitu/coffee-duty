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
        Schema::create('cleaning_duties', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            // One duty per week; the unique index also guards against race conditions
            $table->date('week_start')->unique();
            $table->boolean('done')->default(false);
            $table->timestamp('done_at')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cleaning_duties');
    }
};
