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
        Schema::create('beans', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('unnamed');
            $table->bigInteger('count')->default(0);
            $table->boolean('finished')->nullable()->default(false);
            $table->timestamp('finished_at')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beans');
    }
};
