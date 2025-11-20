<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Agregar la columna usuario_id a la tabla venta
        Schema::table('venta', function (Blueprint $table) {
            $table->foreignId('usuario_id')->nullable()->after('cliente_id');
        });

        // Crear la foreign key a usuario
        Schema::table('venta', function (Blueprint $table) {
            $table->foreign('usuario_id')->references('id')->on('usuario')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar la foreign key a usuario
        Schema::table('venta', function (Blueprint $table) {
            $table->dropForeign(['usuario_id']);
        });

        // Eliminar la columna usuario_id
        Schema::table('venta', function (Blueprint $table) {
            $table->dropColumn('usuario_id');
        });
    }
};
