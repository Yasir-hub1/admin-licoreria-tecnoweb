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
        Schema::create('inventario', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_movimiento', 20)->nullable();
            $table->integer('cantidad')->nullable();
            $table->date('fecha')->nullable();
            $table->decimal('stock_actual', 10, 2)->nullable();
            $table->string('glosa', 30)->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('usuario');
            $table->foreignId('detalle_compra_id')->nullable()->constrained('detalle_compra');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventario');
    }
};
