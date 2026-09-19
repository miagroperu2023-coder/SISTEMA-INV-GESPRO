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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_location_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cashier_shift_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('tipo_comprobante', ['ticket', 'boleta', 'factura'])->default('ticket');
            $table->string('serie')->nullable();
            $table->unsignedInteger('numero')->nullable();
            $table->enum('estado', ['pendiente', 'aceptado', 'rechazado', 'anulado'])->nullable();
            $table->json('respuesta_proveedor')->nullable();
            $table->string('pdf_url')->nullable();
            $table->string('xml_url')->nullable();
            $table->string('cdr_url')->nullable();
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('igv_total', 10, 2)->default(0);
            $table->date('fecha');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
