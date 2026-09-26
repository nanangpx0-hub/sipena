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
        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->string('bps_code')->unique();
            $table->string('name');
            $table->string('capital_city')->nullable();
            $table->integer('altitude_min')->nullable();
            $table->integer('altitude_max')->nullable();
            $table->decimal('total_area_sqkm', 10, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('villages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained('districts')->onDelete('cascade');
            $table->string('bps_code')->unique();
            $table->string('name');
            $table->boolean('is_kelurahan')->default(false);
            $table->decimal('area_sqm', 14, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('villages');
        Schema::dropIfExists('districts');
    }
};
