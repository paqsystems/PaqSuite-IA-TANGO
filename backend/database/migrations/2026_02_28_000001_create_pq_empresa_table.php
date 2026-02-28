<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Crear tabla pq_empresa (Dictionary DB)
 *
 * Catálogo de empresas del sistema (multiempresa).
 *
 * @see docs/modelo-datos/md-diccionario/md-diccionario.md
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pq_empresa', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre_empresa', 100)->comment('Nombre visual al usuario');
            $table->string('nombre_bd', 100)->comment('Nombre técnico de la base de datos');
            $table->unsignedTinyInteger('habilita')->nullable()->comment('Si está habilitada para usar');
            $table->string('imagen', 100)->nullable()->comment('Imagen o icono para el menú');
            $table->string('theme', 100)->default('default')->comment('Código de tema DevExtreme');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pq_empresa');
    }
};
