<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Crear tabla pq_menus (Dictionary DB)
 *
 * Catálogo de opciones de menú del sistema. Fuente de verdad: PQ_MENUS.seed.v2.json
 *
 * @see docs/modelo-datos/md-diccionario/md-diccionario.md
 * @see docs/03-hu-historias/Historia_PQ_MENUS_seed.md
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pq_menus', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->string('text', 150)->comment('Descripción visible en pantalla');
            $table->boolean('expanded')->default(false);
            $table->unsignedInteger('idparent')->default(0)->comment('0 = raíz principal');
            $table->unsignedSmallInteger('orden')->default(0)->comment('Orden dentro del mismo padre');
            $table->char('tipo', 3)->default('INF')->comment('ABM / INF');
            $table->string('procedimiento', 150)->nullable()->comment('Vincula APIs, reportes');
            $table->boolean('enabled')->default(true);
            $table->string('routeName', 50)->nullable();
            $table->unsignedInteger('estructura')->nullable();

            $table->unique(['idparent', 'orden'], 'uq_pq_menus_parent_order');
            $table->index('idparent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pq_menus');
    }
};
