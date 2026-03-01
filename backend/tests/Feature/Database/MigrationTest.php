<?php

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Tests de Integración: Migraciones
 */
class MigrationTest extends TestCase
{
    use DatabaseTransactions;

    private array $requiredTables = [
        'USERS',
        'personal_access_tokens',
        'password_reset_tokens',
        'failed_jobs',
        'pq_menus',
    ];

    /**
     * @test
     * Verificar que las tablas base existen después de las migraciones
     */
    public function all_tables_exist_after_migration(): void
    {
        foreach ($this->requiredTables as $table) {
            $this->assertTrue(
                Schema::hasTable($table),
                "La tabla {$table} no existe"
            );
        }
    }

    /**
     * @test
     * Verificar que la tabla USERS tiene las columnas correctas
     */
    public function users_table_has_correct_columns(): void
    {
        $expectedColumns = [
            'id',
            'codigo',
            'name_user',
            'email',
            'password_hash',
            'activo',
            'inhabilitado',
            'created_at',
            'updated_at',
        ];

        foreach ($expectedColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('USERS', $column),
                "La columna {$column} no existe en USERS"
            );
        }
    }
}
