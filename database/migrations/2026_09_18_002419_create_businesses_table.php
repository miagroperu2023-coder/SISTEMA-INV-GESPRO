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
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_comercial');
            $table->enum('tipo_documento', ['ruc', 'sin_ruc'])->default('sin_ruc');
            $table->enum('regimen_tributario', ['sin_ruc', 'nrus', 'general'])->default('sin_ruc');
            $table->string('numero_documento')->nullable();
            $table->string('razon_social')->nullable();
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
