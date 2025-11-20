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
        Schema::create('pago', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->nullable()->constrained('venta')->onDelete('cascade');
            $table->string('nro_pago', 50)->unique(); // Número de pago de PagoFacil
            $table->enum('tipo_pago', ['qr', 'tigo_money', 'efectivo', 'tarjeta', 'cheque'])->default('efectivo');
            $table->enum('estado', ['pendiente', 'procesando', 'completado', 'rechazado', 'cancelado'])->default('pendiente');
            $table->decimal('monto', 10, 2);
            $table->string('qr_image', 255)->nullable(); // URL de la imagen QR
            $table->string('nro_transaccion', 100)->nullable(); // Número de transacción de Tigo Money
            $table->string('nombre_persona', 100)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('nit', 20)->nullable();
            $table->text('detalles_pago')->nullable(); // JSON con detalles del pago
            $table->timestamp('fecha_pago')->nullable();
            $table->timestamp('fecha_confirmacion')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pago');
    }
};

