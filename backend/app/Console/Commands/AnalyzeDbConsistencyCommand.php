<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Analiza la consistencia de la base de datos Diccionario vs md-diccionario-diagramas.md
 *
 * Verifica: existencia de tablas, campos y tipos de datos.
 *
 * Uso: php artisan db:analyze-consistency
 */
class AnalyzeDbConsistencyCommand extends Command
{
    protected $signature = 'db:analyze-consistency {--db= : Nombre de la base de datos (opcional)}';

    protected $description = 'Analiza consistencia de BD Diccionario vs md-diccionario';

    /** @var array<string, array<string, string>> Tabla => [columna => tipo_esperado] */
    private array $expectedSchema = [
        'users' => [  // En BD puede ser users o USERS
            'id' => 'bigint',
            'codigo' => 'nvarchar',
            'name_user' => 'nvarchar',
            'email' => 'nvarchar',
            'password_hash' => 'nvarchar',
            'first_login' => 'bit',
            'supervisor' => 'bit',
            'activo' => 'bit',
            'inhabilitado' => 'bit',
            'token' => 'nvarchar',
            'menu_abrir_nueva_pestana' => 'bit',
            'locale' => 'varchar',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ],
        'pq_menus' => [
            'id' => 'int',
            'text' => 'nvarchar',
            'expanded' => 'bit',
            'Idparent' => 'int',
            'order' => 'smallint',
            'tipo' => 'char',
            'procedimiento' => 'nvarchar',
            'enabled' => 'bit',
            'routeName' => 'varchar',
            'estructura' => 'int',
        ],
        'PQ_Empresa' => [
            'IDEmpresa' => 'int',
            'NombreEmpresa' => 'varchar',
            'NombreBD' => 'varchar',
            'Habilita' => 'int',
            'imagen' => 'varchar',
            'theme' => 'varchar',
        ],
        'Pq_Rol' => [
            'IDRol' => 'int',
            'NombreRol' => 'varchar',
            'DescripcionRol' => 'varchar',
            'AccesoTotal' => 'bit',
        ],
        'PQ_RolAtributo' => [
            'IDRol' => 'int',
            'IDOpcionMenu' => 'int',
            'IDAtributo' => 'int',
            'Permiso_Alta' => 'bit',
            'Permiso_Baja' => 'bit',
            'Permiso_Modi' => 'bit',
            'Permiso_Repo' => 'bit',
        ],
        'Pq_Permiso' => [
            'id' => 'int',
            'IDRol' => 'int',
            'IDEmpresa' => 'int',
            'IDUsuario' => 'int',
        ],
        'pq_grid_layouts' => [
            'id' => 'bigint',
            'user_id' => 'bigint',
            'proceso' => 'varchar',
            'grid_id' => 'varchar',
            'layout_name' => 'varchar',
            'layout_data' => 'nvarchar',
            'is_default' => 'bit',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ],
        'PQ_GrupoEmpresario' => [
            'id' => 'bigint',
            'descripcion' => 'varchar',
        ],
        'PQ_GrupoEmpresario_Empresas' => [
            'id_grupo' => 'bigint',
            'id_empresa' => 'bigint',
        ],
        'PQ_REPORTE_IA' => [
            'Id' => 'int',
            'procedimiento' => 'nvarchar',
            'Name' => 'nvarchar',
            'DisplayName' => 'text',
            'LayoutData' => 'varbinary',
            'Usuario' => 'text',
            'Empresa' => 'text',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'Proceso' => 'text',
            'Empresas' => 'text',
        ],
        'PQ_SistemaAlarmas_Cabecera' => [
            'idAlarma' => 'int',
            'idUsuario' => 'varchar',
            'archivo' => 'varchar',
            'clase' => 'varchar',
            'nombre' => 'varchar',
            'descripcion' => 'text',
            'activa' => 'bit',
        ],
        'PQ_SistemaAlarmas_Detalle' => [
            'idAlarma' => 'int',
            'clave' => 'varchar',
            'valor_string' => 'varchar',
            'valor_int' => 'int',
            'valor_datetime' => 'datetime',
            'valor_float' => 'numeric',
            'valor_bool' => 'bit',
        ],
        'PQ_TareasProgramadas_Cabecera' => [
            'idTarea' => 'int',
            'archivo' => 'varchar',
            'clase' => 'varchar',
            'nombre' => 'varchar',
            'descripcion' => 'text',
            'periodicidad' => 'varchar',
            'horario' => 'char',
            'fechaPasada' => 'char',
            'usaLog' => 'bit',
            'logFile' => 'varchar',
            'ultimaEjecucion' => 'datetime',
            'ultimoEstado' => 'varchar',
            'activa' => 'bit',
        ],
        'PQ_TareasProgramadas_Parametros' => [
            'idTarea' => 'int',
            'clave' => 'varchar',
            'valor_string' => 'varchar',
            'valor_int' => 'int',
            'valor_double' => 'numeric',
            'valor_datetime' => 'datetime',
            'valor_float' => 'numeric',
            'valor_bool' => 'bit',
            'valor_text' => 'text',
        ],
    ];

    public function handle(): int
    {
        $dbName = $this->option('db') ?? config('database.connections.sqlsrv.database');
        $this->info("Analizando base de datos: {$dbName}");
        $this->newLine();

        $errors = [];
        $warnings = [];

        // 1. Obtener tablas existentes en la BD
        $existingTables = $this->getExistingTables();
        $this->info('Tablas encontradas en BD: ' . implode(', ', $existingTables));
        $this->newLine();

        // 2. Verificar existencia de tablas esperadas
        foreach ($this->expectedSchema as $table => $columns) {
            $tableExists = $this->tableExists($table, $existingTables);
            if (!$tableExists) {
                $errors[] = "Tabla faltante: {$table}";
                continue;
            }

            $actualTable = $this->resolveTableName($table, $existingTables);
            $actualColumns = $this->getTableColumns($actualTable);

            foreach ($columns as $col => $expectedType) {
                $colExists = $this->columnExists($col, $actualColumns);
                if (!$colExists) {
                    $errors[] = "Tabla {$table}: columna faltante '{$col}'";
                    continue;
                }

                $actualType = $this->getColumnType($col, $actualColumns);
                if (!$this->typeMatches($expectedType, $actualType)) {
                    $warnings[] = "Tabla {$table}.{$col}: tipo esperado '{$expectedType}', actual '{$actualType}'";
                }
            }

            // Columnas extra en BD (no en esquema esperado)
            foreach ($actualColumns as $ac) {
                $colName = $ac['COLUMN_NAME'];
                if (!isset($columns[$colName])) {
                    $warnings[] = "Tabla {$table}: columna extra en BD '{$colName}' (tipo: {$ac['DATA_TYPE']})";
                }
            }
        }

        // 3. Tablas extra en BD (no en esquema esperado)
        $expectedTableNames = array_keys($this->expectedSchema);
        foreach ($existingTables as $t) {
            $normalized = $this->normalizeTableName($t);
            $found = false;
            foreach ($expectedTableNames as $exp) {
                if (strcasecmp($this->normalizeTableName($exp), $normalized) === 0) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $warnings[] = "Tabla extra en BD (no en md-diccionario): {$t}";
            }
        }

        // 4. Reporte
        $this->table(
            ['Tipo', 'Cantidad'],
            [
                ['Errores (tablas/columnas faltantes)', count($errors)],
                ['Advertencias (tipos/columnas extra)', count($warnings)],
            ]
        );
        $this->newLine();

        if (!empty($errors)) {
            $this->error('--- ERRORES ---');
            foreach ($errors as $e) {
                $this->line("  • {$e}");
            }
            $this->newLine();
        }

        if (!empty($warnings)) {
            $this->warn('--- ADVERTENCIAS ---');
            foreach ($warnings as $w) {
                $this->line("  • {$w}");
            }
            $this->newLine();
        }

        if (empty($errors) && empty($warnings)) {
            $this->info('✓ Consistencia OK: todas las tablas y columnas coinciden con md-diccionario.');
            return 0;
        }

        if (empty($errors)) {
            $this->info('Tablas y columnas esperadas existen. Hay advertencias de tipos o columnas extra.');
            return 0;
        }

        $this->error('Se encontraron errores de consistencia.');
        return 1;
    }

    private function getExistingTables(): array
    {
        $dbName = config('database.connections.sqlsrv.database');
        $rows = DB::select("
            SELECT TABLE_NAME
            FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_TYPE = 'BASE TABLE'
            AND TABLE_CATALOG = ?
            ORDER BY TABLE_NAME
        ", [$dbName]);
        return array_map(fn ($r) => $r->TABLE_NAME, $rows);
    }

    private function tableExists(string $table, array $existingTables): bool
    {
        return $this->resolveTableName($table, $existingTables) !== null;
    }

    private function resolveTableName(string $table, array $existingTables): ?string
    {
        $normalized = $this->normalizeTableName($table);
        foreach ($existingTables as $t) {
            if (strcasecmp($this->normalizeTableName($t), $normalized) === 0) {
                return $t;
            }
        }
        return null;
    }

    private function normalizeTableName(string $name): string
    {
        return str_replace(['[', ']'], '', $name);
    }

    private function getTableColumns(string $tableName): array
    {
        $dbName = config('database.connections.sqlsrv.database');
        $rows = DB::select("
            SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, NUMERIC_SCALE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_NAME = ?
            AND TABLE_CATALOG = ?
            ORDER BY ORDINAL_POSITION
        ", [$tableName, $dbName]);

        $result = [];
        foreach ($rows as $r) {
            $result[] = [
                'COLUMN_NAME' => $r->COLUMN_NAME,
                'DATA_TYPE' => $r->DATA_TYPE,
                'CHARACTER_MAXIMUM_LENGTH' => $r->CHARACTER_MAXIMUM_LENGTH,
                'NUMERIC_PRECISION' => $r->NUMERIC_PRECISION,
                'NUMERIC_SCALE' => $r->NUMERIC_SCALE,
            ];
        }
        return $result;
    }

    private function columnExists(string $col, array $actualColumns): bool
    {
        foreach ($actualColumns as $ac) {
            if (strcasecmp($ac['COLUMN_NAME'], $col) === 0) {
                return true;
            }
        }
        return false;
    }

    private function getColumnType(string $col, array $actualColumns): string
    {
        foreach ($actualColumns as $ac) {
            if (strcasecmp($ac['COLUMN_NAME'], $col) === 0) {
                return strtolower($ac['DATA_TYPE']);
            }
        }
        return '';
    }

    private function typeMatches(string $expected, string $actual): bool
    {
        $expected = strtolower($expected);
        $actual = strtolower($actual);

        $equiv = [
            'nvarchar' => ['nvarchar', 'varchar', 'nchar', 'char'],
            'varchar' => ['varchar', 'nvarchar', 'char', 'nchar'],
            'char' => ['char', 'nchar', 'varchar', 'nvarchar'],
            'int' => ['int', 'integer'],
            'bigint' => ['bigint'],
            'smallint' => ['smallint'],
            'bit' => ['bit'],
            'datetime' => ['datetime', 'datetime2', 'date', 'smalldatetime'],
            'text' => ['text', 'ntext', 'varchar', 'nvarchar'],
            'varbinary' => ['varbinary', 'binary', 'image'],
            'numeric' => ['numeric', 'decimal', 'float', 'real'],
        ];

        if ($expected === $actual) {
            return true;
        }

        $equivExpected = $equiv[$expected] ?? [$expected];
        return in_array($actual, $equivExpected);
    }
}
