<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla auxiliar para "último layout usado" por usuario/proceso/grid.
 *
 * @see docs/04-tareas/000-Generalidades/TR-001-layouts-grilla.md
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pq_grid_layout_last_used', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('proceso', 150);
            $table->string('grid_id', 50)->default('default');
            $table->unsignedBigInteger('layout_id');
            $table->timestamps();

            $table->unique(['user_id', 'proceso', 'grid_id'], 'uq_pq_grid_layout_last_used_user_proceso_grid');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('layout_id')->references('id')->on('pq_grid_layouts');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pq_grid_layout_last_used');
    }
};
