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
        Schema::create('business_location_size_set', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_location_id')->constrained()->cascadeOnDelete();
            $table->foreignId('size_set_id')->constrained()->cascadeOnDelete();
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();

            $table->unique(['business_location_id', 'size_set_id'], 'sede_talla_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_location_size_set');
    }
};
