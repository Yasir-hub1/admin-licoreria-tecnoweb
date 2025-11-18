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
            $table->foreignId('usuario_id')->nullable()->after('id')->constrained('usuario')->onDelete('cascade');
            $table->boolean('credito_aprobado')->default(false)->after('estado');
            $table->decimal('limite_credito', 10, 2)->default(0)->after('credito_aprobado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cliente', function (Blueprint $table) {
            $table->dropForeign(['usuario_id']);
            $table->dropColumn(['usuario_id', 'credito_aprobado', 'limite_credito']);
        });
    }
};
