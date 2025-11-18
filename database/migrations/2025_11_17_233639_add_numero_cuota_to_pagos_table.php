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
        Schema::table('pagos', function (Blueprint $table) {
            $table->integer('numero_cuota')->nullable()->after('credito_id');
        });

        // Actualizar registros existentes: extraer número de cuota del nro_transaccion
        \Illuminate\Support\Facades\DB::statement("
            UPDATE pagos
            SET numero_cuota = CAST(SUBSTRING(nro_transaccion FROM 'CUOTA-([0-9]+)') AS INTEGER)
            WHERE nro_transaccion ~ '^CUOTA-[0-9]+$'
        ");

        // Para registros que no tienen formato CUOTA-X, intentar extraer de observación
        \Illuminate\Support\Facades\DB::statement("
            UPDATE pagos
            SET numero_cuota = CAST(SUBSTRING(observacion FROM 'Cuota ([0-9]+)') AS INTEGER)
            WHERE numero_cuota IS NULL
            AND observacion ~ 'Cuota [0-9]+'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn('numero_cuota');
        });
    }
};
