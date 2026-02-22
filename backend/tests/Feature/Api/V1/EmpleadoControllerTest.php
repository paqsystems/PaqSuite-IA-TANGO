<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Models\Usuario;
use App\Services\EmpleadoService;
use App\Http\Controllers\Api\V1\EmpleadoController;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Tests de Integración: EmpleadoController (TR-018, TR-019, TR-020, TR-021)
 *
 * - GET /api/v1/empleados - Listado paginado (solo supervisores)
 * - POST /api/v1/empleados - Crear empleado (solo supervisores)
 * - GET /api/v1/empleados/{id} - Obtener empleado (solo supervisores)
 * - PUT /api/v1/empleados/{id} - Actualizar empleado (solo supervisores)
 * - DELETE /api/v1/empleados/{id} - Eliminar empleado (solo supervisores)
 *
 * @see TR-018(MH)-listado-de-empleados.md
 * @see TR-019(MH)-creación-de-empleado.md
 * @see TR-020(MH)-edición-de-empleado.md
 * @see TR-021(MH)-eliminación-de-empleado.md
 */
class EmpleadoControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Supervisor puede listar empleados (200).
     */
    public function test_index_supervisor_retorna_200_con_items(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor en la base de datos');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->getJson('/api/v1/empleados?page=1&page_size=5');

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
            $this->assertArrayHasKey('email', $items[0]);
            $this->assertArrayHasKey('supervisor', $items[0]);
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

        $response = $this->getJson('/api/v1/empleados');

        $response->assertStatus(403)
            ->assertJsonPath('error', 3101)
            ->assertJsonPath('respuesta', 'No tiene permiso para acceder a esta funcionalidad');
    }

    /**
     * Sin token recibe 401.
     */
    public function test_index_sin_token_retorna_401(): void
    {
        $response = $this->getJson('/api/v1/empleados');

        $response->assertStatus(401);
    }

    /**
     * Filtros: search, supervisor, activo, inhabilitado aplican correctamente (200).
     */
    public function test_index_con_filtros_retorna_200(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor en la base de datos');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->getJson('/api/v1/empleados?page=1&page_size=10&search=test&supervisor=false&activo=true&inhabilitado=false&sort=nombre&sort_dir=asc');

        $response->assertStatus(200)
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.page', 1);
    }

    /**
     * Búsqueda por código, nombre o email funciona correctamente.
     */
    public function test_index_busqueda_por_codigo_nombre_email(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor en la base de datos');
        }
        Sanctum::actingAs($supervisor);

        // Crear un empleado de prueba si no existe
        $usuario = Usuario::first();
        if ($usuario) {
            $searchTerm = substr($usuario->code, 0, 3);
            $response = $this->getJson("/api/v1/empleados?search={$searchTerm}");
            $response->assertStatus(200)
                ->assertJsonPath('error', 0);
        } else {
            $this->markTestSkipped('No hay empleados en la base de datos para probar búsqueda');
        }
    }

    /**
     * Filtro por supervisor funciona correctamente.
     */
    public function test_index_filtro_supervisor(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor en la base de datos');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->getJson('/api/v1/empleados?supervisor=true');
        $response->assertStatus(200)
            ->assertJsonPath('error', 0);

        $items = $response->json('resultado.items');
        foreach ($items as $item) {
            $this->assertTrue($item['supervisor']);
        }
    }

    /**
     * Filtro por activo funciona correctamente.
     */
    public function test_index_filtro_activo(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor en la base de datos');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->getJson('/api/v1/empleados?activo=true');
        $response->assertStatus(200)
            ->assertJsonPath('error', 0);

        $items = $response->json('resultado.items');
        foreach ($items as $item) {
            $this->assertTrue($item['activo']);
        }
    }

    /**
     * Filtro por inhabilitado funciona correctamente.
     */
    public function test_index_filtro_inhabilitado(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor en la base de datos');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->getJson('/api/v1/empleados?inhabilitado=false');
        $response->assertStatus(200)
            ->assertJsonPath('error', 0);

        $items = $response->json('resultado.items');
        foreach ($items as $item) {
            $this->assertFalse($item['inhabilitado']);
        }
    }

    /**
     * Paginación funciona correctamente.
     */
    public function test_index_paginacion(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor en la base de datos');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->getJson('/api/v1/empleados?page=1&page_size=2');
        $response->assertStatus(200)
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.page', 1)
            ->assertJsonPath('resultado.page_size', 2);

        $items = $response->json('resultado.items');
        $this->assertLessThanOrEqual(2, count($items));
    }

    /**
     * TR-019: Supervisor puede crear empleado correctamente (201).
     */
    public function test_store_supervisor_crea_empleado_201(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor en la base de datos');
        }
        Sanctum::actingAs($supervisor);

        $code = 'TR019_' . uniqid();
        $response = $this->postJson('/api/v1/empleados', [
            'code' => $code,
            'nombre' => 'Empleado TR-019 Test',
            'email' => 'test' . uniqid() . '@ejemplo.com',
            'password' => 'password123',
            'supervisor' => false,
            'activo' => true,
            'inhabilitado' => false,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.code', $code)
            ->assertJsonPath('resultado.nombre', 'Empleado TR-019 Test')
            ->assertJsonPath('resultado.supervisor', false);
        $this->assertDatabaseHas('PQ_PARTES_USUARIOS', ['code' => $code]);
        $this->assertDatabaseHas('USERS', ['code' => $code]);
    }

    /**
     * TR-019: Empleado no supervisor recibe 403 al crear empleado.
     */
    public function test_store_empleado_retorna_403(): void
    {
        $empleado = User::whereHas('usuario', fn ($q) => $q->where('supervisor', false))->first();
        if (!$empleado) {
            $this->markTestSkipped('No hay usuario empleado no supervisor');
        }
        Sanctum::actingAs($empleado);

        $response = $this->postJson('/api/v1/empleados', [
            'code' => 'TR019_FORBIDDEN',
            'nombre' => 'Empleado Test',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('error', 3101);
    }

    /**
     * TR-019: Código duplicado en USERS retorna 409.
     */
    public function test_store_codigo_duplicado_retorna_409(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        $existente = User::first();
        if (!$existente) {
            $this->markTestSkipped('No hay usuario existente para probar duplicado');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->postJson('/api/v1/empleados', [
            'code' => $existente->code,
            'nombre' => 'Otro nombre',
            'password' => 'password123',
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('error', \App\Services\EmpleadoService::ERROR_CODE_DUPLICATE);
    }

    /**
     * TR-019: Email duplicado retorna 409.
     */
    public function test_store_email_duplicado_retorna_409(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        $existente = Usuario::whereNotNull('email')->where('email', '!=', '')->first();
        if (!$existente || !$existente->email) {
            $this->markTestSkipped('No hay empleado con email para probar duplicado');
        }
        Sanctum::actingAs($supervisor);

        $code = 'TR019_EMAIL_' . uniqid();
        $response = $this->postJson('/api/v1/empleados', [
            'code' => $code,
            'nombre' => 'Otro nombre',
            'email' => $existente->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('error', \App\Services\EmpleadoService::ERROR_EMAIL_DUPLICATE);
    }

    /**
     * TR-019: Sin token retorna 401.
     */
    public function test_store_sin_token_retorna_401(): void
    {
        $response = $this->postJson('/api/v1/empleados', [
            'code' => 'TR019_401',
            'nombre' => 'Empleado Test',
            'password' => 'password123',
        ]);

        $response->assertStatus(401);
    }

    /**
     * TR-019: Validación: código requerido retorna 422.
     */
    public function test_store_codigo_requerido_retorna_422(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->postJson('/api/v1/empleados', [
            'nombre' => 'Empleado Test',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
    }

    /**
     * TR-019: Validación: nombre requerido retorna 422.
     */
    public function test_store_nombre_requerido_retorna_422(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->postJson('/api/v1/empleados', [
            'code' => 'TR019_NOMBRE',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
    }

    /**
     * TR-019: Validación: contraseña requerida retorna 422.
     */
    public function test_store_password_requerido_retorna_422(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->postJson('/api/v1/empleados', [
            'code' => 'TR019_PASS',
            'nombre' => 'Empleado Test',
        ]);

        $response->assertStatus(422);
    }

    /**
     * TR-019: Validación: contraseña muy corta retorna 422.
     */
    public function test_store_password_corto_retorna_422(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->postJson('/api/v1/empleados', [
            'code' => 'TR019_SHORT',
            'nombre' => 'Empleado Test',
            'password' => '123',
        ]);

        $response->assertStatus(422);
    }

    /**
     * TR-019: Validación: email formato inválido retorna 422.
     */
    public function test_store_email_invalido_retorna_422(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->postJson('/api/v1/empleados', [
            'code' => 'TR019_EMAIL',
            'nombre' => 'Empleado Test',
            'email' => 'email-invalido',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
    }

    /**
     * TR-020: GET /empleados/{id} – supervisor obtiene detalle 200.
     */
    public function test_show_supervisor_retorna_200(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        $empleado = Usuario::first();
        if (!$empleado) {
            $this->markTestSkipped('No hay empleado en la base de datos');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->getJson('/api/v1/empleados/' . $empleado->id);

        $response->assertStatus(200)
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.id', $empleado->id)
            ->assertJsonPath('resultado.code', $empleado->code)
            ->assertJsonPath('resultado.nombre', $empleado->nombre)
            ->assertJsonStructure([
                'resultado' => [
                    'id',
                    'code',
                    'nombre',
                    'email',
                    'supervisor',
                    'activo',
                    'inhabilitado',
                ],
            ]);
    }

    /**
     * TR-022: GET /empleados/{id} con include_stats=true incluye total_tareas.
     */
    public function test_show_con_include_stats_incluye_total_tareas(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        $empleado = Usuario::first();
        if (!$empleado) {
            $this->markTestSkipped('No hay empleado en la base de datos');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->getJson('/api/v1/empleados/' . $empleado->id . '?include_stats=true');

        $response->assertStatus(200)
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.id', $empleado->id)
            ->assertJsonStructure([
                'resultado' => [
                    'id',
                    'code',
                    'nombre',
                    'email',
                    'supervisor',
                    'activo',
                    'inhabilitado',
                    'total_tareas',
                ],
            ]);
        $resultado = $response->json('resultado');
        $this->assertIsInt($resultado['total_tareas']);
        $this->assertGreaterThanOrEqual(0, $resultado['total_tareas']);
    }

    /**
     * TR-022: GET /empleados/{id} sin include_stats no incluye total_tareas (solo datos básicos).
     */
    public function test_show_sin_include_stats_no_incluye_total_tareas(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        $empleado = Usuario::first();
        if (!$empleado) {
            $this->markTestSkipped('No hay empleado en la base de datos');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->getJson('/api/v1/empleados/' . $empleado->id);

        $response->assertStatus(200)
            ->assertJsonPath('error', 0);
        $resultado = $response->json('resultado');
        $this->assertArrayNotHasKey('total_tareas', $resultado);
    }

    /**
     * TR-020: GET /empleados/{id} – id inexistente retorna 404.
     */
    public function test_show_id_inexistente_retorna_404(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->getJson('/api/v1/empleados/999999');

        $response->assertStatus(404)
            ->assertJsonPath('error', \App\Services\EmpleadoService::ERROR_NOT_FOUND);
    }

    /**
     * TR-020: GET /empleados/{id} – empleado no supervisor retorna 403.
     */
    public function test_show_empleado_retorna_403(): void
    {
        $empleado = User::whereHas('usuario', fn ($q) => $q->where('supervisor', false))->first();
        if (!$empleado) {
            $this->markTestSkipped('No hay empleado no supervisor');
        }
        $empleadoUsuario = Usuario::first();
        if (!$empleadoUsuario) {
            $this->markTestSkipped('No hay empleado');
        }
        Sanctum::actingAs($empleado);

        $response = $this->getJson('/api/v1/empleados/' . $empleadoUsuario->id);

        $response->assertStatus(403)
            ->assertJsonPath('error', 3101);
    }

    /**
     * TR-020: GET /empleados/{id} – sin token retorna 401.
     */
    public function test_show_sin_token_retorna_401(): void
    {
        $empleado = Usuario::first();
        if (!$empleado) {
            $this->markTestSkipped('No hay empleado');
        }
        $response = $this->getJson('/api/v1/empleados/' . $empleado->id);

        $response->assertStatus(401);
    }

    /**
     * TR-020: PUT /empleados/{id} – supervisor actualiza empleado correctamente 200.
     */
    public function test_update_supervisor_actualiza_empleado_200(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        $empleado = Usuario::first();
        if (!$empleado) {
            $this->markTestSkipped('No hay empleado en la base de datos');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->putJson('/api/v1/empleados/' . $empleado->id, [
            'nombre' => 'Empleado Actualizado TR-020',
            'email' => 'actualizado' . uniqid() . '@ejemplo.com',
            'supervisor' => false,
            'activo' => true,
            'inhabilitado' => false,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.nombre', 'Empleado Actualizado TR-020')
            ->assertJsonPath('resultado.code', $empleado->code); // Code no modificable
        $this->assertDatabaseHas('PQ_PARTES_USUARIOS', [
            'id' => $empleado->id,
            'nombre' => 'Empleado Actualizado TR-020',
        ]);
    }

    /**
     * TR-020: PUT /empleados/{id} – cambiar contraseña actualiza USERS.password_hash.
     */
    public function test_update_cambiar_password_actualiza_users(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        $empleado = Usuario::whereNotNull('user_id')->first();
        if (!$empleado || !$empleado->user_id) {
            $this->markTestSkipped('No hay empleado con user_id');
        }
        Sanctum::actingAs($supervisor);

        $oldPasswordHash = User::find($empleado->user_id)->password_hash;

        $response = $this->putJson('/api/v1/empleados/' . $empleado->id, [
            'nombre' => $empleado->nombre,
            'password' => 'nuevaPassword123',
        ]);

        $response->assertStatus(200);
        $newPasswordHash = User::find($empleado->user_id)->password_hash;
        $this->assertNotEquals($oldPasswordHash, $newPasswordHash);
    }

    /**
     * TR-020: PUT /empleados/{id} – cambiar activo/inhabilitado sincroniza USERS.
     */
    public function test_update_cambiar_estado_sincroniza_users(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        $empleado = Usuario::whereNotNull('user_id')->first();
        if (!$empleado || !$empleado->user_id) {
            $this->markTestSkipped('No hay empleado con user_id');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->putJson('/api/v1/empleados/' . $empleado->id, [
            'nombre' => $empleado->nombre,
            'activo' => false,
            'inhabilitado' => true,
        ]);

        $response->assertStatus(200);
        $user = User::find($empleado->user_id);
        $this->assertFalse($user->activo);
        $this->assertTrue($user->inhabilitado);
        $empleadoActualizado = Usuario::find($empleado->id);
        $this->assertFalse($empleadoActualizado->activo);
        $this->assertTrue($empleadoActualizado->inhabilitado);
    }

    /**
     * TR-020: PUT /empleados/{id} – email duplicado retorna 409.
     */
    public function test_update_email_duplicado_retorna_409(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        $empleados = Usuario::whereNotNull('email')->where('email', '!=', '')->limit(2)->get();
        if ($empleados->count() < 2) {
            $this->markTestSkipped('No hay suficientes empleados con email para probar duplicado');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->putJson('/api/v1/empleados/' . $empleados[0]->id, [
            'nombre' => $empleados[0]->nombre,
            'email' => $empleados[1]->email,
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('error', \App\Services\EmpleadoService::ERROR_EMAIL_DUPLICATE);
    }

    /**
     * TR-020: PUT /empleados/{id} – id inexistente retorna 404.
     */
    public function test_update_id_inexistente_retorna_404(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->putJson('/api/v1/empleados/999999', [
            'nombre' => 'Test',
        ]);

        $response->assertStatus(404)
            ->assertJsonPath('error', \App\Services\EmpleadoService::ERROR_NOT_FOUND);
    }

    /**
     * TR-020: PUT /empleados/{id} – empleado no supervisor retorna 403.
     */
    public function test_update_empleado_retorna_403(): void
    {
        $empleado = User::whereHas('usuario', fn ($q) => $q->where('supervisor', false))->first();
        if (!$empleado) {
            $this->markTestSkipped('No hay empleado no supervisor');
        }
        $empleadoUsuario = Usuario::first();
        if (!$empleadoUsuario) {
            $this->markTestSkipped('No hay empleado');
        }
        Sanctum::actingAs($empleado);

        $response = $this->putJson('/api/v1/empleados/' . $empleadoUsuario->id, [
            'nombre' => 'Test',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('error', 3101);
    }

    /**
     * TR-020: PUT /empleados/{id} – sin token retorna 401.
     */
    public function test_update_sin_token_retorna_401(): void
    {
        $empleado = Usuario::first();
        if (!$empleado) {
            $this->markTestSkipped('No hay empleado');
        }
        $response = $this->putJson('/api/v1/empleados/' . $empleado->id, [
            'nombre' => 'Test',
        ]);

        $response->assertStatus(401);
    }

    /**
     * TR-020: PUT /empleados/{id} – nombre requerido retorna 422.
     */
    public function test_update_nombre_requerido_retorna_422(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        $empleado = Usuario::first();
        if (!$empleado) {
            $this->markTestSkipped('No hay empleado');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->putJson('/api/v1/empleados/' . $empleado->id, [
            'nombre' => '',
        ]);

        $response->assertStatus(422);
    }

    /**
     * TR-020: PUT /empleados/{id} – contraseña corta retorna 422.
     */
    public function test_update_password_corto_retorna_422(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        $empleado = Usuario::first();
        if (!$empleado) {
            $this->markTestSkipped('No hay empleado');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->putJson('/api/v1/empleados/' . $empleado->id, [
            'nombre' => $empleado->nombre,
            'password' => '123',
        ]);

        $response->assertStatus(422);
    }

    /**
     * TR-020: PUT /empleados/{id} – code no modificable (se ignora si se envía).
     */
    public function test_update_code_no_modificable(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        $empleado = Usuario::first();
        if (!$empleado) {
            $this->markTestSkipped('No hay empleado');
        }
        Sanctum::actingAs($supervisor);

        $codeOriginal = $empleado->code;
        $response = $this->putJson('/api/v1/empleados/' . $empleado->id, [
            'nombre' => 'Test',
            'code' => 'CODIGO_MODIFICADO', // Se debe ignorar
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('resultado.code', $codeOriginal); // Code no cambió
    }

    /**
     * TR-021: DELETE /empleados/{id} – supervisor elimina empleado sin tareas retorna 200.
     */
    public function test_destroy_supervisor_elimina_empleado_sin_tareas_200(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        // Crear un empleado sin tareas para eliminar
        $empleado = Usuario::whereDoesntHave('registrosTarea')->first();
        if (!$empleado) {
            $this->markTestSkipped('No hay empleado sin tareas para eliminar');
        }
        $empleadoId = $empleado->id;
        Sanctum::actingAs($supervisor);

        $response = $this->deleteJson('/api/v1/empleados/' . $empleadoId);

        $response->assertStatus(200)
            ->assertJsonPath('error', 0)
            ->assertJsonPath('respuesta', 'Empleado eliminado correctamente');
        $this->assertEmpty($response->json('resultado'));

        // Verificar que el empleado fue eliminado
        $this->assertNull(Usuario::find($empleadoId));
    }

    /**
     * TR-021: DELETE /empleados/{id} – empleado con tareas retorna 422 (2113).
     */
    public function test_destroy_empleado_con_tareas_retorna_422(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        // Buscar un empleado que tenga tareas asociadas
        $empleado = Usuario::has('registrosTarea')->first();
        if (!$empleado) {
            $this->markTestSkipped('No hay empleado con tareas para probar');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->deleteJson('/api/v1/empleados/' . $empleado->id);

        $response->assertStatus(422)
            ->assertJsonPath('error', EmpleadoService::ERROR_TIENE_TAREAS)
            ->assertJsonPath('respuesta', 'No se puede eliminar un empleado que tiene tareas asociadas.');

        // Verificar que el empleado NO fue eliminado
        $this->assertNotNull(Usuario::find($empleado->id));
    }

    /**
     * TR-021: DELETE /empleados/{id} – id inexistente retorna 404.
     */
    public function test_destroy_id_inexistente_retorna_404(): void
    {
        $supervisor = User::whereHas('usuario', fn ($q) => $q->where('supervisor', true))->first();
        if (!$supervisor) {
            $this->markTestSkipped('No hay usuario supervisor');
        }
        Sanctum::actingAs($supervisor);

        $response = $this->deleteJson('/api/v1/empleados/99999');

        $response->assertStatus(404)
            ->assertJsonPath('error', EmpleadoService::ERROR_NOT_FOUND)
            ->assertJsonPath('respuesta', 'Empleado no encontrado.');
    }

    /**
     * TR-021: DELETE /empleados/{id} – empleado no supervisor retorna 403.
     */
    public function test_destroy_empleado_retorna_403(): void
    {
        $empleado = User::whereHas('usuario', fn ($q) => $q->where('supervisor', false))->first();
        if (!$empleado) {
            $this->markTestSkipped('No hay usuario empleado no supervisor');
        }
        $empleadoAEliminar = Usuario::first();
        if (!$empleadoAEliminar) {
            $this->markTestSkipped('No hay empleado para eliminar');
        }
        Sanctum::actingAs($empleado);

        $response = $this->deleteJson('/api/v1/empleados/' . $empleadoAEliminar->id);

        $response->assertStatus(403)
            ->assertJsonPath('error', EmpleadoController::ERROR_FORBIDDEN);
    }

    /**
     * TR-021: DELETE /empleados/{id} – sin token retorna 401.
     */
    public function test_destroy_sin_token_retorna_401(): void
    {
        $empleado = Usuario::first();
        if (!$empleado) {
            $this->markTestSkipped('No hay empleado');
        }

        $response = $this->deleteJson('/api/v1/empleados/' . $empleado->id);

        $response->assertStatus(401);
    }
}
