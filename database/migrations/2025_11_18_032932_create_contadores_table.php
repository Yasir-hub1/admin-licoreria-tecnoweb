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
        Schema::create('contador', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 50)->unique(); // 'venta', 'compra', 'producto'
            $table->string('prefijo', 10)->default(''); // 'V-', 'C-', 'PROD-'
            $table->integer('valor_actual')->default(0);
            $table->integer('longitud')->default(6); // Longitud del número (ej: 6 para 000001)
            $table->string('descripcion', 200)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contador');
    }
};
