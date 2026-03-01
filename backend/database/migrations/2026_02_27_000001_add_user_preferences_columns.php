<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Agregar columnas de preferencias a USERS
 *
 * @see docs/04-tareas/000-Generalidades/TR-003-apertura-menu-misma-o-nueva-pestana.md
 * @see docs/04-tareas/000-Generalidades/TR-004-seleccion-idioma.md
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('USERS', function (Blueprint $table) {
            if (!Schema::hasColumn('USERS', 'locale')) {
                $table->string('locale', 10)->nullable()->after('inhabilitado');
            }
            if (!Schema::hasColumn('USERS', 'menu_abrir_nueva_pestana')) {
                $table->boolean('menu_abrir_nueva_pestana')->default(false)->after('locale');
            }
        });
    }

    public function down(): void
    {
        Schema::table('USERS', function (Blueprint $table) {
            if (Schema::hasColumn('USERS', 'locale')) {
                $table->dropColumn('locale');
            }
            if (Schema::hasColumn('USERS', 'menu_abrir_nueva_pestana')) {
                $table->dropColumn('menu_abrir_nueva_pestana');
            }
        });
    }
};
