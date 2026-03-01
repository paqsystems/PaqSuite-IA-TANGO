<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Renombrar tabla PQ_Reportes → PQ_REPORTE_IA
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        $schema = 'dbo';

        if ($driver === 'sqlsrv') {
            $row = DB::selectOne("
                SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_NAME IN ('PQ_Reportes', 'PQ_REPORTES', 'pq_reportes')
                AND TABLE_CATALOG = ?
            ", [config('database.connections.sqlsrv.database')]);
            if ($row && isset($row->TABLE_NAME)) {
                DB::statement("EXEC sp_rename '{$schema}.{$row->TABLE_NAME}', 'PQ_REPORTE_IA'");
            }
        } else {
            if (Schema::hasTable('PQ_Reportes')) {
                Schema::rename('PQ_Reportes', 'PQ_REPORTE_IA');
            } elseif (Schema::hasTable('PQ_REPORTES')) {
                Schema::rename('PQ_REPORTES', 'PQ_REPORTE_IA');
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('PQ_REPORTE_IA')) {
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'sqlsrv') {
                DB::statement("EXEC sp_rename 'dbo.PQ_REPORTE_IA', 'PQ_Reportes'");
            } else {
                Schema::rename('PQ_REPORTE_IA', 'PQ_Reportes');
            }
        }
    }
};
