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
        Schema::create('item_carrito', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrito_id')->constrained('carrito')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('producto')->onDelete('cascade');
            $table->integer('cantidad')->default(1);
            $table->decimal('precio', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_carrito');
    }
};
