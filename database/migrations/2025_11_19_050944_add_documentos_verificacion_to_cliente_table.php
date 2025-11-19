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
        Schema::table('cliente', function (Blueprint $table) {
            $table->string('carnet_anverso')->nullable()->after('limite_credito');
            $table->string('carnet_reverso')->nullable()->after('carnet_anverso');
            $table->string('foto_luz')->nullable()->after('carnet_reverso');
            $table->string('foto_agua')->nullable()->after('foto_luz');
            $table->string('foto_garantia')->nullable()->after('foto_agua');
            $table->enum('estado_verificacion', ['pendiente', 'en_revision', 'aprobado', 'rechazado'])->default('pendiente')->after('foto_garantia');
            $table->text('observaciones_verificacion')->nullable()->after('estado_verificacion');
            $table->timestamp('fecha_verificacion')->nullable()->after('observaciones_verificacion');
            $table->unsignedBigInteger('verificado_por')->nullable()->after('fecha_verificacion');
            
            $table->foreign('verificado_por')->references('id')->on('usuario')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cliente', function (Blueprint $table) {
            $table->dropForeign(['verificado_por']);
            $table->dropColumn([
                'carnet_anverso',
                'carnet_reverso',
                'foto_luz',
                'foto_agua',
                'foto_garantia',
                'estado_verificacion',
                'observaciones_verificacion',
                'fecha_verificacion',
                'verificado_por'
            ]);
        });
    }
};
