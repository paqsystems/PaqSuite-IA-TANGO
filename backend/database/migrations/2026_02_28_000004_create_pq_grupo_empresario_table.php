<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Crear tabla pq_grupo_empresario (Dictionary DB)
 *
 * @see docs/04-tareas/002-GruposEmpresarios/TR-001-listado-grupos-empresarios.md
 * @see docs/modelo-datos/md-diccionario/md-diccionario.md
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pq_grupo_empresario', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion', 100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pq_grupo_empresario');
    }
};
