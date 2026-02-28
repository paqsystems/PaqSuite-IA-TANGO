<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Crear tabla pq_rol (Dictionary DB)
 *
 * Roles de usuario del sistema.
 *
 * @see docs/modelo-datos/md-diccionario/md-diccionario.md
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pq_rol', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre_rol', 100)->nullable()->comment('Código de rol');
            $table->string('descripcion_rol', 100)->nullable()->comment('Nombre descriptivo');
            $table->boolean('acceso_total')->default(false)->comment('Acceso a todas las opciones (Supervisor)');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pq_rol');
    }
};
