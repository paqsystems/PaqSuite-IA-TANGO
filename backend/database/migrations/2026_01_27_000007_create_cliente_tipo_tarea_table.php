<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Crear tabla PQ_PARTES_CLIENTE_TIPO_TAREA
 * 
 * Tabla de asociación N:M entre Cliente y TipoTarea.
 * Permite asignar tipos de tarea específicos a clientes (cuando el tipo NO es genérico).
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
        Schema::create('PQ_PARTES_CLIENTE_TIPO_TAREA', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id')->comment('FK → PQ_PARTES_CLIENTES');
            $table->unsignedBigInteger('tipo_tarea_id')->comment('FK → PQ_PARTES_TIPOS_TAREA');
            $table->timestamps();

            // Índice único compuesto para evitar duplicados
            $table->unique(['cliente_id', 'tipo_tarea_id'], 'idx_ctt_unique');

            // Foreign Keys con CASCADE (si se elimina cliente o tipo, se elimina la asociación)
            $table->foreign('cliente_id', 'fk_ctt_cliente')
                  ->references('id')
                  ->on('PQ_PARTES_CLIENTES')
                  ->onDelete('cascade');

            $table->foreign('tipo_tarea_id', 'fk_ctt_tipo')
                  ->references('id')
                  ->on('PQ_PARTES_TIPOS_TAREA')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('PQ_PARTES_CLIENTE_TIPO_TAREA');
    }
};
