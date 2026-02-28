<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Crear tabla pq_permiso (Dictionary DB)
 *
 * Asociación Usuario–Empresa–Rol (tripleta única).
 *
 * @see docs/modelo-datos/md-diccionario/md-diccionario.md
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pq_permiso', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('id_rol')->comment('FK pq_rol.id');
            $table->unsignedInteger('id_empresa')->comment('FK pq_empresa.id');
            $table->unsignedBigInteger('id_usuario')->comment('FK USERS.id');
            $table->timestamps();

            $table->unique(['id_rol', 'id_empresa', 'id_usuario'], 'uq_pq_permiso_rol_empresa_usuario');
            $table->foreign('id_rol')->references('id')->on('pq_rol')->onDelete('cascade');
            $table->foreign('id_empresa')->references('id')->on('pq_empresa')->onDelete('cascade');
            $table->foreign('id_usuario')->references('id')->on('USERS')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pq_permiso');
    }
};
