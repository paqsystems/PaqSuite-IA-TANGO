<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Crear tabla pq_grid_layouts (Dictionary DB)
 *
 * Layouts persistentes de grillas DevExtreme por usuario.
 *
 * @see docs/04-tareas/000-Generalidades/TR-001-layouts-grilla.md
 * @see docs/modelo-datos/md-diccionario/md-diccionario.md
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pq_grid_layouts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('Usuario que creó el layout');
            $table->string('proceso', 150)->comment('Identificador del proceso (ej. pq_menus.procedimiento)');
            $table->string('grid_id', 50)->default('default')->comment('Identificador del grid cuando hay varios');
            $table->string('layout_name', 100)->comment('Nombre del layout');
            $table->json('layout_data')->nullable()->comment('JSON: columnas, filtros, agrupaciones, etc.');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['proceso', 'grid_id', 'layout_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pq_grid_layouts');
    }
};
