<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla unión N:M grupos empresarios - empresas (Dictionary DB)
 *
 * @see docs/04-tareas/002-GruposEmpresarios/TR-001-listado-grupos-empresarios.md
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pq_grupo_empresario_empresas', function (Blueprint $table) {
            $table->unsignedBigInteger('id_grupo');
            $table->unsignedInteger('id_empresa');

            $table->primary(['id_grupo', 'id_empresa']);
            $table->foreign('id_grupo')->references('id')->on('pq_grupo_empresario')->onDelete('cascade');
            $table->foreign('id_empresa')->references('IDEmpresa')->on('pq_empresa')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pq_grupo_empresario_empresas');
    }
};
