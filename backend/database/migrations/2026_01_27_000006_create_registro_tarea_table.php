<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Crear tabla PQ_PARTES_REGISTRO_TAREA
 * 
 * Tabla principal del sistema. Almacena los registros de tareas diarias.
 * 
 * Restricciones:
 * - duracion_minutos debe ser múltiplo de 15 (15, 30, 45, ..., 1440)
 * - duracion_minutos <= 1440 (máximo 24 horas)
 * - observacion es obligatorio
 * - cerrado = true impide modificación y eliminación
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
        Schema::create('PQ_PARTES_REGISTRO_TAREA', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id')->comment('FK → PQ_PARTES_USUARIOS');
            $table->unsignedBigInteger('cliente_id')->comment('FK → PQ_PARTES_CLIENTES');
            $table->unsignedBigInteger('tipo_tarea_id')->comment('FK → PQ_PARTES_TIPOS_TAREA');
            $table->date('fecha')->comment('Fecha de la tarea');
            $table->integer('duracion_minutos')->comment('Duración en minutos (múltiplo de 15, máximo 1440)');
            $table->boolean('sin_cargo')->default(false)->comment('Indica si la tarea es sin cargo para el cliente');
            $table->boolean('presencial')->default(false)->comment('Indica si la tarea es presencial (en el cliente)');
            $table->text('observacion')->comment('Descripción de la tarea (obligatorio)');
            $table->boolean('cerrado')->default(false)->comment('Indica si la tarea está cerrada (no se puede modificar ni eliminar)');
            $table->timestamps();

            // Índices
            $table->index('usuario_id', 'idx_registro_usuario');
            $table->index('cliente_id', 'idx_registro_cliente');
            $table->index('fecha', 'idx_registro_fecha');

            // Foreign Keys
            $table->foreign('usuario_id', 'fk_registro_usuario')
                  ->references('id')
                  ->on('PQ_PARTES_USUARIOS')
                  ->onDelete('no action');

            $table->foreign('cliente_id', 'fk_registro_cliente')
                  ->references('id')
                  ->on('PQ_PARTES_CLIENTES')
                  ->onDelete('no action');

            $table->foreign('tipo_tarea_id', 'fk_registro_tipo')
                  ->references('id')
                  ->on('PQ_PARTES_TIPOS_TAREA')
                  ->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('PQ_PARTES_REGISTRO_TAREA');
    }
};
