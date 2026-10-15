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
        Schema::create('voucher_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_location_id')->constrained()->cascadeOnDelete();
            $table->foreignId('voucher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->enum('tipo', ['cambio', 'anulacion_producto', 'anulacion_venta']);

            // prenda que sale de la venta (la devuelta o la anulada)
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('descripcion_anterior');
            $table->unsignedInteger('cantidad')->default(1);
            $table->decimal('precio_anterior', 10, 2)->default(0);

            // prenda que entra (solo en los cambios)
            $table->foreignId('nuevo_product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('descripcion_nueva')->nullable();
            $table->decimal('precio_nuevo', 10, 2)->nullable();

            // positivo = el cliente pagó más; negativo = se le devolvió dinero
            $table->decimal('diferencia', 10, 2)->default(0);
            $table->string('metodo_pago')->nullable();

            $table->string('motivo')->nullable();
            $table->timestamps();

            $table->index(['business_location_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_changes');
    }
};
