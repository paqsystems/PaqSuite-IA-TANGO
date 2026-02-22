<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Crear tabla PQ_PARTES_TIPOS_CLIENTE
 * 
 * Catálogo de tipos de cliente (ej: Corporativo, PyME, Startup, Gobierno).
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
        Schema::create('PQ_PARTES_TIPOS_CLIENTE', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->comment('Código único del tipo de cliente');
            $table->string('descripcion', 255)->comment('Descripción del tipo de cliente');
            $table->boolean('activo')->default(true)->comment('Indica si el tipo está activo');
            $table->boolean('inhabilitado')->default(false)->comment('Indica si el tipo está inhabilitado');
            $table->timestamps();

            // Índice único para code
            $table->unique('code', 'idx_tipos_cliente_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('PQ_PARTES_TIPOS_CLIENTE');
    }
};
