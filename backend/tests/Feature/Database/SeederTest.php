<?php

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Tests de Integración: Seeders
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
     * Verificar que los seeders pueden ejecutarse múltiples veces sin duplicar datos
     */
    public function seeders_are_idempotent(): void
    {
        $this->artisan('db:seed');
        $countBefore = \DB::table('USERS')->count();

        $this->artisan('db:seed');
        $countAfter = \DB::table('USERS')->count();

        $this->assertEquals($countBefore, $countAfter, 'Los seeders no son idempotentes');
    }
}
