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
        Schema::create('size_sets', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['letra', 'numero', 'calzado', 'unico']);
            $table->string('valor');
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('orden')->default(0);
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('size_sets');
    }
};
