<?php

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Tests de Integración: Seeders
 * 
 * Verifica que los seeders crean los datos mínimos necesarios
 * para el funcionamiento del sistema y los tests.
 * Usa DatabaseTransactions para mejor rendimiento con SQL Server remoto.
 * 
 * @see TR-00(MH)-Generacion-base-datos-inicial.md
 */
class SeederTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     * Verificar que los seeders crean al menos un usuario administrador
     */
    public function seeders_create_admin_user(): void
    {
        $this->artisan('db:seed');

        $this->assertDatabaseHas('USERS', [
            'code' => 'ADMIN',
            'activo' => true,
            'inhabilitado' => false,
        ]);
    }

    /**
     * @test
     * Verificar que los seeders crean al menos un supervisor
     */
    public function seeders_create_supervisor(): void
    {
        $this->artisan('db:seed');

        $this->assertDatabaseHas('PQ_PARTES_USUARIOS', [
            'supervisor' => true,
            'activo' => true,
            'inhabilitado' => false,
        ]);
    }

    /**
     * @test
     * Verificar que los seeders crean al menos un tipo de cliente
     */
    public function seeders_create_tipo_cliente(): void
    {
        $this->artisan('db:seed');

        $this->assertDatabaseHas('PQ_PARTES_TIPOS_CLIENTE', [
            'code' => 'CORP',
            'activo' => true,
        ]);
    }

    /**
     * @test
     * Verificar que los seeders crean al menos un tipo de tarea con is_default=true
     */
    public function seeders_create_default_tipo_tarea(): void
    {
        $this->artisan('db:seed');

        $this->assertDatabaseHas('PQ_PARTES_TIPOS_TAREA', [
            'is_default' => true,
            'is_generico' => true,
            'activo' => true,
        ]);
    }

    /**
     * @test
     * Verificar que los seeders crean al menos un cliente
     */
    public function seeders_create_cliente(): void
    {
        $this->artisan('db:seed');

        $this->assertDatabaseHas('PQ_PARTES_CLIENTES', [
            'code' => 'CLI001',
            'activo' => true,
        ]);
    }

    /**
     * @test
     * Verificar que el supervisor está vinculado correctamente a USERS
     */
    public function supervisor_is_linked_to_users(): void
    {
        $this->artisan('db:seed');

        // Obtener el ID del user ADMIN dinámicamente
        $adminUserId = \DB::table('USERS')->where('code', 'ADMIN')->value('id');
        
        $this->assertNotNull($adminUserId, 'User ADMIN debe existir');

        $this->assertDatabaseHas('PQ_PARTES_USUARIOS', [
            'code' => 'ADMIN',
            'user_id' => $adminUserId,
        ]);

        $this->assertDatabaseHas('USERS', [
            'code' => 'ADMIN',
        ]);
    }

    /**
     * @test
     * Verificar que el cliente está vinculado correctamente a su tipo
     */
    public function cliente_is_linked_to_tipo_cliente(): void
    {
        $this->artisan('db:seed');

        // Obtener el ID del tipo cliente CORP dinámicamente
        $tipoClienteId = \DB::table('PQ_PARTES_TIPOS_CLIENTE')->where('code', 'CORP')->value('id');
        
        $this->assertNotNull($tipoClienteId, 'TipoCliente CORP debe existir');

        $this->assertDatabaseHas('PQ_PARTES_CLIENTES', [
            'code' => 'CLI001',
            'tipo_cliente_id' => $tipoClienteId,
        ]);
    }

    /**
     * @test
     * Verificar que solo existe un tipo de tarea con is_default=true
     */
    public function only_one_default_tipo_tarea_exists(): void
    {
        $this->artisan('db:seed');

        $count = \DB::table('PQ_PARTES_TIPOS_TAREA')
            ->where('is_default', true)
            ->count();

        $this->assertEquals(1, $count, "Debe existir exactamente un TipoTarea con is_default=true");
    }

    /**
     * @test
     * Verificar que los seeders pueden ejecutarse múltiples veces sin duplicar datos
     */
    public function seeders_are_idempotent(): void
    {
        $this->artisan('db:seed');
        $countBefore = \DB::table('USERS')->count();

        $this->artisan('db:seed');
        $countAfter = \DB::table('USERS')->count();

        $this->assertEquals($countBefore, $countAfter, "Los seeders no son idempotentes");
    }

    /**
     * @test
     * Verificar datos mínimos completos después de ejecutar seeders
     */
    public function seeders_create_minimum_required_data(): void
    {
        // Limpiar tablas antes de ejecutar el seeder para asegurar un estado limpio
        // Orden: primero tablas dependientes, luego tablas base (respetando foreign keys)
        \DB::table('PQ_PARTES_REGISTRO_TAREA')->delete();
        \DB::table('PQ_PARTES_CLIENTE_TIPO_TAREA')->delete();
        \DB::table('PQ_PARTES_CLIENTES')->delete();
        \DB::table('PQ_PARTES_USUARIOS')->delete();
        \DB::table('USERS')->delete();
        \DB::table('PQ_PARTES_TIPOS_TAREA')->delete();
        \DB::table('PQ_PARTES_TIPOS_CLIENTE')->delete();

        $this->artisan('db:seed');

        // Verificar USERS
        $this->assertDatabaseCount('USERS', 3); // ADMIN, CLI001, EMP001

        // Verificar PQ_PARTES_USUARIOS
        $this->assertDatabaseCount('PQ_PARTES_USUARIOS', 2); // ADMIN, EMP001

        // Verificar PQ_PARTES_TIPOS_CLIENTE
        $this->assertDatabaseCount('PQ_PARTES_TIPOS_CLIENTE', 2); // CORP, PYME

        // Verificar PQ_PARTES_TIPOS_TAREA
        $this->assertDatabaseCount('PQ_PARTES_TIPOS_TAREA', 3); // GENERAL, SOPORTE, DESARROLLO

        // Verificar PQ_PARTES_CLIENTES
        $this->assertDatabaseCount('PQ_PARTES_CLIENTES', 2); // CLI001, CLI002
    }
}
