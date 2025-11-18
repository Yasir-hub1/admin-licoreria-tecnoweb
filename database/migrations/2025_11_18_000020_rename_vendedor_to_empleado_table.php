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
        // Renombrar la tabla vendedor a empleado
        Schema::rename('vendedor', 'empleado');

        // Actualizar la foreign key en la tabla venta
        Schema::table('venta', function (Blueprint $table) {
            // Eliminar la foreign key antigua
            $table->dropForeign(['vendedor_id']);
        });

        // Recrear la foreign key con el nuevo nombre de tabla
        Schema::table('venta', function (Blueprint $table) {
            $table->foreign('vendedor_id')->references('id')->on('empleado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir la foreign key
        Schema::table('venta', function (Blueprint $table) {
            $table->dropForeign(['vendedor_id']);
        });

        Schema::table('venta', function (Blueprint $table) {
            $table->foreign('vendedor_id')->references('id')->on('vendedor');
        });

        // Renombrar la tabla empleado a vendedor
        Schema::rename('empleado', 'vendedor');
    }
};
