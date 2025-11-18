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
        Schema::table('inventario', function (Blueprint $table) {
            $table->foreignId('producto_id')->nullable()->after('usuario_id')->constrained('producto')->onDelete('cascade');
            $table->foreignId('detalle_venta_id')->nullable()->after('detalle_compra_id')->constrained('detalle_venta')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventario', function (Blueprint $table) {
            $table->dropForeign(['producto_id']);
            $table->dropForeign(['detalle_venta_id']);
            $table->dropColumn(['producto_id', 'detalle_venta_id']);
        });
    }
};
