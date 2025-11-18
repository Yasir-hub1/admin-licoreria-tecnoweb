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
        Schema::table('usuario', function (Blueprint $table) {
            $table->string('email')->unique()->after('correo')->nullable();
            $table->string('password')->after('clave')->nullable();
            $table->rememberToken()->after('password');
            $table->timestamp('email_verified_at')->nullable()->after('email');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuario', function (Blueprint $table) {
            $table->dropColumn(['email', 'password', 'remember_token', 'email_verified_at', 'created_at', 'updated_at']);
        });
    }
};
