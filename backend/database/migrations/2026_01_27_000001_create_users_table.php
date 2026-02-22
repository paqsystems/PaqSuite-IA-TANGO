<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Crear tabla USERS
 * 
 * Tabla de autenticación centralizada del sistema.
 * ÚNICA tabla SIN prefijo PQ_PARTES_.
 * 
 * @see docs/modelo-datos.md
 * @see TR-00(MH)-Generacion-base-datos-inicial.md
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('USERS', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->comment('Código de usuario para autenticación');
            $table->string('password_hash', 255)->comment('Hash de contraseña');
            $table->boolean('activo')->default(true)->comment('Indica si el usuario está activo');
            $table->boolean('inhabilitado')->default(false)->comment('Indica si el usuario está inhabilitado');
            $table->timestamps();

            // Índice único para code
            $table->unique('code', 'idx_users_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('USERS');
    }
};
