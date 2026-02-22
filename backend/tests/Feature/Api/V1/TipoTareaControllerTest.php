<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Models\TipoTarea;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Tests de Integración: TipoTareaController (TR-023, TR-024, TR-025, TR-026, TR-027)
 *
 * - GET /api/v1/tipos-tarea (sin page: lista selector; con page: listado paginado)
 * - GET /api/v1/tipos-tarea/{id}
 * - POST /api/v1/tipos-tarea
 * - PUT /api/v1/tipos-tarea/{id}
 * - DELETE /api/v1/tipos-tarea/{id}
 *
 * @see TR-023(MH)-listado-de-tipos-de-tarea.md
 * @see TR-024(MH)-creación-de-tipo-de-tarea.md
 * @see TR-025(MH)-edición-de-tipo-de-tarea.md
 * @see TR-026(MH)-eliminación-de-tipo-de-tarea.md
 * @see TR-027(SH)-visualización-de-detalle-de-tipo-de-tarea.md
 */
class TipoTareaControllerTest extends TestCase
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

    /** Supervisor puede listar tipos de tarea en formato selector (sin page). */
    public function test_index_sin_page_supervisor_retorna_200_array(): void
    {
        $supervisor = $this->getSupervisor();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor en la base de datos');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->getJson('/api/v1/tipos-tarea');

        $response->assertStatus(200)
            ->assertJsonPath('error', 0)
            ->assertJsonStructure(['resultado' => []]);
        $this->assertIsArray($response->json('resultado'));
    }

    /** Supervisor puede listar tipos de tarea paginado (con page). */
    public function test_index_con_page_supervisor_retorna_200_paginado(): void
    {
        $supervisor = $this->getSupervisor();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor en la base de datos');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->getJson('/api/v1/tipos-tarea?page=1&page_size=5');

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
            $this->assertArrayHasKey('is_generico', $items[0]);
            $this->assertArrayHasKey('is_default', $items[0]);
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

        $response = $this->getJson('/api/v1/tipos-tarea?page=1');

        $response->assertStatus(403)
            ->assertJsonPath('error', 3101);
    }

    /** Sin token recibe 401. */
    public function test_index_sin_token_retorna_401(): void
    {
        $response = $this->getJson('/api/v1/tipos-tarea?page=1');

        $response->assertStatus(401);
    }

    /** Supervisor puede obtener un tipo por ID (show). */
    public function test_show_supervisor_retorna_200(): void
    {
        $supervisor = $this->getSupervisor();
        $tipo = TipoTarea::first();
        if (!$supervisor || !$tipo) {
            $this->markTestSkipped('Falta supervisor o tipo de tarea');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->getJson('/api/v1/tipos-tarea/' . $tipo->id);

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

        $fakeId = TipoTarea::max('id') + 9999;
        $response = $this->getJson('/api/v1/tipos-tarea/' . $fakeId);

        $response->assertStatus(404)
            ->assertJsonPath('error', 4003);
    }

    /** TR-024: Supervisor puede crear tipo de tarea (201). */
    public function test_store_supervisor_crea_tipo_201(): void
    {
        $supervisor = $this->getSupervisor();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        Sanctum::actingAs($supervisor);

        $code = 'TR024_' . uniqid();
        $response = $this->postJson('/api/v1/tipos-tarea', [
            'code' => $code,
            'descripcion' => 'Tipo TR-024 Test',
            'is_generico' => false,
            'is_default' => false,
            'activo' => true,
            'inhabilitado' => false,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.code', $code)
            ->assertJsonPath('resultado.descripcion', 'Tipo TR-024 Test');
        $this->assertDatabaseHas('PQ_PARTES_TIPOS_TAREA', ['code' => $code]);
    }

    /** Crear con código duplicado retorna 409. */
    public function test_store_codigo_duplicado_retorna_409(): void
    {
        $supervisor = $this->getSupervisor();
        $existente = TipoTarea::first();
        if (!$supervisor || !$existente) {
            $this->markTestSkipped('Falta supervisor o tipo existente');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->postJson('/api/v1/tipos-tarea', [
            'code' => $existente->code,
            'descripcion' => 'Otro tipo',
            'is_generico' => false,
            'is_default' => false,
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

        $response = $this->postJson('/api/v1/tipos-tarea', [
            'code' => 'TRX_' . uniqid(),
            'descripcion' => '',
            'is_generico' => false,
            'is_default' => false,
            'activo' => true,
        ]);

        $response->assertStatus(422);
    }

    /** Crear con is_default cuando ya existe otro por defecto retorna 422 (2117). */
    public function test_store_segundo_por_defecto_retorna_422_2117(): void
    {
        $supervisor = $this->getSupervisor();
        $existente = TipoTarea::where('is_default', true)->first();
        if (!$supervisor || !$existente) {
            $this->markTestSkipped('Falta supervisor o tipo con is_default=true');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->postJson('/api/v1/tipos-tarea', [
            'code' => 'TR2117_' . uniqid(),
            'descripcion' => 'Otro por defecto',
            'is_generico' => true,
            'is_default' => true,
            'activo' => true,
            'inhabilitado' => false,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error', 2117);
    }

    /** TR-025: Supervisor puede actualizar tipo de tarea (200). */
    public function test_update_supervisor_retorna_200(): void
    {
        $supervisor = $this->getSupervisor();
        $tipo = TipoTarea::where('is_default', false)->first();
        if (!$supervisor || !$tipo) {
            $this->markTestSkipped('Falta supervisor o tipo no por defecto');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->putJson('/api/v1/tipos-tarea/' . $tipo->id, [
            'descripcion' => 'Descripción actualizada TR-025',
            'is_generico' => $tipo->is_generico,
            'is_default' => $tipo->is_default,
            'activo' => $tipo->activo,
            'inhabilitado' => $tipo->inhabilitado,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.descripcion', 'Descripción actualizada TR-025');
    }

    /** update con ID inexistente retorna 404. */
    public function test_update_id_inexistente_retorna_404(): void
    {
        $supervisor = $this->getSupervisor();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        Sanctum::actingAs($supervisor);

        $fakeId = TipoTarea::max('id') + 9999;
        $response = $this->putJson('/api/v1/tipos-tarea/' . $fakeId, [
            'descripcion' => 'Cualquiera',
            'is_generico' => false,
            'is_default' => false,
            'activo' => true,
            'inhabilitado' => false,
        ]);

        $response->assertStatus(404);
    }

    /** TR-026: Supervisor puede eliminar tipo sin referencias (200). */
    public function test_destroy_sin_referencias_retorna_200(): void
    {
        $supervisor = $this->getSupervisor();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        $tipo = TipoTarea::create([
            'code' => 'TR026_DEL_' . uniqid(),
            'descripcion' => 'Tipo para eliminar TR-026',
            'is_generico' => false,
            'is_default' => false,
            'activo' => true,
            'inhabilitado' => false,
        ]);
        Sanctum::actingAs($supervisor);

        $response = $this->deleteJson('/api/v1/tipos-tarea/' . $tipo->id);

        $response->assertStatus(200)
            ->assertJsonPath('error', 0);
        $this->assertDatabaseMissing('PQ_PARTES_TIPOS_TAREA', ['id' => $tipo->id]);
    }

    /** Eliminar tipo con tareas o clientes asociados retorna 422 con código 2114. */
    public function test_destroy_con_referencias_retorna_422_codigo_2114(): void
    {
        $supervisor = $this->getSupervisor();
        $tipo = TipoTarea::whereHas('registrosTarea')->orWhereHas('clientes')->first();
        if (!$supervisor || !$tipo) {
            $this->markTestSkipped('Falta supervisor o tipo con tareas/clientes asociados');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->deleteJson('/api/v1/tipos-tarea/' . $tipo->id);

        $response->assertStatus(422)
            ->assertJsonPath('error', 2114);
    }

    /** destroy con ID inexistente retorna 404. */
    public function test_destroy_id_inexistente_retorna_404(): void
    {
        $supervisor = $this->getSupervisor();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        Sanctum::actingAs($supervisor);

        $fakeId = TipoTarea::max('id') + 9999;
        $response = $this->deleteJson('/api/v1/tipos-tarea/' . $fakeId);

        $response->assertStatus(404);
    }
}
