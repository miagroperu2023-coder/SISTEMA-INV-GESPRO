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
        Schema::create('voucher_series', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_location_id')->constrained()->cascadeOnDelete();
            $table->enum('tipo_comprobante', ['boleta', 'factura']);
            $table->string('serie'); //B001, F001
            $table->unsignedBigInteger('correlativo_actual')->default(0);
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();
            // nombre corto explícito, en vez de dejar que Laravel lo autogenere
            $table->unique(['business_location_id', 'tipo_comprobante', 'serie'], 'voucher_series_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_series');
    }
};
