<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Models\Usuario;
use App\Models\Cliente;
use App\Models\TipoTarea;
use App\Models\RegistroTarea;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Tests de Integración: ReportController
 * - GET /api/v1/reports/detail (TR-044 Consulta detallada)
 * - GET /api/v1/reports/by-client (TR-046 Consulta agrupada por cliente)
 * - GET /api/v1/reports/by-employee (TR-045 Consulta agrupada por empleado)
 * - GET /api/v1/reports/by-task-type (TR-047 Consulta agrupada por tipo de tarea)
 * - GET /api/v1/reports/by-date (TR-048 Consulta agrupada por fecha)
 *
 * @see TR-044(MH)-consulta-detallada-de-tareas.md
 * @see TR-045(SH)-consulta-agrupada-por-empleado.md
 * @see TR-046(MH)-consulta-agrupada-por-cliente.md
 * @see TR-047(SH)-consulta-agrupada-por-tipo-de-tarea.md
 * @see TR-048(SH)-consulta-agrupada-por-fecha.md
 */
class ReportControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedTestData();
    }

    protected function seedTestData(): void
    {
        $testCodes = ['JPEREZ', 'MGARCIA', 'CLI001'];
        $clienteIds = DB::table('PQ_PARTES_CLIENTES')->whereIn('code', $testCodes)->pluck('id');
        $usuarioIds = DB::table('PQ_PARTES_USUARIOS')->whereIn('code', $testCodes)->pluck('id');
        if ($clienteIds->isNotEmpty()) {
            DB::table('PQ_PARTES_REGISTRO_TAREA')->whereIn('cliente_id', $clienteIds)->delete();
        }
        if ($usuarioIds->isNotEmpty()) {
            DB::table('PQ_PARTES_REGISTRO_TAREA')->whereIn('usuario_id', $usuarioIds)->delete();
        }
        DB::table('PQ_PARTES_USUARIOS')->whereIn('code', $testCodes)->delete();
        DB::table('PQ_PARTES_CLIENTES')->whereIn('code', $testCodes)->delete();
        $userIds = DB::table('USERS')->whereIn('code', $testCodes)->pluck('id');
        if ($userIds->isNotEmpty()) {
            DB::table('personal_access_tokens')
                ->where('tokenable_type', 'App\\Models\\User')
                ->whereIn('tokenable_id', $userIds)
                ->delete();
        }
        DB::table('USERS')->whereIn('code', $testCodes)->delete();

        $tipoClienteId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->first()?->id;
        if (!$tipoClienteId) {
            return;
        }

        DB::table('USERS')->insert([
            'code' => 'JPEREZ',
            'password_hash' => Hash::make('password123'),
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $jperezUserId = DB::table('USERS')->where('code', 'JPEREZ')->value('id');
        DB::table('PQ_PARTES_USUARIOS')->insert([
            'user_id' => $jperezUserId,
            'code' => 'JPEREZ',
            'nombre' => 'Juan Pérez',
            'email' => 'juan@ejemplo.com',
            'supervisor' => false,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('USERS')->insert([
            'code' => 'MGARCIA',
            'password_hash' => Hash::make('password456'),
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $mgarciaUserId = DB::table('USERS')->where('code', 'MGARCIA')->value('id');
        DB::table('PQ_PARTES_USUARIOS')->insert([
            'user_id' => $mgarciaUserId,
            'code' => 'MGARCIA',
            'nombre' => 'María García',
            'email' => 'maria@ejemplo.com',
            'supervisor' => true,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('USERS')->insert([
            'code' => 'CLI001',
            'password_hash' => Hash::make('cliente123'),
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $cli001UserId = DB::table('USERS')->where('code', 'CLI001')->value('id');
        DB::table('PQ_PARTES_CLIENTES')->insert([
            'user_id' => $cli001UserId,
            'code' => 'CLI001',
            'nombre' => 'Empresa ABC S.A.',
            'email' => 'contacto@abc.com',
            'tipo_cliente_id' => $tipoClienteId,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tipoTareaId = DB::table('PQ_PARTES_TIPOS_TAREA')->where('is_generico', true)->value('id');
        if (!$tipoTareaId) {
            return;
        }
        $empleado = Usuario::where('code', 'JPEREZ')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        if ($empleado && $cliente) {
            RegistroTarea::create([
                'usuario_id' => $empleado->id,
                'cliente_id' => $cliente->id,
                'tipo_tarea_id' => $tipoTareaId,
                'fecha' => '2026-01-28',
                'duracion_minutos' => 120,
                'sin_cargo' => false,
                'presencial' => true,
                'observacion' => 'Tarea consulta detallada',
                'cerrado' => false,
            ]);
        }
    }

    /** @test */
    public function detail_empleado_retorna_sus_tareas(): void
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/reports/detail?fecha_desde=2026-01-01&fecha_hasta=2026-01-31');

        $response->assertStatus(200)
            ->assertJson([
                'error' => 0,
                'respuesta' => 'Consulta obtenida correctamente',
            ])
            ->assertJsonStructure([
                'resultado' => [
                    'data',
                    'pagination' => ['current_page', 'per_page', 'total', 'last_page'],
                    'total_horas',
                ],
            ]);
        $this->assertGreaterThanOrEqual(1, count($response->json('resultado.data')));
        $this->assertArrayHasKey('total_horas', $response->json('resultado'));
    }

    /** @test */
    public function detail_supervisor_retorna_todas_las_tareas(): void
    {
        $user = User::where('code', 'MGARCIA')->first();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/reports/detail?fecha_desde=2026-01-01&fecha_hasta=2026-01-31');

        $response->assertStatus(200)
            ->assertJson(['error' => 0]);
        $data = $response->json('resultado.data');
        $this->assertIsArray($data);
        if (count($data) > 0) {
            $this->assertArrayHasKey('empleado', $data[0]);
        }
    }

    /** @test */
    public function detail_cliente_retorna_solo_su_cliente(): void
    {
        $user = User::where('code', 'CLI001')->first();
        Sanctum::actingAs($user);
        $cliente = Cliente::where('code', 'CLI001')->first();

        $response = $this->getJson('/api/v1/reports/detail?fecha_desde=2026-01-01&fecha_hasta=2026-01-31');

        $response->assertStatus(200)
            ->assertJson(['error' => 0]);
        $data = $response->json('resultado.data');
        foreach ($data as $row) {
            $this->assertEquals($cliente->id, $row['cliente']['id']);
        }
    }

    /** @test */
    public function detail_periodo_invalido_retorna_422_1305(): void
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/reports/detail?fecha_desde=2026-01-31&fecha_hasta=2026-01-01');

        $response->assertStatus(422)
            ->assertJson([
                'error' => 1305,
            ]);
    }

    /** @test */
    public function detail_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/reports/detail?fecha_desde=2026-01-01&fecha_hasta=2026-01-31');

        $response->assertStatus(401);
    }

    // ========================================
    // TR-046: GET /api/v1/reports/by-client
    // ========================================

    /** @test */
    public function by_client_empleado_retorna_grupos_sus_tareas(): void
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/reports/by-client?fecha_desde=2026-01-01&fecha_hasta=2026-01-31');

        $response->assertStatus(200)
            ->assertJson([
                'error' => 0,
                'respuesta' => 'Reporte por cliente obtenido correctamente',
            ])
            ->assertJsonStructure([
                'resultado' => [
                    'grupos' => [
                        '*' => [
                            'cliente_id',
                            'nombre',
                            'total_horas',
                            'cantidad_tareas',
                            'tareas',
                        ],
                    ],
                    'total_general_horas',
                    'total_general_tareas',
                ],
            ]);
    }

    /** @test */
    public function by_client_supervisor_retorna_todos_los_grupos(): void
    {
        $user = User::where('code', 'MGARCIA')->first();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/reports/by-client?fecha_desde=2026-01-01&fecha_hasta=2026-01-31');

        $response->assertStatus(200)->assertJson(['error' => 0]);
        $grupos = $response->json('resultado.grupos');
        $this->assertIsArray($grupos);
        if (count($grupos) > 0 && count($grupos[0]['tareas']) > 0) {
            $this->assertArrayHasKey('empleado', $grupos[0]['tareas'][0]);
        }
    }

    /** @test */
    public function by_client_periodo_invalido_retorna_422_1305(): void
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/reports/by-client?fecha_desde=2026-01-31&fecha_hasta=2026-01-01');

        $response->assertStatus(422)->assertJson(['error' => 1305]);
    }

    /** @test */
    public function by_client_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/reports/by-client?fecha_desde=2026-01-01&fecha_hasta=2026-01-31');

        $response->assertStatus(401);
    }

    // ========================================
    // TR-045: GET /api/v1/reports/by-employee
    // ========================================

    /** @test */
    public function by_employee_supervisor_retorna_200_con_grupos(): void
    {
        $user = User::where('code', 'MGARCIA')->first();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/reports/by-employee?fecha_desde=2026-01-01&fecha_hasta=2026-01-31');

        $response->assertStatus(200)
            ->assertJson([
                'error' => 0,
                'respuesta' => 'Reporte por empleado obtenido correctamente',
            ])
            ->assertJsonStructure([
                'resultado' => [
                    'grupos' => [
                        '*' => [
                            'usuario_id',
                            'nombre',
                            'code',
                            'total_horas',
                            'cantidad_tareas',
                            'tareas',
                        ],
                    ],
                    'total_general_horas',
                    'total_general_tareas',
                ],
            ]);
    }

    /** @test */
    public function by_employee_empleado_no_supervisor_retorna_403(): void
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/reports/by-employee?fecha_desde=2026-01-01&fecha_hasta=2026-01-31');

        $response->assertStatus(403);
    }

    /** @test */
    public function by_employee_periodo_invalido_retorna_422_1305(): void
    {
        $user = User::where('code', 'MGARCIA')->first();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/reports/by-employee?fecha_desde=2026-01-31&fecha_hasta=2026-01-01');

        $response->assertStatus(422)->assertJson(['error' => 1305]);
    }

    /** @test */
    public function by_employee_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/reports/by-employee?fecha_desde=2026-01-01&fecha_hasta=2026-01-31');

        $response->assertStatus(401);
    }

    // ============================================
    // TR-047: GET /api/v1/reports/by-task-type
    // ============================================

    /** @test */
    public function by_task_type_supervisor_retorna_200_con_grupos(): void
    {
        $user = User::where('code', 'MGARCIA')->first();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/reports/by-task-type?fecha_desde=2026-01-01&fecha_hasta=2026-01-31');

        $response->assertStatus(200)
            ->assertJson([
                'error' => 0,
                'respuesta' => 'Reporte por tipo de tarea obtenido correctamente',
            ])
            ->assertJsonStructure([
                'resultado' => [
                    'grupos' => [
                        '*' => [
                            'tipo_tarea_id',
                            'descripcion',
                            'total_horas',
                            'cantidad_tareas',
                            'tareas',
                        ],
                    ],
                    'total_general_horas',
                    'total_general_tareas',
                ],
            ]);
    }

    /** @test */
    public function by_task_type_empleado_no_supervisor_retorna_403(): void
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/reports/by-task-type?fecha_desde=2026-01-01&fecha_hasta=2026-01-31');

        $response->assertStatus(403);
    }

    /** @test */
    public function by_task_type_periodo_invalido_retorna_422_1305(): void
    {
        $user = User::where('code', 'MGARCIA')->first();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/reports/by-task-type?fecha_desde=2026-01-31&fecha_hasta=2026-01-01');

        $response->assertStatus(422)->assertJson(['error' => 1305]);
    }

    /** @test */
    public function by_task_type_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/reports/by-task-type?fecha_desde=2026-01-01&fecha_hasta=2026-01-31');

        $response->assertStatus(401);
    }

    // ========================================
    // TR-048: GET /api/v1/reports/by-date
    // ========================================

    /** @test */
    public function by_date_supervisor_retorna_200_con_grupos(): void
    {
        $user = User::where('code', 'MGARCIA')->first();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/reports/by-date?fecha_desde=2026-01-01&fecha_hasta=2026-01-31');

        $response->assertStatus(200)
            ->assertJson([
                'error' => 0,
                'respuesta' => 'Reporte por fecha obtenido correctamente',
            ])
            ->assertJsonStructure([
                'resultado' => [
                    'grupos' => [
                        '*' => [
                            'fecha',
                            'total_horas',
                            'cantidad_tareas',
                            'tareas',
                        ],
                    ],
                    'total_general_horas',
                    'total_general_tareas',
                ],
            ]);
    }

    /** @test */
    public function by_date_empleado_retorna_200(): void
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/reports/by-date?fecha_desde=2026-01-01&fecha_hasta=2026-01-31');

        $response->assertStatus(200)->assertJson(['error' => 0]);
    }

    /** @test */
    public function by_date_periodo_invalido_retorna_422_1305(): void
    {
        $user = User::where('code', 'MGARCIA')->first();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/reports/by-date?fecha_desde=2026-01-31&fecha_hasta=2026-01-01');

        $response->assertStatus(422)->assertJson(['error' => 1305]);
    }

    /** @test */
    public function by_date_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/reports/by-date?fecha_desde=2026-01-01&fecha_hasta=2026-01-31');

        $response->assertStatus(401);
    }
}
