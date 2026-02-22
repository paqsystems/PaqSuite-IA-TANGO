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
 * Tests de Integración: GET /api/v1/dashboard (TR-051 Dashboard principal)
 *
 * @see TR-051(MH)-dashboard-principal.md
 */
class DashboardControllerTest extends TestCase
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
                'observacion' => 'Tarea dashboard',
                'cerrado' => false,
            ]);
        }
    }

    /** @test */
    public function dashboard_empleado_retorna_kpis_y_top_clientes(): void
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/dashboard?fecha_desde=2026-01-01&fecha_hasta=2026-01-31');

        $response->assertStatus(200)
            ->assertJson([
                'error' => 0,
                'respuesta' => 'Dashboard obtenido correctamente',
            ])
            ->assertJsonStructure([
                'resultado' => [
                    'total_horas',
                    'cantidad_tareas',
                    'promedio_horas_por_dia',
                    'top_clientes' => [
                        '*' => ['cliente_id', 'nombre', 'total_horas', 'cantidad_tareas', 'porcentaje'],
                    ],
                    'top_empleados',
                    'distribucion_por_tipo',
                ],
            ]);
    }

    /** @test */
    public function dashboard_supervisor_retorna_top_empleados(): void
    {
        $user = User::where('code', 'MGARCIA')->first();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/dashboard?fecha_desde=2026-01-01&fecha_hasta=2026-01-31');

        $response->assertStatus(200)->assertJson(['error' => 0]);
        $this->assertArrayHasKey('top_empleados', $response->json('resultado'));
    }

    /** @test */
    public function dashboard_periodo_invalido_retorna_422_1305(): void
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/dashboard?fecha_desde=2026-01-31&fecha_hasta=2026-01-01');

        $response->assertStatus(422)->assertJson(['error' => 1305]);
    }

    /** @test */
    public function dashboard_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/dashboard?fecha_desde=2026-01-01&fecha_hasta=2026-01-31');

        $response->assertStatus(401);
    }
}
