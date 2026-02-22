<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Migración: Corregir índice único user_id en PQ_PARTES_CLIENTES.
 *
 * En SQL Server, un UNIQUE sobre una columna nullable trata múltiples NULL como duplicados.
 * Reemplazamos el índice único por uno filtrado (solo user_id IS NOT NULL) para permitir
 * muchos clientes sin acceso (user_id NULL). TR-009.
 *
 * En MySQL, un índice UNIQUE sobre una columna nullable permite múltiples NULL automáticamente,
 * por lo que no requiere ninguna acción adicional.
 *
 * @see TR-009(MH)-creación-de-cliente.md
 * @see docs/migracion-mssql-a-mysql.md
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();
        $table = 'PQ_PARTES_CLIENTES';

        if ($driver === 'sqlsrv') {
            // Eliminar índice único actual (permite solo un NULL en SQL Server)
            DB::statement("IF EXISTS (SELECT * FROM sys.indexes WHERE name = 'idx_clientes_user_id' AND object_id = OBJECT_ID('{$table}')) DROP INDEX idx_clientes_user_id ON [{$table}]");
            // Índice único solo para filas con user_id no nulo (un user_id solo puede estar en un cliente)
            DB::statement("CREATE UNIQUE NONCLUSTERED INDEX idx_clientes_user_id ON [{$table}]([user_id]) WHERE [user_id] IS NOT NULL");
        }
        // Para MySQL/SQLite: el UNIQUE nullable ya permite múltiples NULL automáticamente; no hacer nada
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        $table = 'PQ_PARTES_CLIENTES';

        if ($driver === 'sqlsrv') {
            DB::statement("IF EXISTS (SELECT * FROM sys.indexes WHERE name = 'idx_clientes_user_id' AND object_id = OBJECT_ID('{$table}')) DROP INDEX idx_clientes_user_id ON [{$table}]");
            DB::statement("CREATE UNIQUE NONCLUSTERED INDEX idx_clientes_user_id ON [{$table}]([user_id])");
        }
    }
};
