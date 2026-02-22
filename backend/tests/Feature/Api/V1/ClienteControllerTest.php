<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Models\Cliente;
use App\Models\ClienteTipoTarea;
use App\Models\TipoCliente;
use App\Models\TipoTarea;
use App\Models\RegistroTarea;
use App\Services\ClienteService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Tests de Integración: ClienteController (TR-008, TR-009, TR-010, TR-011)
 *
 * - GET /api/v1/clientes - Listado paginado (solo supervisores)
 * - GET /api/v1/tipos-cliente - Lista tipos de cliente (solo supervisores)
 * - POST /api/v1/clientes - Crear cliente (TR-009)
 * - GET /api/v1/clientes/{id} - Detalle (TR-010)
 * - PUT /api/v1/clientes/{id} - Actualizar (TR-010)
 * - DELETE /api/v1/clientes/{id} - Eliminar (TR-011)
 * - GET /api/v1/clientes/{id}/tipos-tarea - Tipos asignados (TR-012)
 * - PUT /api/v1/clientes/{id}/tipos-tarea - Actualizar asignación (TR-012)
 *
 * @see TR-008(MH)-listado-de-clientes.md
 * @see TR-009(MH)-creación-de-cliente.md
 * @see TR-010(MH)-edición-de-cliente.md
 * @see TR-011(MH)-eliminación-de-cliente.md
 * @see TR-012(MH)-asignación-de-tipos-de-tarea-a-cliente.md
 */
class ClienteControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Supervisor puede listar clientes (200).
     */
    public function test_index_supervisor_retorna_200_con_items(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor en la base de datos');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->getJson('/api/v1/clientes?page=1&page_size=5');

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
                    'total_pages',
                ],
            ]);
        $items = $response->json('resultado.items');
        $this->assertIsArray($items);
        if (count($items) > 0) {
            $this->assertArrayHasKey('id', $items[0]);
            $this->assertArrayHasKey('code', $items[0]);
            $this->assertArrayHasKey('nombre', $items[0]);
            $this->assertArrayHasKey('tipo_cliente', $items[0]);
            $this->assertArrayHasKey('activo', $items[0]);
            $this->assertArrayHasKey('inhabilitado', $items[0]);
        }
    }

    /**
     * Empleado no supervisor recibe 403.
     */
    public function test_index_empleado_no_supervisor_retorna_403(): void
    {
        $empleado = User::whereHas('usuario', fn ($q) => $q->where('supervisor', false))->first();
        if (!$empleado) {
            $this->markTestSkipped('No hay usuario empleado no supervisor en la base de datos');
        }
        Sanctum::actingAs($empleado);

        $response = $this->getJson('/api/v1/clientes');

        $response->assertStatus(403)
            ->assertJsonPath('error', 3101)
            ->assertJsonPath('respuesta', 'No tiene permiso para acceder a esta funcionalidad');
    }

    /**
     * Sin token recibe 401.
     */
    public function test_index_sin_token_retorna_401(): void
    {
        $response = $this->getJson('/api/v1/clientes');

        $response->assertStatus(401);
    }

    /**
     * Supervisor puede obtener tipos de cliente (200).
     */
    public function test_tipos_cliente_supervisor_retorna_200(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
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

    /**
     * Empleado no supervisor en tipos-cliente recibe 403.
     */
    public function test_tipos_cliente_empleado_retorna_403(): void
    {
        $empleado = User::whereHas('usuario', fn ($q) => $q->where('supervisor', false))->first();
        if (!$empleado) {
            $this->markTestSkipped('No hay usuario empleado no supervisor en la base de datos');
        }
        Sanctum::actingAs($empleado);

        $response = $this->getJson('/api/v1/tipos-cliente');

        $response->assertStatus(403)
            ->assertJsonPath('error', 3101);
    }

    /**
     * Filtros: search, tipo_cliente_id aplican correctamente (200).
     */
    public function test_index_con_filtros_retorna_200(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor en la base de datos');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->getJson('/api/v1/clientes?page=1&page_size=10&search=xyz&sort=nombre&sort_dir=asc');

        $response->assertStatus(200)
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.page', 1);
    }

    /**
     * TR-009: Supervisor puede crear cliente sin acceso al sistema (201).
     */
    public function test_store_supervisor_crea_cliente_sin_acceso_201(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor en la base de datos');
        }
        $tipoCliente = TipoCliente::where('activo', true)->where('inhabilitado', false)->first();
        if (!$tipoCliente) {
            $this->markTestSkipped('No hay tipo de cliente activo en la base de datos');
        }
        Sanctum::actingAs($supervisor);

        $code = 'TR009_' . uniqid();
        $response = $this->postJson('/api/v1/clientes', [
            'code' => $code,
            'nombre' => 'Cliente TR-009 Test',
            'tipo_cliente_id' => $tipoCliente->id,
            'activo' => true,
            'inhabilitado' => false,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.code', $code)
            ->assertJsonPath('resultado.nombre', 'Cliente TR-009 Test')
            ->assertJsonPath('resultado.tipo_cliente_id', $tipoCliente->id);
        $this->assertDatabaseHas('PQ_PARTES_CLIENTES', ['code' => $code]);
    }

    /**
     * TR-009: Empleado no supervisor recibe 403 al crear cliente.
     */
    public function test_store_empleado_retorna_403(): void
    {
        $empleado = User::whereHas('usuario', fn ($q) => $q->where('supervisor', false))->first();
        if (!$empleado) {
            $this->markTestSkipped('No hay usuario empleado no supervisor');
        }
        $tipoCliente = TipoCliente::where('activo', true)->where('inhabilitado', false)->first();
        if (!$tipoCliente) {
            $this->markTestSkipped('No hay tipo de cliente activo');
        }
        Sanctum::actingAs($empleado);

        $response = $this->postJson('/api/v1/clientes', [
            'code' => 'TR009_FORBIDDEN',
            'nombre' => 'Cliente Test',
            'tipo_cliente_id' => $tipoCliente->id,
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('error', 3101);
    }

    /**
     * TR-009: Código duplicado retorna 409.
     */
    public function test_store_codigo_duplicado_retorna_409(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        $existente = Cliente::first();
        if (!$existente) {
            $this->markTestSkipped('No hay cliente existente para probar duplicado');
        }
        $tipoCliente = TipoCliente::where('activo', true)->where('inhabilitado', false)->first();
        if (!$tipoCliente) {
            $this->markTestSkipped('No hay tipo de cliente activo');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->postJson('/api/v1/clientes', [
            'code' => $existente->code,
            'nombre' => 'Otro nombre',
            'tipo_cliente_id' => $tipoCliente->id,
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('error', ClienteService::ERROR_CODE_DUPLICATE);
    }

    /**
     * TR-009: Sin token retorna 401.
     */
    public function test_store_sin_token_retorna_401(): void
    {
        $tipoCliente = TipoCliente::where('activo', true)->where('inhabilitado', false)->first();
        if (!$tipoCliente) {
            $this->markTestSkipped('No hay tipo de cliente activo');
        }
        $response = $this->postJson('/api/v1/clientes', [
            'code' => 'TR009_401',
            'nombre' => 'Cliente Test',
            'tipo_cliente_id' => $tipoCliente->id,
        ]);
        $response->assertStatus(401);
    }

    /**
     * TR-009: Supervisor crea cliente con acceso al sistema (201) – User + Cliente con user_id.
     */
    public function test_store_supervisor_crea_cliente_con_acceso_201(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        $tipoCliente = TipoCliente::where('activo', true)->where('inhabilitado', false)->first();
        if (!$tipoCliente) {
            $this->markTestSkipped('No hay tipo de cliente activo');
        }
        Sanctum::actingAs($supervisor);

        $code = 'TR009ACC_' . uniqid();
        $response = $this->postJson('/api/v1/clientes', [
            'code' => $code,
            'nombre' => 'Cliente con acceso TR-009',
            'tipo_cliente_id' => $tipoCliente->id,
            'email' => 'tr009acc_' . uniqid() . '@test.local',
            'habilitar_acceso' => true,
            'password' => 'password123',
            'activo' => true,
            'inhabilitado' => false,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.code', $code)
            ->assertJsonPath('resultado.nombre', 'Cliente con acceso TR-009');
        $this->assertDatabaseHas('PQ_PARTES_CLIENTES', ['code' => $code]);
        $this->assertDatabaseHas('USERS', ['code' => $code]);
    }

    /**
     * TR-009: Email duplicado retorna 409.
     */
    public function test_store_email_duplicado_retorna_409(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        $existente = Cliente::whereNotNull('email')->where('email', '!=', '')->first();
        if (!$existente) {
            $this->markTestSkipped('No hay cliente con email para probar duplicado');
        }
        $tipoCliente = TipoCliente::where('activo', true)->where('inhabilitado', false)->first();
        if (!$tipoCliente) {
            $this->markTestSkipped('No hay tipo de cliente activo');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->postJson('/api/v1/clientes', [
            'code' => 'TR009_EMAIL_' . uniqid(),
            'nombre' => 'Otro nombre',
            'tipo_cliente_id' => $tipoCliente->id,
            'email' => $existente->email,
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('error', ClienteService::ERROR_EMAIL_DUPLICATE);
    }

    /**
     * TR-009: Validación – nombre vacío retorna 422.
     */
    public function test_store_nombre_vacio_retorna_422(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        $tipoCliente = TipoCliente::where('activo', true)->where('inhabilitado', false)->first();
        if (!$tipoCliente) {
            $this->markTestSkipped('No hay tipo de cliente activo');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->postJson('/api/v1/clientes', [
            'code' => 'TR009_422_' . uniqid(),
            'nombre' => '',
            'tipo_cliente_id' => $tipoCliente->id,
        ]);

        $response->assertStatus(422);
    }

    /**
     * TR-009: Habilitar acceso sin contraseña retorna 422 (validación en servicio).
     */
    public function test_store_habilitar_acceso_sin_password_retorna_422(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        $tipoCliente = TipoCliente::where('activo', true)->where('inhabilitado', false)->first();
        if (!$tipoCliente) {
            $this->markTestSkipped('No hay tipo de cliente activo');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->postJson('/api/v1/clientes', [
            'code' => 'TR009_NOPASS_' . uniqid(),
            'nombre' => 'Cliente sin pass',
            'tipo_cliente_id' => $tipoCliente->id,
            'habilitar_acceso' => true,
        ]);

        $response->assertStatus(422);
    }

    /**
     * TR-010: GET /clientes/{id} – supervisor obtiene detalle 200.
     */
    public function test_show_supervisor_retorna_200(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        $cliente = Cliente::with('tipoCliente')->first();
        if (!$cliente) {
            $this->markTestSkipped('No hay cliente en la base de datos');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->getJson('/api/v1/clientes/' . $cliente->id);

        $response->assertStatus(200)
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.id', $cliente->id)
            ->assertJsonPath('resultado.code', $cliente->code)
            ->assertJsonPath('resultado.nombre', $cliente->nombre)
            ->assertJsonPath('resultado.tipo_cliente_id', $cliente->tipo_cliente_id);
        $this->assertArrayHasKey('tiene_acceso', $response->json('resultado'));
    }

    /**
     * TR-010: GET /clientes/{id} – id inexistente retorna 404.
     */
    public function test_show_id_inexistente_retorna_404(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->getJson('/api/v1/clientes/999999');

        $response->assertStatus(404)
            ->assertJsonPath('error', ClienteService::ERROR_NOT_FOUND);
    }

    /**
     * TR-010: GET /clientes/{id} – empleado no supervisor retorna 403.
     */
    public function test_show_empleado_retorna_403(): void
    {
        $empleado = User::whereHas('usuario', fn ($q) => $q->where('supervisor', false))->first();
        if (!$empleado) {
            $this->markTestSkipped('No hay empleado no supervisor');
        }
        $cliente = Cliente::first();
        if (!$cliente) {
            $this->markTestSkipped('No hay cliente');
        }
        Sanctum::actingAs($empleado);

        $response = $this->getJson('/api/v1/clientes/' . $cliente->id);

        $response->assertStatus(403)
            ->assertJsonPath('error', 3101);
    }

    /**
     * TR-010: PUT /clientes/{id} – supervisor actualiza cliente 200.
     */
    public function test_update_supervisor_retorna_200(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        $cliente = Cliente::with('tipoCliente')->first();
        if (!$cliente) {
            $this->markTestSkipped('No hay cliente');
        }
        $tipoCliente = TipoCliente::where('activo', true)->where('inhabilitado', false)->first();
        if (!$tipoCliente) {
            $this->markTestSkipped('No hay tipo de cliente activo');
        }
        Sanctum::actingAs($supervisor);

        $nuevoNombre = 'Cliente TR-010 Actualizado ' . uniqid();
        $response = $this->putJson('/api/v1/clientes/' . $cliente->id, [
            'nombre' => $nuevoNombre,
            'tipo_cliente_id' => $tipoCliente->id,
            'email' => $cliente->email,
            'activo' => $cliente->activo,
            'inhabilitado' => $cliente->inhabilitado,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.nombre', $nuevoNombre)
            ->assertJsonPath('resultado.id', $cliente->id);
        $this->assertDatabaseHas('PQ_PARTES_CLIENTES', ['id' => $cliente->id, 'nombre' => $nuevoNombre]);
    }

    /**
     * TR-010: PUT /clientes/{id} – id inexistente retorna 404.
     */
    public function test_update_id_inexistente_retorna_404(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        $tipoCliente = TipoCliente::where('activo', true)->where('inhabilitado', false)->first();
        if (!$tipoCliente) {
            $this->markTestSkipped('No hay tipo de cliente activo');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->putJson('/api/v1/clientes/999999', [
            'nombre' => 'Nombre',
            'tipo_cliente_id' => $tipoCliente->id,
        ]);

        $response->assertStatus(404)
            ->assertJsonPath('error', ClienteService::ERROR_NOT_FOUND);
    }

    /**
     * TR-010: PUT /clientes/{id} – nombre vacío retorna 422.
     */
    public function test_update_nombre_vacio_retorna_422(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        $cliente = Cliente::first();
        if (!$cliente) {
            $this->markTestSkipped('No hay cliente');
        }
        $tipoCliente = TipoCliente::where('activo', true)->where('inhabilitado', false)->first();
        if (!$tipoCliente) {
            $this->markTestSkipped('No hay tipo de cliente activo');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->putJson('/api/v1/clientes/' . $cliente->id, [
            'nombre' => '',
            'tipo_cliente_id' => $tipoCliente->id,
        ]);

        $response->assertStatus(422);
    }

    /**
     * TR-011: DELETE /clientes/{id} – supervisor elimina cliente sin tareas 200.
     */
    public function test_destroy_supervisor_sin_tareas_retorna_200(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        $tipoCliente = TipoCliente::where('activo', true)->where('inhabilitado', false)->first();
        if (!$tipoCliente) {
            $this->markTestSkipped('No hay tipo de cliente activo');
        }
        Sanctum::actingAs($supervisor);

        $code = 'TR011_DEL_' . uniqid();
        $email = 'tr011_del_' . uniqid() . '@test.local';
        $cliente = Cliente::create([
            'code' => $code,
            'nombre' => 'Cliente a eliminar TR-011',
            'tipo_cliente_id' => $tipoCliente->id,
            'email' => $email,
            'activo' => true,
            'inhabilitado' => false,
        ]);
        $id = $cliente->id;

        $response = $this->deleteJson('/api/v1/clientes/' . $id);

        $response->assertStatus(200)
            ->assertJsonPath('error', 0)
            ->assertJsonPath('respuesta', 'Cliente eliminado correctamente');
        $this->assertDatabaseMissing('PQ_PARTES_CLIENTES', ['id' => $id]);
    }

    /**
     * TR-011: DELETE /clientes/{id} – cliente con tareas retorna 422 (2112).
     */
    public function test_destroy_cliente_con_tareas_retorna_422(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        $cliente = Cliente::first();
        if (!$cliente) {
            $this->markTestSkipped('No hay cliente');
        }
        $usuario = \App\Models\Usuario::first();
        $tipoTarea = \App\Models\TipoTarea::where('activo', true)->first();
        if (!$usuario || !$tipoTarea) {
            $this->markTestSkipped('No hay usuario o tipo de tarea para crear registro');
        }
        RegistroTarea::create([
            'usuario_id' => $usuario->id,
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'fecha' => now()->toDateString(),
            'duracion_minutos' => 60,
            'sin_cargo' => false,
            'presencial' => true,
            'observacion' => 'Tarea test TR-011',
            'cerrado' => false,
        ]);
        Sanctum::actingAs($supervisor);

        $response = $this->deleteJson('/api/v1/clientes/' . $cliente->id);

        $response->assertStatus(422)
            ->assertJsonPath('error', ClienteService::ERROR_TIENE_TAREAS)
            ->assertJsonPath('respuesta', 'No se puede eliminar un cliente que tiene tareas asociadas.');
        $this->assertDatabaseHas('PQ_PARTES_CLIENTES', ['id' => $cliente->id]);
    }

    /**
     * TR-011: DELETE /clientes/{id} – id inexistente retorna 404.
     */
    public function test_destroy_id_inexistente_retorna_404(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->deleteJson('/api/v1/clientes/999999');

        $response->assertStatus(404)
            ->assertJsonPath('error', ClienteService::ERROR_NOT_FOUND);
    }

    /**
     * TR-011: DELETE /clientes/{id} – empleado no supervisor retorna 403.
     */
    public function test_destroy_empleado_retorna_403(): void
    {
        $empleado = User::whereHas('usuario', fn ($q) => $q->where('supervisor', false))->first();
        if (!$empleado) {
            $this->markTestSkipped('No hay empleado no supervisor');
        }
        $cliente = Cliente::first();
        if (!$cliente) {
            $this->markTestSkipped('No hay cliente');
        }
        Sanctum::actingAs($empleado);

        $response = $this->deleteJson('/api/v1/clientes/' . $cliente->id);

        $response->assertStatus(403)
            ->assertJsonPath('error', 3101);
    }

    // --- TR-012: GET/PUT /api/v1/clientes/{id}/tipos-tarea ---

    /**
     * TR-012: GET tipos-tarea – supervisor retorna 200 con array (asignados).
     */
    public function test_tipos_tarea_index_supervisor_retorna_200(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        $cliente = Cliente::first();
        if (!$cliente) {
            $this->markTestSkipped('No hay cliente');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->getJson('/api/v1/clientes/' . $cliente->id . '/tipos-tarea');

        $response->assertStatus(200)
            ->assertJsonPath('error', 0)
            ->assertJsonPath('respuesta', 'Tipos de tarea obtenidos correctamente');
        $this->assertIsArray($response->json('resultado'));
    }

    /**
     * TR-012: GET tipos-tarea – cliente inexistente retorna 404.
     */
    public function test_tipos_tarea_index_cliente_inexistente_retorna_404(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->getJson('/api/v1/clientes/999999/tipos-tarea');

        $response->assertStatus(404)
            ->assertJsonPath('error', ClienteService::ERROR_NOT_FOUND);
    }

    /**
     * TR-012: GET tipos-tarea – empleado no supervisor retorna 403.
     */
    public function test_tipos_tarea_index_empleado_retorna_403(): void
    {
        $empleado = User::whereHas('usuario', fn ($q) => $q->where('supervisor', false))->first();
        if (!$empleado) {
            $this->markTestSkipped('No hay empleado no supervisor');
        }
        $cliente = Cliente::first();
        if (!$cliente) {
            $this->markTestSkipped('No hay cliente');
        }
        Sanctum::actingAs($empleado);

        $response = $this->getJson('/api/v1/clientes/' . $cliente->id . '/tipos-tarea');

        $response->assertStatus(403)
            ->assertJsonPath('error', 3101);
    }

    /**
     * TR-012: PUT tipos-tarea – supervisor con ids válidos (no genéricos) retorna 200.
     */
    public function test_tipos_tarea_update_supervisor_ids_validos_retorna_200(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        $cliente = Cliente::first();
        if (!$cliente) {
            $this->markTestSkipped('No hay cliente');
        }
        $tipoNoGenerico = TipoTarea::where('is_generico', false)->where('activo', true)->where('inhabilitado', false)->first();
        if (!$tipoNoGenerico) {
            $this->markTestSkipped('No hay tipo de tarea no genérico activo');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->putJson('/api/v1/clientes/' . $cliente->id . '/tipos-tarea', [
            'tipo_tarea_ids' => [$tipoNoGenerico->id],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('error', 0)
            ->assertJsonPath('respuesta', 'Tipos de tarea actualizados correctamente');
        $resultado = $response->json('resultado');
        $this->assertIsArray($resultado);
        $this->assertCount(1, $resultado);
        $this->assertEquals($tipoNoGenerico->id, $resultado[0]['id']);
        $this->assertDatabaseHas('PQ_PARTES_CLIENTE_TIPO_TAREA', [
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoNoGenerico->id,
        ]);
    }

    /**
     * TR-012: PUT tipos-tarea – lista vacía cuando hay genéricos retorna 200.
     */
    public function test_tipos_tarea_update_lista_vacia_con_genericos_retorna_200(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        $cliente = Cliente::first();
        if (!$cliente) {
            $this->markTestSkipped('No hay cliente');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->putJson('/api/v1/clientes/' . $cliente->id . '/tipos-tarea', [
            'tipo_tarea_ids' => [],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('error', 0);
        $this->assertCount(0, $response->json('resultado'));
    }

    /**
     * TR-012: PUT tipos-tarea – lista vacía sin genéricos retorna 422 (2116).
     */
    public function test_tipos_tarea_update_lista_vacia_sin_genericos_retorna_422_2116(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        $cliente = Cliente::first();
        if (!$cliente) {
            $this->markTestSkipped('No hay cliente');
        }
        TipoTarea::where('is_generico', true)->update(['activo' => false]);
        Sanctum::actingAs($supervisor);

        $response = $this->putJson('/api/v1/clientes/' . $cliente->id . '/tipos-tarea', [
            'tipo_tarea_ids' => [],
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error', ClienteService::ERROR_SIN_TIPOS_TAREA)
            ->assertJsonPath('respuesta', 'El cliente debe tener al menos un tipo de tarea disponible (genérico o asignado).');
    }

    /**
     * TR-012: PUT tipos-tarea – id de tipo genérico retorna 422 (2118).
     */
    public function test_tipos_tarea_update_tipo_generico_retorna_422_2118(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        $cliente = Cliente::first();
        if (!$cliente) {
            $this->markTestSkipped('No hay cliente');
        }
        $tipoGenerico = TipoTarea::where('is_generico', true)->where('activo', true)->first();
        if (!$tipoGenerico) {
            $this->markTestSkipped('No hay tipo de tarea genérico');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->putJson('/api/v1/clientes/' . $cliente->id . '/tipos-tarea', [
            'tipo_tarea_ids' => [$tipoGenerico->id],
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error', ClienteService::ERROR_TIPO_GENERICO)
            ->assertJsonPath('respuesta', 'No se puede asignar un tipo de tarea genérico a un cliente.');
    }

    /**
     * TR-012: PUT tipos-tarea – cliente inexistente retorna 404.
     */
    public function test_tipos_tarea_update_cliente_inexistente_retorna_404(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->putJson('/api/v1/clientes/999999/tipos-tarea', [
            'tipo_tarea_ids' => [1],
        ]);

        $response->assertStatus(404)
            ->assertJsonPath('error', ClienteService::ERROR_NOT_FOUND);
    }

    /**
     * TR-012: PUT tipos-tarea – empleado no supervisor retorna 403.
     */
    public function test_tipos_tarea_update_empleado_retorna_403(): void
    {
        $empleado = User::whereHas('usuario', fn ($q) => $q->where('supervisor', false))->first();
        if (!$empleado) {
            $this->markTestSkipped('No hay empleado no supervisor');
        }
        $cliente = Cliente::first();
        if (!$cliente) {
            $this->markTestSkipped('No hay cliente');
        }
        Sanctum::actingAs($empleado);

        $response = $this->putJson('/api/v1/clientes/' . $cliente->id . '/tipos-tarea', [
            'tipo_tarea_ids' => [],
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('error', 3101);
    }
}
