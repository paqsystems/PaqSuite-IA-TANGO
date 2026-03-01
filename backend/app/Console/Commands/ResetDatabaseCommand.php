<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Vacía las tablas del diccionario (md-diccionario-diagramas.md) y ejecuta los seeders.
 *
 * Orden de borrado: tablas hijas primero para respetar FKs.
 * No se modifica la tabla migrations.
 *
 * Uso: php artisan db:reset-and-seed
 */
class ResetDatabaseCommand extends Command
{
    protected $signature = 'db:reset-and-seed {--no-seed : Solo vaciar tablas, no ejecutar seeders}';

    protected $description = 'Vacía tablas del diccionario y ejecuta seeders (datos de prueba + menú)';

    /**
     * Tablas a vaciar en orden (hijas primero).
     * Nombres según migraciones y md-diccionario (SQL Server es case-insensitive).
     */
    private array $tablesToTruncate = [
        'personal_access_tokens',
        'password_reset_tokens',
        'pq_grid_layout_last_used',
        'pq_grid_layouts',
        'pq_rol_atributo',
        'PQ_RolAtributo',
        'pq_permiso',
        'Pq_Permiso',
        'pq_grupo_empresario_empresas',
        'PQ_GrupoEmpresario_Empresas',
        'pq_grupo_empresario',
        'PQ_GrupoEmpresario',
        'PQ_SistemaAlarmas_Detalle',
        'PQ_SistemaAlarmas_Cabecera',
        'PQ_TareasProgramadas_Parametros',
        'PQ_TareasProgramadas_Cabecera',
        'PQ_REPORTE_IA',
        'pq_reporte_ia',
        'pq_menus',
        'pq_rol',
        'Pq_Rol',
        'pq_empresa',
        'PQ_Empresa',
        'USERS',
        'users',
    ];

    public function handle(): int
    {
        $driver = config('database.default');
        $connection = config("database.connections.{$driver}.driver");

        $this->info('Vaciando tablas del diccionario...');

        $truncated = 0;
        $seen = [];

        foreach ($this->tablesToTruncate as $table) {
            $normalized = strtolower($table);
            if (isset($seen[$normalized])) {
                continue;
            }

            if (!$this->tableExists($table)) {
                continue;
            }

            try {
                if ($connection === 'sqlsrv') {
                    DB::statement("DELETE FROM [{$table}]");
                } else {
                    DB::table($table)->truncate();
                }
                $this->line("  ✓ {$table}");
                $truncated++;
                $seen[$normalized] = true;
            } catch (\Throwable $e) {
                $this->warn("  ✗ {$table}: {$e->getMessage()}");
            }
        }

        $this->info("Tablas vaciadas: {$truncated}");

        if ($this->option('no-seed')) {
            $this->info('Omitiendo seeders (--no-seed).');
            return Command::SUCCESS;
        }

        $this->info('Ejecutando seeders...');
        Artisan::call('db:seed', [], $this->output);

        $this->info('Listo.');
        return Command::SUCCESS;
    }

    private function tableExists(string $table): bool
    {
        return Schema::hasTable($table);
    }
}
