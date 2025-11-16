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
        Schema::create('credito', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->nullable()->constrained('venta');
            $table->decimal('monto_total', 10, 2)->nullable();
            $table->decimal('saldo', 10, 2)->nullable();
            $table->string('numero_cuotas', 20)->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->string('estado', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credito');
    }
};
