<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Crear tabla PQ_PARTES_USUARIOS
 * 
 * Representa a los empleados que cargan las tareas al sistema.
 * Tiene relación 1:1 obligatoria con la tabla USERS.
 * 
 * Permisos:
 * - Usuario normal (supervisor=false): Solo puede gestionar sus propias tareas.
 * - Supervisor (supervisor=true): Puede gestionar tareas de cualquier usuario.
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
        Schema::create('PQ_PARTES_USUARIOS', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('FK → USERS, obligatorio, relación 1:1');
            $table->string('code', 50)->comment('Código de usuario (debe coincidir con User.code)');
            $table->string('nombre', 255)->comment('Nombre completo del empleado');
            $table->string('email', 255)->nullable()->comment('Email del empleado');
            $table->boolean('supervisor')->default(false)->comment('Indica si es supervisor');
            $table->boolean('activo')->default(true)->comment('Indica si el usuario está activo');
            $table->boolean('inhabilitado')->default(false)->comment('Indica si el usuario está inhabilitado');
            $table->timestamps();

            // Índices únicos
            $table->unique('user_id', 'idx_usuarios_user_id');
            $table->unique('code', 'idx_usuarios_code');
            $table->unique('email', 'idx_usuarios_email');

            // Foreign Key
            $table->foreign('user_id', 'fk_usuarios_user')
                  ->references('id')
                  ->on('USERS')
                  ->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('PQ_PARTES_USUARIOS');
    }
};
