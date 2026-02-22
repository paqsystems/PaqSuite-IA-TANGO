<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Crear tabla PQ_PARTES_CLIENTES
 * 
 * Representa a los clientes para los cuales se registran tareas.
 * - Tiene relación 1:1 OPCIONAL con la tabla USERS (si el cliente tiene acceso al sistema).
 * - Tiene relación N:1 OBLIGATORIA con PQ_PARTES_TIPOS_CLIENTE.
 * 
 * Si un cliente tiene user_id, puede autenticarse y consultar (solo lectura) las tareas relacionadas.
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
        Schema::create('PQ_PARTES_CLIENTES', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->comment('FK → USERS, opcional, relación 1:1');
            $table->string('nombre', 255)->comment('Nombre/Descripción del cliente');
            $table->unsignedBigInteger('tipo_cliente_id')->comment('FK → PQ_PARTES_TIPOS_CLIENTE, obligatorio');
            $table->string('code', 50)->comment('Código único del cliente');
            $table->string('email', 255)->nullable()->comment('Email del cliente');
            $table->boolean('activo')->default(true)->comment('Indica si el cliente está activo');
            $table->boolean('inhabilitado')->default(false)->comment('Indica si el cliente está inhabilitado');
            $table->timestamps();

            // Índices únicos
            $table->unique('code', 'idx_clientes_code');
            $table->unique('user_id', 'idx_clientes_user_id');
            $table->unique('email', 'idx_clientes_email');

            // Foreign Keys
            $table->foreign('user_id', 'fk_clientes_user')
                  ->references('id')
                  ->on('USERS')
                  ->onDelete('set null');

            $table->foreign('tipo_cliente_id', 'fk_clientes_tipo')
                  ->references('id')
                  ->on('PQ_PARTES_TIPOS_CLIENTE')
                  ->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('PQ_PARTES_CLIENTES');
    }
};
