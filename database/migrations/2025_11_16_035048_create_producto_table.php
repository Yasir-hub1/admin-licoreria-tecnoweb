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
        Schema::create('producto', function (Blueprint $table) {
            $table->id();
            $table->text('codigo', 20)->nullable();
            $table->text('nombre', 20)->nullable();
            $table->text('descripcion', 20)->nullable();
            $table->decimal('precio', 10, 2)->nullable();
            $table->text('marca', 20)->nullable();
            $table->foreignId('categoria_id')->nullable()->constrained('categoria');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producto');
    }
};
