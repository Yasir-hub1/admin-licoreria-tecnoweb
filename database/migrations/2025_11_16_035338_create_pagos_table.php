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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credito_id')->nullable()->constrained('credito');
            $table->date('fecha_pago')->nullable();
            $table->decimal('monto', 10, 2)->nullable();
            $table->string('metodo', 20)->nullable();
            $table->string('nro_transaccion', 20)->nullable();
            $table->string('observacion', 30)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
