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
        // Eliminar la foreign key antigua
        DB::statement('ALTER TABLE venta DROP CONSTRAINT IF EXISTS venta_vendedor_id_foreign');

        // Migrar datos: obtener usuario_id desde empleado
        DB::statement('
            UPDATE venta 
            SET vendedor_id = (
                SELECT usuario_id 
                FROM empleado 
                WHERE empleado.id = venta.vendedor_id
            )
            WHERE vendedor_id IS NOT NULL 
            AND EXISTS (
                SELECT 1 
                FROM empleado 
                WHERE empleado.id = venta.vendedor_id 
                AND empleado.usuario_id IS NOT NULL
            )
        ');

        // Renombrar la columna usando SQL directo
        DB::statement('ALTER TABLE venta RENAME COLUMN vendedor_id TO usuario_id');

        // Crear la nueva foreign key a usuario
        Schema::table('venta', function (Blueprint $table) {
            $table->foreign('usuario_id')->references('id')->on('usuario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar la foreign key a usuario
        DB::statement('ALTER TABLE venta DROP CONSTRAINT IF EXISTS venta_usuario_id_foreign');

        // Renombrar de vuelta usando SQL directo
        DB::statement('ALTER TABLE venta RENAME COLUMN usuario_id TO vendedor_id');

        // Recrear la foreign key a empleado
        Schema::table('venta', function (Blueprint $table) {
            $table->foreign('vendedor_id')->references('id')->on('empleado');
        });
    }
};
