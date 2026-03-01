<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Crear tabla pq_rol_atributo (Dictionary DB)
 *
 * Permisos granulares por rol y opción de menú (Alta, Baja, Modi, Repo).
 *
 * @see docs/04-tareas/001-Seguridad/TR-014-administracion-atributos-rol.md
 * @see docs/modelo-datos/md-diccionario/md-diccionario.md
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pq_rol_atributo', function (Blueprint $table) {
            $table->unsignedInteger('id_rol');
            $table->unsignedInteger('id_opcion_menu');
            $table->boolean('permiso_alta')->default(false);
            $table->boolean('permiso_baja')->default(false);
            $table->boolean('permiso_modi')->default(false);
            $table->boolean('permiso_repo')->default(false);
            $table->timestamps();

            $table->primary(['id_rol', 'id_opcion_menu']);
            $table->foreign('id_rol')->references('id')->on('pq_rol')->onDelete('cascade');
            $table->foreign('id_opcion_menu')->references('id')->on('pq_menus')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pq_rol_atributo');
    }
};
