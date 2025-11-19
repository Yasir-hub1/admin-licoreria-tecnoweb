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
        Schema::create('visita', function (Blueprint $table) {
            $table->id();
            $table->string('ruta', 200)->nullable(); // Ruta visitada
            $table->string('nombre_pagina', 100)->nullable(); // Nombre descriptivo de la página
            $table->foreignId('usuario_id')->nullable()->constrained('usuario');
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // Índice para búsquedas rápidas
        Schema::table('visita', function (Blueprint $table) {
            $table->index('ruta');
            $table->index('usuario_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visita');
    }
};
