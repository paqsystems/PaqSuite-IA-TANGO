<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Renombrar columna name → name_user en USERS
 *
 * Para bases de datos existentes que tienen la columna 'name'.
 * Si la tabla fue creada con name_user (migración actualizada), esta migración no hace nada.
 * Usa SQL nativo para compatibilidad con SQL Server.
 *
 * @see docs/modelo-datos/md-diccionario/md-diccionario.md
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('USERS') && Schema::hasColumn('USERS', 'name')) {
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'sqlsrv') {
                DB::statement("EXEC sp_rename 'USERS.name', 'name_user', 'COLUMN'");
            } elseif ($driver === 'mysql') {
                DB::statement('ALTER TABLE USERS RENAME COLUMN name TO name_user');
            } else {
                DB::statement('ALTER TABLE USERS RENAME COLUMN name TO name_user');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('USERS') && Schema::hasColumn('USERS', 'name_user')) {
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'sqlsrv') {
                DB::statement("EXEC sp_rename 'USERS.name_user', 'name', 'COLUMN'");
            } elseif ($driver === 'mysql') {
                DB::statement('ALTER TABLE USERS RENAME COLUMN name_user TO name');
            } else {
                DB::statement('ALTER TABLE USERS RENAME COLUMN name_user TO name');
            }
        }
    }
};
