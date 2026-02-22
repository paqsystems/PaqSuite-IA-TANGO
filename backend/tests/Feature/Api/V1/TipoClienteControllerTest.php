<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Models\TipoCliente;
use App\Models\Cliente;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Tests de Integración: TipoClienteController (TR-014, TR-015, TR-016, TR-017)
 *
 * - GET /api/v1/tipos-cliente (sin page: lista selector; con page: listado paginado)
 * - GET /api/v1/tipos-cliente/{id}
 * - POST /api/v1/tipos-cliente
 * - PUT /api/v1/tipos-cliente/{id}
 * - DELETE /api/v1/tipos-cliente/{id}
 *
 * @see TR-014(MH)-listado-de-tipos-de-cliente.md
 * @see TR-015(MH)-creación-de-tipo-de-cliente.md
 * @see TR-016(MH)-edición-de-tipo-de-cliente.md
 * @see TR-017(MH)-eliminación-de-tipo-de-cliente.md
 */
class TipoClienteControllerTest extends TestCase
{
    use DatabaseTransactions;

    private function getSupervisor(): ?User
    {
        return User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
    }

    private function getEmpleado(): ?User
    {
        return User::whereHas('usuario', fn ($q) => $q->where('supervisor', false))->first();
    }

    /** Supervisor puede listar tipos de cliente en formato selector (sin page). */
    public function test_index_sin_page_supervisor_retorna_200_array(): void
    {
        $supervisor = $this->getSupervisor();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor en la base de datos');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->getJson('/api/v1/tipos-cliente');

        $response->assertStatus(200)
            ->assertJsonPath('error', 0)
            ->assertJsonStructure(['resultado' => []]);
        $this->assertIsArray($response->json('resultado'));
    }

    /** Supervisor puede listar tipos de cliente paginado (con page). */
    public function test_index_con_page_supervisor_retorna_200_paginado(): void
    {
        $supervisor = $this->getSupervisor();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor en la base de datos');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->getJson('/api/v1/tipos-cliente?page=1&page_size=5');

        $response->assertStatus(200)
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.page', 1)
            ->assertJsonPath('resultado.page_size', 5)
            ->assertJsonStructure([
                'resultado' => [
                    'items',
                    'page',
                    'page_size',
                    'total',
                ],
            ]);
        $items = $response->json('resultado.items');
        $this->assertIsArray($items);
        if (count($items) > 0) {
            $this->assertArrayHasKey('id', $items[0]);
            $this->assertArrayHasKey('code', $items[0]);
            $this->assertArrayHasKey('descripcion', $items[0]);
            $this->assertArrayHasKey('activo', $items[0]);
            $this->assertArrayHasKey('inhabilitado', $items[0]);
        }
    }

    /** Empleado no supervisor recibe 403 en listado. */
    public function test_index_empleado_retorna_403(): void
    {
        $empleado = $this->getEmpleado();
        if (!$empleado) {
            $this->markTestSkipped('No hay usuario empleado en la base de datos');
        }
        Sanctum::actingAs($empleado);

        $response = $this->getJson('/api/v1/tipos-cliente?page=1');

        $response->assertStatus(403)
            ->assertJsonPath('error', 3101);
    }

    /** Sin token recibe 401. */
    public function test_index_sin_token_retorna_401(): void
    {
        $response = $this->getJson('/api/v1/tipos-cliente?page=1');

        $response->assertStatus(401);
    }

    /** Supervisor puede obtener un tipo por ID (show). */
    public function test_show_supervisor_retorna_200(): void
    {
        $supervisor = $this->getSupervisor();
        $tipo = TipoCliente::first();
        if (!$supervisor || !$tipo) {
            $this->markTestSkipped('Falta supervisor o tipo de cliente');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->getJson('/api/v1/tipos-cliente/' . $tipo->id);

        $response->assertStatus(200)
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.id', $tipo->id)
            ->assertJsonPath('resultado.code', $tipo->code);
    }

    /** show con ID inexistente retorna 404. */
    public function test_show_id_inexistente_retorna_404(): void
    {
        $supervisor = $this->getSupervisor();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        Sanctum::actingAs($supervisor);

        $fakeId = TipoCliente::max('id') + 9999;
        $response = $this->getJson('/api/v1/tipos-cliente/' . $fakeId);

        $response->assertStatus(404)
            ->assertJsonPath('error', 4003);
    }

    /** TR-015: Supervisor puede crear tipo de cliente (201). */
    public function test_store_supervisor_crea_tipo_201(): void
    {
        $supervisor = $this->getSupervisor();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        Sanctum::actingAs($supervisor);

        $code = 'TR015_' . uniqid();
        $response = $this->postJson('/api/v1/tipos-cliente', [
            'code' => $code,
            'descripcion' => 'Tipo TR-015 Test',
            'activo' => true,
            'inhabilitado' => false,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.code', $code)
            ->assertJsonPath('resultado.descripcion', 'Tipo TR-015 Test');
        $this->assertDatabaseHas('PQ_PARTES_TIPOS_CLIENTE', ['code' => $code]);
    }

    /** Crear con código duplicado retorna 409. */
    public function test_store_codigo_duplicado_retorna_409(): void
    {
        $supervisor = $this->getSupervisor();
        $existente = TipoCliente::first();
        if (!$supervisor || !$existente) {
            $this->markTestSkipped('Falta supervisor o tipo existente');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->postJson('/api/v1/tipos-cliente', [
            'code' => $existente->code,
            'descripcion' => 'Otro tipo',
            'activo' => true,
            'inhabilitado' => false,
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('error', 4102);
    }

    /** Crear sin descripcion retorna 422. */
    public function test_store_sin_descripcion_retorna_422(): void
    {
        $supervisor = $this->getSupervisor();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->postJson('/api/v1/tipos-cliente', [
            'code' => 'TRX_' . uniqid(),
            'descripcion' => '',
            'activo' => true,
        ]);

        $response->assertStatus(422);
    }

    /** TR-016: Supervisor puede actualizar tipo de cliente (200). */
    public function test_update_supervisor_retorna_200(): void
    {
        $supervisor = $this->getSupervisor();
        $tipo = TipoCliente::first();
        if (!$supervisor || !$tipo) {
            $this->markTestSkipped('Falta supervisor o tipo');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->putJson('/api/v1/tipos-cliente/' . $tipo->id, [
            'descripcion' => 'Descripción actualizada TR-016',
            'activo' => $tipo->activo,
            'inhabilitado' => $tipo->inhabilitado,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.descripcion', 'Descripción actualizada TR-016');
    }

    /** update con ID inexistente retorna 404. */
    public function test_update_id_inexistente_retorna_404(): void
    {
        $supervisor = $this->getSupervisor();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        Sanctum::actingAs($supervisor);

        $fakeId = TipoCliente::max('id') + 9999;
        $response = $this->putJson('/api/v1/tipos-cliente/' . $fakeId, [
            'descripcion' => 'Cualquiera',
            'activo' => true,
            'inhabilitado' => false,
        ]);

        $response->assertStatus(404);
    }

    /** TR-017: Supervisor puede eliminar tipo sin clientes (200). */
    public function test_destroy_sin_clientes_retorna_200(): void
    {
        $supervisor = $this->getSupervisor();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        $tipo = TipoCliente::create([
            'code' => 'TR017_DEL_' . uniqid(),
            'descripcion' => 'Tipo para eliminar TR-017',
            'activo' => true,
            'inhabilitado' => false,
        ]);
        Sanctum::actingAs($supervisor);

        $response = $this->deleteJson('/api/v1/tipos-cliente/' . $tipo->id);

        $response->assertStatus(200)
            ->assertJsonPath('error', 0);
        $this->assertDatabaseMissing('PQ_PARTES_TIPOS_CLIENTE', ['id' => $tipo->id]);
    }

    /** Eliminar tipo con clientes asociados retorna 422 con código 2115. */
    public function test_destroy_con_clientes_retorna_422_codigo_2115(): void
    {
        $supervisor = $this->getSupervisor();
        $tipo = TipoCliente::whereHas('clientes')->first();
        if (!$supervisor || !$tipo) {
            $this->markTestSkipped('Falta supervisor o tipo con clientes asociados');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->deleteJson('/api/v1/tipos-cliente/' . $tipo->id);

        $response->assertStatus(422)
            ->assertJsonPath('error', 2115);
    }

    /** destroy con ID inexistente retorna 404. */
    public function test_destroy_id_inexistente_retorna_404(): void
    {
        $supervisor = $this->getSupervisor();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        Sanctum::actingAs($supervisor);

        $fakeId = TipoCliente::max('id') + 9999;
        $response = $this->deleteJson('/api/v1/tipos-cliente/' . $fakeId);

        $response->assertStatus(404);
    }
}
