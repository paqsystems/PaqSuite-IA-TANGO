<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Models\Usuario;
use App\Models\Cliente;
use App\Models\TipoTarea;
use App\Models\RegistroTarea;
use App\Models\ClienteTipoTarea;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Tests de Integración: Endpoints de Tareas
 * 
 * Tests de los endpoints de gestión de tareas con diferentes escenarios.
 * Usa DatabaseTransactions para mejor rendimiento con SQL Server remoto.
 * 
 * @see TR-028(MH)-carga-de-tarea-diaria.md
 */
class TaskControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedTestData();
    }

    /**
     * Crear datos de prueba para los tests
     */
    protected function seedTestData(): void
    {
        // Limpiar datos existentes
        $testCodes = ['JPEREZ', 'MGARCIA', 'CLI001', 'CLI002', 'CLIINACTIVO'];
        
        $clienteIds = DB::table('PQ_PARTES_CLIENTES')->whereIn('code', $testCodes)->pluck('id');
        $usuarioIds = DB::table('PQ_PARTES_USUARIOS')->whereIn('code', $testCodes)->pluck('id');
        
        if ($clienteIds->isNotEmpty()) {
            DB::table('PQ_PARTES_REGISTRO_TAREA')->whereIn('cliente_id', $clienteIds)->delete();
            DB::table('PQ_PARTES_CLIENTE_TIPO_TAREA')->whereIn('cliente_id', $clienteIds)->delete();
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

        // Obtener tipo de cliente
        $tipoClienteId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->where('code', 'CORP')->value('id');
        if (!$tipoClienteId) {
            $tipoClienteId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->first()?->id;
        }

        // Crear usuarios empleados
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
            'email' => 'juan.perez@ejemplo.com',
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
            'email' => 'maria.garcia@ejemplo.com',
            'supervisor' => true,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Crear clientes
        if ($tipoClienteId) {
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
                'email' => 'contacto@empresaabc.com',
                'tipo_cliente_id' => $tipoClienteId,
                'activo' => true,
                'inhabilitado' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('USERS')->insert([
                'code' => 'CLI002',
                'password_hash' => Hash::make('cliente002'),
                'activo' => true,
                'inhabilitado' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $cli002UserId = DB::table('USERS')->where('code', 'CLI002')->value('id');
            
            DB::table('PQ_PARTES_CLIENTES')->insert([
                'user_id' => $cli002UserId,
                'code' => 'CLI002',
                'nombre' => 'Corporación XYZ',
                'email' => 'contacto@corporacionxyz.com',
                'tipo_cliente_id' => $tipoClienteId,
                'activo' => true,
                'inhabilitado' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('USERS')->insert([
                'code' => 'CLIINACTIVO',
                'password_hash' => Hash::make('cliente456'),
                'activo' => true,
                'inhabilitado' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $cliInactivoUserId = DB::table('USERS')->where('code', 'CLIINACTIVO')->value('id');
            
            DB::table('PQ_PARTES_CLIENTES')->insert([
                'user_id' => $cliInactivoUserId,
                'code' => 'CLIINACTIVO',
                'nombre' => 'Cliente Inactivo S.R.L.',
                'email' => 'contacto@clienteinactivo.com',
                'tipo_cliente_id' => $tipoClienteId,
                'activo' => false,
                'inhabilitado' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Crear tipos de tarea
        $desarrolloId = DB::table('PQ_PARTES_TIPOS_TAREA')->where('code', 'DESARROLLO')->value('id');
        if (!$desarrolloId) {
            DB::table('PQ_PARTES_TIPOS_TAREA')->insert([
                'code' => 'DESARROLLO',
                'descripcion' => 'Desarrollo de Software',
                'is_generico' => true,
                'is_default' => false,
                'activo' => true,
                'inhabilitado' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('PQ_PARTES_TIPOS_TAREA')
                ->where('id', $desarrolloId)
                ->update(['is_generico' => true]);
        }

        $especialId = DB::table('PQ_PARTES_TIPOS_TAREA')->where('code', 'ESPECIAL')->value('id');
        if (!$especialId) {
            DB::table('PQ_PARTES_TIPOS_TAREA')->insert([
                'code' => 'ESPECIAL',
                'descripcion' => 'Tarea Especial para Cliente',
                'is_generico' => false,
                'is_default' => false,
                'activo' => true,
                'inhabilitado' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Crear asignación ClienteTipoTarea (CLI001 → ESPECIAL)
        $cli001Id = DB::table('PQ_PARTES_CLIENTES')->where('code', 'CLI001')->value('id');
        $especialId = DB::table('PQ_PARTES_TIPOS_TAREA')->where('code', 'ESPECIAL')->value('id');
        
        if ($cli001Id && $especialId) {
            $asignacionExists = DB::table('PQ_PARTES_CLIENTE_TIPO_TAREA')
                ->where('cliente_id', $cli001Id)
                ->where('tipo_tarea_id', $especialId)
                ->exists();
            
            if (!$asignacionExists) {
                DB::table('PQ_PARTES_CLIENTE_TIPO_TAREA')->insert([
                    'cliente_id' => $cli001Id,
                    'tipo_tarea_id' => $especialId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    // ========================================
    // Tests POST /api/v1/tasks
    // ========================================

    /** @test */
    public function store_creates_task_success()
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);
        
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        $response = $this->postJson('/api/v1/tasks', [
            'fecha' => '2026-01-28',
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'duracion_minutos' => 120,
            'sin_cargo' => false,
            'presencial' => true,
            'observacion' => 'Desarrollo de feature X',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'error' => 0,
                'respuesta' => 'Tarea registrada correctamente',
            ])
            ->assertJsonStructure([
                'error',
                'respuesta',
                'resultado' => [
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
                ],
            ]);

        $this->assertEquals('2026-01-28', $response->json('resultado.fecha'));
        $this->assertEquals(120, $response->json('resultado.duracion_minutos'));
        $this->assertEquals('Desarrollo de feature X', $response->json('resultado.observacion'));
    }

    /** @test */
    public function store_validates_fecha_formato_ymd()
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);
        
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        $response = $this->postJson('/api/v1/tasks', [
            'fecha' => '28/01/2026', // Formato DMY inválido
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'duracion_minutos' => 120,
            'observacion' => 'Test',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => 4220,
                'resultado' => [
                    'errors' => [
                        'fecha' => []
                    ]
                ]
            ]);
        
        // Verificar que el error de fecha está presente
        $this->assertArrayHasKey('fecha', $response->json('resultado.errors'));
    }

    /** @test */
    public function store_validates_required_fields()
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/tasks', []);

        $response->assertStatus(422)
            ->assertJson([
                'error' => 4220,
            ]);
        
        // Verificar que todos los campos requeridos tienen errores
        $errors = $response->json('resultado.errors');
        $this->assertArrayHasKey('fecha', $errors);
        $this->assertArrayHasKey('cliente_id', $errors);
        $this->assertArrayHasKey('tipo_tarea_id', $errors);
        $this->assertArrayHasKey('duracion_minutos', $errors);
        $this->assertArrayHasKey('observacion', $errors);
    }

    /** @test */
    public function store_validates_duracion_multiplo_15()
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);
        
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        $response = $this->postJson('/api/v1/tasks', [
            'fecha' => '2026-01-28',
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'duracion_minutos' => 25, // No es múltiplo de 15
            'observacion' => 'Test',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => 4220,
            ]);
        
        // Verificar que el error de duración está presente
        $this->assertArrayHasKey('duracion_minutos', $response->json('resultado.errors'));
    }

    /** @test */
    public function store_validates_tipo_tarea_asignado_cliente()
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);
        
        $cliente = Cliente::where('code', 'CLI002')->first(); // CLI002 no tiene ESPECIAL asignado
        $tipoEspecial = TipoTarea::where('code', 'ESPECIAL')->first();

        $response = $this->postJson('/api/v1/tasks', [
            'fecha' => '2026-01-28',
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoEspecial->id,
            'duracion_minutos' => 120,
            'observacion' => 'Test',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => 4220,
            ]);
        
        // Verificar que el error de tipo_tarea_id está presente
        $this->assertArrayHasKey('tipo_tarea_id', $response->json('resultado.errors'));
    }

    /** @test */
    public function store_supervisor_can_assign_to_other()
    {
        $supervisor = User::where('code', 'MGARCIA')->first();
        Sanctum::actingAs($supervisor);
        
        $empleado = Usuario::where('code', 'JPEREZ')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        $response = $this->postJson('/api/v1/tasks', [
            'fecha' => '2026-01-28',
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'duracion_minutos' => 120,
            'observacion' => 'Test',
            'usuario_id' => $empleado->id,
        ]);

        $response->assertStatus(201);
        $this->assertEquals($empleado->id, $response->json('resultado.usuario_id'));
    }

    /** @test */
    public function store_empleado_cannot_assign_to_other()
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);
        
        $otroEmpleado = Usuario::where('code', 'MGARCIA')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        $response = $this->postJson('/api/v1/tasks', [
            'fecha' => '2026-01-28',
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'duracion_minutos' => 120,
            'observacion' => 'Test',
            'usuario_id' => $otroEmpleado->id,
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'error' => 4003,
            ]);
    }

    /** @test */
    public function store_requires_authentication()
    {
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        $response = $this->postJson('/api/v1/tasks', [
            'fecha' => '2026-01-28',
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'duracion_minutos' => 120,
            'observacion' => 'Test',
        ]);

        $response->assertStatus(401);
    }

    // ========================================
    // Tests GET /api/v1/tasks (index - lista de tareas propias)
    // @see TR-033(MH)-visualización-de-lista-de-tareas-propias.md
    // ========================================

    /** @test */
    public function index_retorna_lista_paginada()
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);
        $empleado = Usuario::where('code', 'JPEREZ')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        RegistroTarea::create([
            'usuario_id' => $empleado->id,
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'fecha' => '2026-01-28',
            'duracion_minutos' => 120,
            'observacion' => 'Tarea de prueba',
            'cerrado' => false,
        ]);

        $response = $this->getJson('/api/v1/tasks');

        $response->assertStatus(200)
            ->assertJson([
                'error' => 0,
                'respuesta' => 'Tareas obtenidas correctamente',
            ])
            ->assertJsonStructure([
                'error',
                'respuesta',
                'resultado' => [
                    'data' => [
                        '*' => [
                            'id',
                            'fecha',
                            'cliente' => ['id', 'nombre'],
                            'tipo_tarea' => ['id', 'nombre'],
                            'duracion_minutos',
                            'duracion_horas',
                            'sin_cargo',
                            'presencial',
                            'observacion',
                            'cerrado',
                        ],
                    ],
                    'pagination' => ['current_page', 'per_page', 'total', 'last_page'],
                    'totales' => ['cantidad_tareas', 'total_horas'],
                ],
            ]);

        $this->assertGreaterThanOrEqual(1, count($response->json('resultado.data')));
    }

    /** @test */
    public function index_aplica_filtro_fecha()
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);
        $empleado = Usuario::where('code', 'JPEREZ')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        RegistroTarea::create([
            'usuario_id' => $empleado->id,
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'fecha' => '2026-01-15',
            'duracion_minutos' => 60,
            'observacion' => 'Enero',
            'cerrado' => false,
        ]);
        RegistroTarea::create([
            'usuario_id' => $empleado->id,
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'fecha' => '2026-02-01',
            'duracion_minutos' => 60,
            'observacion' => 'Febrero',
            'cerrado' => false,
        ]);

        $response = $this->getJson('/api/v1/tasks?fecha_desde=2026-01-01&fecha_hasta=2026-01-31');

        $response->assertStatus(200);
        $data = $response->json('resultado.data');
        $this->assertCount(1, $data);
        $this->assertEquals('2026-01-15', $data[0]['fecha']);
    }

    /** @test */
    public function index_requires_authentication()
    {
        $response = $this->getJson('/api/v1/tasks');

        $response->assertStatus(401);
    }

    /** @test @see TR-034(MH)-visualización-de-lista-de-todas-las-tareas-supervisor */
    public function indexAll_supervisor_retorna_todas_las_tareas()
    {
        $supervisor = User::where('code', 'MGARCIA')->first();
        Sanctum::actingAs($supervisor);
        $empleadoJ = Usuario::where('code', 'JPEREZ')->first();
        $empleadoM = Usuario::where('code', 'MGARCIA')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        RegistroTarea::create([
            'usuario_id' => $empleadoJ->id,
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'fecha' => '2026-01-28',
            'duracion_minutos' => 120,
            'observacion' => 'Tarea JPEREZ',
            'cerrado' => false,
        ]);
        RegistroTarea::create([
            'usuario_id' => $empleadoM->id,
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'fecha' => '2026-01-27',
            'duracion_minutos' => 60,
            'observacion' => 'Tarea MGARCIA',
            'cerrado' => false,
        ]);

        $response = $this->getJson('/api/v1/tasks/all');

        $response->assertStatus(200)
            ->assertJson([
                'error' => 0,
                'respuesta' => 'Tareas obtenidas correctamente',
            ])
            ->assertJsonStructure([
                'resultado' => [
                    'data' => [
                        '*' => [
                            'id',
                            'fecha',
                            'empleado' => ['id', 'code', 'nombre'],
                            'cliente' => ['id', 'nombre'],
                            'tipo_tarea' => ['id', 'nombre'],
                            'duracion_minutos',
                            'duracion_horas',
                            'observacion',
                            'cerrado',
                        ],
                    ],
                    'pagination' => ['current_page', 'per_page', 'total', 'last_page'],
                    'totales' => ['cantidad_tareas', 'total_horas'],
                ],
            ]);
        $this->assertGreaterThanOrEqual(2, count($response->json('resultado.data')));
    }

    /** @test @see TR-034: empleado no puede acceder a /tasks/all */
    public function indexAll_empleado_retorna_403()
    {
        $empleado = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($empleado);

        $response = $this->getJson('/api/v1/tasks/all');

        $response->assertStatus(403)
            ->assertJson([
                'error' => 4030,
                'respuesta' => 'Solo los supervisores pueden acceder a todas las tareas',
            ]);
        $this->assertEmpty($response->json('resultado'));
    }

    /** @test @see TR-040: validación rango fechas en GET /tasks/all */
    public function indexAll_fecha_desde_mayor_que_fecha_hasta_retorna_422_1305()
    {
        $supervisor = User::where('code', 'MGARCIA')->first();
        Sanctum::actingAs($supervisor);

        $response = $this->getJson('/api/v1/tasks/all?fecha_desde=2026-02-01&fecha_hasta=2026-01-15');

        $response->assertStatus(422)
            ->assertJson([
                'error' => 1305,
                'respuesta' => 'La fecha desde no puede ser posterior a fecha hasta',
            ]);
    }

    /** @test @see TR-042: proceso masivo cerrar/reabrir */
    public function bulk_toggle_close_supervisor_invierte_cerrado()
    {
        $supervisor = User::where('code', 'MGARCIA')->first();
        Sanctum::actingAs($supervisor);
        $empleado = Usuario::where('code', 'JPEREZ')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        $tarea = RegistroTarea::create([
            'usuario_id' => $empleado->id,
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'fecha' => '2026-01-28',
            'duracion_minutos' => 60,
            'observacion' => 'Tarea para bulk',
            'cerrado' => false,
        ]);

        $response = $this->postJson('/api/v1/tasks/bulk-toggle-close', [
            'task_ids' => [$tarea->id],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'error' => 0,
                'resultado' => ['processed' => 1, 'task_ids' => [$tarea->id]],
            ]);
        $tarea->refresh();
        $this->assertTrue($tarea->cerrado);
    }

    /** @test @see TR-043: validación task_ids no vacío */
    public function bulk_toggle_close_task_ids_vacio_retorna_422_1212()
    {
        $supervisor = User::where('code', 'MGARCIA')->first();
        Sanctum::actingAs($supervisor);

        $response = $this->postJson('/api/v1/tasks/bulk-toggle-close', [
            'task_ids' => [],
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => 1212,
                'respuesta' => 'Debe seleccionar al menos una tarea',
            ]);
    }

    /** @test @see TR-042: solo supervisores */
    public function bulk_toggle_close_empleado_retorna_403()
    {
        $empleado = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($empleado);

        $response = $this->postJson('/api/v1/tasks/bulk-toggle-close', [
            'task_ids' => [1],
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'error' => 4030,
            ]);
    }

    // ========================================
    // Tests GET /api/v1/tasks/clients
    // ========================================

    /** @test */
    public function get_clients_returns_active_only()
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/tasks/clients');

        $response->assertStatus(200)
            ->assertJson([
                'error' => 0,
                'respuesta' => 'Clientes obtenidos correctamente',
            ])
            ->assertJsonStructure([
                'error',
                'respuesta',
                'resultado' => [
                    '*' => ['id', 'code', 'nombre'],
                ],
            ]);

        $clientes = $response->json('resultado');
        $this->assertNotEmpty($clientes);
        
        // Verificar que todos los clientes están activos
        foreach ($clientes as $cliente) {
            $clienteModel = Cliente::find($cliente['id']);
            $this->assertTrue($clienteModel->activo);
            $this->assertFalse($clienteModel->inhabilitado);
        }
    }

    /** @test */
    public function get_clients_requires_authentication()
    {
        $response = $this->getJson('/api/v1/tasks/clients');

        $response->assertStatus(401);
    }

    // ========================================
    // Tests GET /api/v1/tasks/task-types
    // ========================================

    /** @test */
    public function get_task_types_returns_genericos()
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/tasks/task-types');

        $response->assertStatus(200)
            ->assertJson([
                'error' => 0,
                'respuesta' => 'Tipos de tarea obtenidos correctamente',
            ]);

        $tipos = $response->json('resultado');
        $this->assertNotEmpty($tipos);

        // Sin cliente_id (TR-033 UPDATE): retorna todos los tipos activos (genéricos y no genéricos)
        foreach ($tipos as $tipo) {
            $this->assertArrayHasKey('id', $tipo);
            $this->assertArrayHasKey('code', $tipo);
            $this->assertArrayHasKey('descripcion', $tipo);
            $this->assertArrayHasKey('is_generico', $tipo);
        }
    }

    /** @test */
    public function get_task_types_returns_genericos_y_asignados()
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);
        
        $cliente = Cliente::where('code', 'CLI001')->first();

        $response = $this->getJson('/api/v1/tasks/task-types?cliente_id=' . $cliente->id);

        $response->assertStatus(200);

        $tipos = $response->json('resultado');
        $this->assertNotEmpty($tipos);
        
        // Verificar que incluye genéricos y asignados
        $codigos = array_column($tipos, 'code');
        $this->assertContains('DESARROLLO', $codigos); // Genérico
        $this->assertContains('ESPECIAL', $codigos); // Asignado a CLI001
    }

    /** @test */
    public function get_task_types_requires_authentication()
    {
        $response = $this->getJson('/api/v1/tasks/task-types');

        $response->assertStatus(401);
    }

    // ========================================
    // Tests GET /api/v1/tasks/employees
    // ========================================

    /** @test */
    public function get_employees_requires_supervisor()
    {
        $user = User::where('code', 'JPEREZ')->first(); // No supervisor
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/tasks/employees');

        $response->assertStatus(403)
            ->assertJson([
                'error' => 4003,
                'respuesta' => 'Solo los supervisores pueden acceder a esta información',
            ]);
    }

    /** @test */
    public function get_employees_returns_active_only()
    {
        $supervisor = User::where('code', 'MGARCIA')->first();
        Sanctum::actingAs($supervisor);

        $response = $this->getJson('/api/v1/tasks/employees');

        $response->assertStatus(200)
            ->assertJson([
                'error' => 0,
                'respuesta' => 'Empleados obtenidos correctamente',
            ]);

        $empleados = $response->json('resultado');
        $this->assertNotEmpty($empleados);
        
        // Verificar que todos están activos
        foreach ($empleados as $empleado) {
            $empleadoModel = Usuario::find($empleado['id']);
            $this->assertTrue($empleadoModel->activo);
            $this->assertFalse($empleadoModel->inhabilitado);
        }
    }

    /** @test */
    public function get_employees_requires_authentication()
    {
        $response = $this->getJson('/api/v1/tasks/employees');

        $response->assertStatus(401);
    }

    // ========================================
    // TR-029: GET /api/v1/tasks/{id} y PUT /api/v1/tasks/{id}
    // ========================================

    /** @test */
    public function show_retorna_tarea_existente()
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);
        $empleado = Usuario::where('code', 'JPEREZ')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        $tarea = RegistroTarea::create([
            'usuario_id' => $empleado->id,
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'fecha' => '2026-01-28',
            'duracion_minutos' => 120,
            'observacion' => 'Tarea para editar',
            'cerrado' => false,
        ]);

        $response = $this->getJson("/api/v1/tasks/{$tarea->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => 0,
                'respuesta' => 'Tarea obtenida correctamente',
                'resultado' => [
                    'id' => $tarea->id,
                    'fecha' => '2026-01-28',
                    'duracion_minutos' => 120,
                    'observacion' => 'Tarea para editar',
                ],
            ]);
    }

    /** @test */
    public function show_falla_tarea_no_encontrada_retorna_404()
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/tasks/999999');

        $response->assertStatus(404)
            ->assertJson([
                'error' => 4040,
                'respuesta' => 'Tarea no encontrada',
            ]);
    }

    /** @test */
    public function show_falla_sin_permisos_retorna_403()
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);
        $otroEmpleado = Usuario::where('code', 'MGARCIA')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        $tarea = RegistroTarea::create([
            'usuario_id' => $otroEmpleado->id,
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'fecha' => '2026-01-28',
            'duracion_minutos' => 120,
            'observacion' => 'De MGARCIA',
            'cerrado' => false,
        ]);

        $response = $this->getJson("/api/v1/tasks/{$tarea->id}");

        $response->assertStatus(403)
            ->assertJson([
                'error' => 4030,
                'respuesta' => 'No tiene permisos para acceder a esta tarea',
            ]);
    }

    /** @test */
    public function show_falla_tarea_cerrada_retorna_400_2110()
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);
        $empleado = Usuario::where('code', 'JPEREZ')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        $tarea = RegistroTarea::create([
            'usuario_id' => $empleado->id,
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'fecha' => '2026-01-28',
            'duracion_minutos' => 120,
            'observacion' => 'Cerrada',
            'cerrado' => true,
        ]);

        $response = $this->getJson("/api/v1/tasks/{$tarea->id}");

        $response->assertStatus(400)
            ->assertJson([
                'error' => 2110,
                'respuesta' => 'No se puede modificar una tarea cerrada',
            ]);
    }

    /** @test */
    public function update_exitoso_actualiza_tarea()
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);
        $empleado = Usuario::where('code', 'JPEREZ')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        $tarea = RegistroTarea::create([
            'usuario_id' => $empleado->id,
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'fecha' => '2026-01-28',
            'duracion_minutos' => 120,
            'observacion' => 'Original',
            'cerrado' => false,
        ]);

        $response = $this->putJson("/api/v1/tasks/{$tarea->id}", [
            'fecha' => '2026-01-29',
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'duracion_minutos' => 180,
            'sin_cargo' => true,
            'presencial' => false,
            'observacion' => 'Actualizado',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'error' => 0,
                'respuesta' => 'Tarea actualizada correctamente',
                'resultado' => [
                    'id' => $tarea->id,
                    'fecha' => '2026-01-29',
                    'duracion_minutos' => 180,
                    'observacion' => 'Actualizado',
                    'sin_cargo' => true,
                    'presencial' => false,
                ],
            ]);
    }

    /** @test */
    public function update_falla_tarea_cerrada_retorna_2110()
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);
        $empleado = Usuario::where('code', 'JPEREZ')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        $tarea = RegistroTarea::create([
            'usuario_id' => $empleado->id,
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'fecha' => '2026-01-28',
            'duracion_minutos' => 120,
            'observacion' => 'Cerrada',
            'cerrado' => true,
        ]);

        $response = $this->putJson("/api/v1/tasks/{$tarea->id}", [
            'fecha' => '2026-01-29',
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'duracion_minutos' => 120,
            'observacion' => 'Intentar editar',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'error' => 2110,
                'respuesta' => 'No se puede modificar una tarea cerrada',
            ]);
    }

    /** @test */
    public function update_falla_sin_permisos_retorna_403()
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);
        $otroEmpleado = Usuario::where('code', 'MGARCIA')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        $tarea = RegistroTarea::create([
            'usuario_id' => $otroEmpleado->id,
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'fecha' => '2026-01-28',
            'duracion_minutos' => 120,
            'observacion' => 'De MGARCIA',
            'cerrado' => false,
        ]);

        $response = $this->putJson("/api/v1/tasks/{$tarea->id}", [
            'fecha' => '2026-01-29',
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'duracion_minutos' => 120,
            'observacion' => 'Intentar editar',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'error' => 4030,
                'respuesta' => 'No tiene permisos para editar esta tarea',
            ]);
    }

    /** @test */
    public function update_valida_campos_igual_que_creacion()
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);
        $empleado = Usuario::where('code', 'JPEREZ')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        $tarea = RegistroTarea::create([
            'usuario_id' => $empleado->id,
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'fecha' => '2026-01-28',
            'duracion_minutos' => 120,
            'observacion' => 'Original',
            'cerrado' => false,
        ]);

        $response = $this->putJson("/api/v1/tasks/{$tarea->id}", [
            'fecha' => '2026-01-29',
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'duracion_minutos' => 25, // No múltiplo de 15
            'observacion' => 'Actualizado',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => 4220,
                'respuesta' => 'Errores de validación',
            ]);
        $this->assertArrayHasKey('duracion_minutos', $response->json('resultado.errors'));
    }

    /** @test @see TR-031(MH)-edición-de-tarea-supervisor: supervisor puede cambiar propietario */
    public function update_supervisor_exitoso_con_cambio_propietario()
    {
        $user = User::where('code', 'MGARCIA')->first();
        Sanctum::actingAs($user);
        $empleadoOrigen = Usuario::where('code', 'JPEREZ')->first();
        $empleadoDestino = Usuario::where('code', 'MGARCIA')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        $tarea = RegistroTarea::create([
            'usuario_id' => $empleadoOrigen->id,
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'fecha' => '2026-01-28',
            'duracion_minutos' => 120,
            'observacion' => 'Tarea de JPEREZ',
            'cerrado' => false,
        ]);

        $response = $this->putJson("/api/v1/tasks/{$tarea->id}", [
            'fecha' => '2026-01-29',
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'duracion_minutos' => 180,
            'sin_cargo' => false,
            'presencial' => true,
            'observacion' => 'Reasignada a MGARCIA',
            'usuario_id' => $empleadoDestino->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'error' => 0,
                'respuesta' => 'Tarea actualizada correctamente',
                'resultado' => [
                    'id' => $tarea->id,
                    'usuario_id' => $empleadoDestino->id,
                    'fecha' => '2026-01-29',
                    'duracion_minutos' => 180,
                ],
            ]);
        $tarea->refresh();
        $this->assertEquals($empleadoDestino->id, $tarea->usuario_id);
    }

    /** @test @see TR-031: supervisor asigna empleado inactivo -> 422 */
    public function update_supervisor_falla_empleado_inactivo_retorna_422()
    {
        $user = User::where('code', 'MGARCIA')->first();
        Sanctum::actingAs($user);
        $empleadoOrigen = Usuario::where('code', 'JPEREZ')->first();
        $empleadoInactivo = Usuario::where('code', 'USUINACTIVO')->first();
        if (!$empleadoInactivo) {
            $this->markTestSkipped('USUINACTIVO no existe en PQ_PARTES_USUARIOS');
        }
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        $tarea = RegistroTarea::create([
            'usuario_id' => $empleadoOrigen->id,
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'fecha' => '2026-01-28',
            'duracion_minutos' => 120,
            'observacion' => 'Original',
            'cerrado' => false,
        ]);

        $response = $this->putJson("/api/v1/tasks/{$tarea->id}", [
            'fecha' => '2026-01-28',
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'duracion_minutos' => 120,
            'observacion' => 'Asignar a inactivo',
            'usuario_id' => $empleadoInactivo->id,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => 4220,
                'respuesta' => 'Errores de validación',
            ]);
        $this->assertArrayHasKey('usuario_id', $response->json('resultado.errors'));
    }

    /** @test @see TR-030(MH)-eliminación-de-tarea-propia.md */
    public function destroy_exitoso_elimina_tarea()
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);
        $empleado = Usuario::where('code', 'JPEREZ')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        $tarea = RegistroTarea::create([
            'usuario_id' => $empleado->id,
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'fecha' => '2026-01-28',
            'duracion_minutos' => 120,
            'observacion' => 'A eliminar',
            'cerrado' => false,
        ]);

        $id = $tarea->id;
        $response = $this->deleteJson("/api/v1/tasks/{$id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => 0,
                'respuesta' => 'Tarea eliminada correctamente',
                'resultado' => [],
            ]);
        $this->assertNull(RegistroTarea::find($id));
    }

    /** @test */
    public function destroy_falla_tarea_no_encontrada_retorna_404()
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);
        $response = $this->deleteJson('/api/v1/tasks/999999');
        $response->assertStatus(404)
            ->assertJson([
                'error' => 4040,
                'respuesta' => 'Tarea no encontrada',
            ]);
    }

    /** @test */
    public function destroy_falla_tarea_cerrada_retorna_2111()
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);
        $empleado = Usuario::where('code', 'JPEREZ')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        $tarea = RegistroTarea::create([
            'usuario_id' => $empleado->id,
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'fecha' => '2026-01-28',
            'duracion_minutos' => 120,
            'observacion' => 'Cerrada',
            'cerrado' => true,
        ]);

        $response = $this->deleteJson("/api/v1/tasks/{$tarea->id}");
        $response->assertStatus(400)
            ->assertJson([
                'error' => 2111,
                'respuesta' => 'No se puede eliminar una tarea cerrada',
            ]);
    }

    /** @test */
    public function destroy_falla_sin_permisos_retorna_403()
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);
        $otroEmpleado = Usuario::where('code', 'MGARCIA')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        $tarea = RegistroTarea::create([
            'usuario_id' => $otroEmpleado->id,
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'fecha' => '2026-01-28',
            'duracion_minutos' => 120,
            'observacion' => 'De MGARCIA',
            'cerrado' => false,
        ]);

        $response = $this->deleteJson("/api/v1/tasks/{$tarea->id}");
        $response->assertStatus(403)
            ->assertJson([
                'error' => 4030,
                'respuesta' => 'No tiene permisos para eliminar esta tarea',
            ]);
    }

    /** @test @see TR-032(MH)-eliminación-de-tarea-supervisor: supervisor puede eliminar cualquier tarea */
    public function destroy_supervisor_exitoso_elimina_cualquier_tarea()
    {
        $user = User::where('code', 'MGARCIA')->first();
        Sanctum::actingAs($user);
        $empleadoOrigen = Usuario::where('code', 'JPEREZ')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        $tarea = RegistroTarea::create([
            'usuario_id' => $empleadoOrigen->id,
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'fecha' => '2026-01-28',
            'duracion_minutos' => 120,
            'observacion' => 'Tarea de JPEREZ a eliminar por supervisor',
            'cerrado' => false,
        ]);

        $id = $tarea->id;
        $response = $this->deleteJson("/api/v1/tasks/{$id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => 0,
                'respuesta' => 'Tarea eliminada correctamente',
                'resultado' => [],
            ]);
        $this->assertNull(RegistroTarea::find($id));
    }
}
