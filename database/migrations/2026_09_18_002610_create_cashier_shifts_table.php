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
        Schema::create('cashier_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cashier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('monto_apertura', 10, 2)->nullable();
            $table->decimal('monto_cierre_esperado', 10, 2)->nullable();
            $table->decimal('monto_cierre_real', 10, 2)->nullable();
            $table->decimal('diferencia', 10, 2)->nullable();
            $table->timestamp('abierta_at');
            $table->timestamp('cerrada_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashier_shifts');
    }
};
