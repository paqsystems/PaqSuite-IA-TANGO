<?php

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Tests\TestCase;

/**
 * Tests de Integración: Migraciones
 * 
 * Verifica que todas las tablas del modelo de datos fueron creadas
 * correctamente con sus columnas, índices y foreign keys.
 * Usa DatabaseTransactions para mejor rendimiento con SQL Server remoto.
 * 
 * @see TR-00(MH)-Generacion-base-datos-inicial.md
 */
class MigrationTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Lista de tablas que deben existir después de las migraciones
     */
    private array $requiredTables = [
        'USERS',
        'PQ_PARTES_USUARIOS',
        'PQ_PARTES_CLIENTES',
        'PQ_PARTES_TIPOS_CLIENTE',
        'PQ_PARTES_TIPOS_TAREA',
        'PQ_PARTES_REGISTRO_TAREA',
        'PQ_PARTES_CLIENTE_TIPO_TAREA',
    ];

    /**
     * @test
     * Verificar que todas las tablas existen después de las migraciones
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
            'code',
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

    /**
     * @test
     * Verificar que la tabla PQ_PARTES_USUARIOS tiene las columnas correctas
     */
    public function usuarios_table_has_correct_columns(): void
    {
        $expectedColumns = [
            'id',
            'user_id',
            'code',
            'nombre',
            'email',
            'supervisor',
            'activo',
            'inhabilitado',
            'created_at',
            'updated_at',
        ];

        foreach ($expectedColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('PQ_PARTES_USUARIOS', $column),
                "La columna {$column} no existe en PQ_PARTES_USUARIOS"
            );
        }
    }

    /**
     * @test
     * Verificar que la tabla PQ_PARTES_CLIENTES tiene las columnas correctas
     */
    public function clientes_table_has_correct_columns(): void
    {
        $expectedColumns = [
            'id',
            'user_id',
            'nombre',
            'tipo_cliente_id',
            'code',
            'email',
            'activo',
            'inhabilitado',
            'created_at',
            'updated_at',
        ];

        foreach ($expectedColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('PQ_PARTES_CLIENTES', $column),
                "La columna {$column} no existe en PQ_PARTES_CLIENTES"
            );
        }
    }

    /**
     * @test
     * Verificar que la tabla PQ_PARTES_TIPOS_CLIENTE tiene las columnas correctas
     */
    public function tipos_cliente_table_has_correct_columns(): void
    {
        $expectedColumns = [
            'id',
            'code',
            'descripcion',
            'activo',
            'inhabilitado',
            'created_at',
            'updated_at',
        ];

        foreach ($expectedColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('PQ_PARTES_TIPOS_CLIENTE', $column),
                "La columna {$column} no existe en PQ_PARTES_TIPOS_CLIENTE"
            );
        }
    }

    /**
     * @test
     * Verificar que la tabla PQ_PARTES_TIPOS_TAREA tiene las columnas correctas
     */
    public function tipos_tarea_table_has_correct_columns(): void
    {
        $expectedColumns = [
            'id',
            'code',
            'descripcion',
            'is_generico',
            'is_default',
            'activo',
            'inhabilitado',
            'created_at',
            'updated_at',
        ];

        foreach ($expectedColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('PQ_PARTES_TIPOS_TAREA', $column),
                "La columna {$column} no existe en PQ_PARTES_TIPOS_TAREA"
            );
        }
    }

    /**
     * @test
     * Verificar que la tabla PQ_PARTES_REGISTRO_TAREA tiene las columnas correctas
     */
    public function registro_tarea_table_has_correct_columns(): void
    {
        $expectedColumns = [
            'id',
            'usuario_id',
            'cliente_id',
            'tipo_tarea_id',
            'fecha',
            'duracion_minutos',
            'sin_cargo',
            'presencial',
            'observacion',
            'cerrado',
            'created_at',
            'updated_at',
        ];

        foreach ($expectedColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('PQ_PARTES_REGISTRO_TAREA', $column),
                "La columna {$column} no existe en PQ_PARTES_REGISTRO_TAREA"
            );
        }
    }

    /**
     * @test
     * Verificar que la tabla PQ_PARTES_CLIENTE_TIPO_TAREA tiene las columnas correctas
     */
    public function cliente_tipo_tarea_table_has_correct_columns(): void
    {
        $expectedColumns = [
            'id',
            'cliente_id',
            'tipo_tarea_id',
            'created_at',
            'updated_at',
        ];

        foreach ($expectedColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('PQ_PARTES_CLIENTE_TIPO_TAREA', $column),
                "La columna {$column} no existe en PQ_PARTES_CLIENTE_TIPO_TAREA"
            );
        }
    }

    /**
     * @test
     * Verificar que la FK de PQ_PARTES_USUARIOS a USERS funciona
     */
    public function usuarios_foreign_key_to_users_is_functional(): void
    {
        $this->expectException(QueryException::class);

        DB::table('PQ_PARTES_USUARIOS')->insert([
            'user_id' => 99999, // No existe en USERS
            'code' => 'TEST_FK',
            'nombre' => 'Test FK',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @test
     * Verificar que la FK de PQ_PARTES_CLIENTES a PQ_PARTES_TIPOS_CLIENTE funciona
     */
    public function clientes_foreign_key_to_tipos_cliente_is_functional(): void
    {
        // Primero crear un user válido (sin ID explícito para SQL Server)
        DB::table('USERS')->insert([
            'code' => 'TEST_USER',
            'password_hash' => 'hash',
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userId = DB::table('USERS')->where('code', 'TEST_USER')->value('id');

        $this->expectException(QueryException::class);

        DB::table('PQ_PARTES_CLIENTES')->insert([
            'user_id' => $userId,
            'nombre' => 'Test Cliente',
            'tipo_cliente_id' => 99999, // No existe
            'code' => 'TEST_CLI',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @test
     * Verificar que el total de tablas es 7
     */
    public function total_tables_count_is_seven(): void
    {
        $count = 0;
        foreach ($this->requiredTables as $table) {
            if (Schema::hasTable($table)) {
                $count++;
            }
        }

        $this->assertEquals(7, $count, "Se esperaban 7 tablas, se encontraron {$count}");
    }
}
