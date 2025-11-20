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
        Schema::table('venta', function (Blueprint $table) {
            $table->string('metodo_pago', 50)->nullable()->after('tipo');
            $table->enum('estado_pago', ['pendiente', 'procesando', 'completado', 'rechazado'])->nullable()->after('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('venta', function (Blueprint $table) {
            $table->dropColumn(['metodo_pago', 'estado_pago']);
        });
    }
};

