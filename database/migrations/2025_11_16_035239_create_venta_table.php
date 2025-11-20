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
        Schema::create('venta', function (Blueprint $table) {
            $table->id();
            $table->string('nro_venta', 20)->nullable();
            $table->date('fecha')->nullable();
            $table->string('tipo', 20)->nullable();
            $table->decimal('monto_total', 10, 2)->nullable();
            $table->decimal('saldo', 10, 2)->nullable();
            $table->string('numero_cuotas', 20)->nullable();
            $table->string('estado', 10)->nullable();
            $table->foreignId('cliente_id')->nullable()->constrained('cliente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venta');
    }
};
