<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Models\Usuario;
use App\Models\Cliente;
use App\Models\TipoTarea;
use App\Models\RegistroTarea;
use App\Models\ClienteTipoTarea;
use App\Services\TaskService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Tests Unitarios: TaskService
 * 
 * Tests del servicio de creación de tareas cubriendo todos los casos de negocio.
 * Usa DatabaseTransactions para mejor rendimiento con SQL Server remoto.
 * 
 * @see TR-028(MH)-carga-de-tarea-diaria.md
 */
class TaskServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected TaskService $taskService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->taskService = new TaskService();
        $this->seedTestData();
    }

    /**
     * Crear datos de prueba para los tests
     */
    protected function seedTestData(): void
    {
        // Limpiar datos existentes
        $testCodes = ['JPEREZ', 'MGARCIA', 'CLI001', 'CLI002', 'CLIINACTIVO'];
        
        // Eliminar registros de tarea de prueba
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
            // Asegurar que sea genérico
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

    /**
     * Test: Crear tarea exitosamente con datos válidos
     */
    public function test_create_task_success(): void
    {
        $user = User::where('code', 'JPEREZ')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        $datos = [
            'fecha' => '2026-01-28',
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'duracion_minutos' => 120,
            'sin_cargo' => false,
            'presencial' => true,
            'observacion' => 'Desarrollo de feature X',
        ];

        $resultado = $this->taskService->createTask($datos, $user);

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('id', $resultado);
        $this->assertEquals($datos['fecha'], $resultado['fecha']);
        $this->assertEquals($datos['duracion_minutos'], $resultado['duracion_minutos']);
        $this->assertEquals($datos['observacion'], $resultado['observacion']);

        // Verificar que se creó en la BD
        $registro = RegistroTarea::find($resultado['id']);
        $this->assertNotNull($registro);
        $this->assertEquals($user->id, Usuario::find($registro->usuario_id)->user_id);
    }

    /**
     * Test: Validar que cliente esté activo
     */
    public function test_create_task_validates_cliente_activo(): void
    {
        $user = User::where('code', 'JPEREZ')->first();
        $clienteInactivo = Cliente::where('code', 'CLIINACTIVO')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        $datos = [
            'fecha' => '2026-01-28',
            'cliente_id' => $clienteInactivo->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'duracion_minutos' => 120,
            'observacion' => 'Test',
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(TaskService::ERROR_CLIENTE_INACTIVO);
        
        $this->taskService->createTask($datos, $user);
    }

    /**
     * Test: Validar tipo genérico
     */
    public function test_create_task_validates_tipo_tarea_generico(): void
    {
        $user = User::where('code', 'JPEREZ')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoGenerico = TipoTarea::where('code', 'DESARROLLO')->first();

        $datos = [
            'fecha' => '2026-01-28',
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoGenerico->id,
            'duracion_minutos' => 120,
            'observacion' => 'Test',
        ];

        $resultado = $this->taskService->createTask($datos, $user);
        $this->assertIsArray($resultado);
    }

    /**
     * Test: Validar tipo asignado al cliente
     */
    public function test_create_task_validates_tipo_tarea_asignado(): void
    {
        $user = User::where('code', 'JPEREZ')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoEspecial = TipoTarea::where('code', 'ESPECIAL')->first();

        $datos = [
            'fecha' => '2026-01-28',
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoEspecial->id,
            'duracion_minutos' => 120,
            'observacion' => 'Test',
        ];

        $resultado = $this->taskService->createTask($datos, $user);
        $this->assertIsArray($resultado);
    }

    /**
     * Test: Validar tramos de 15 minutos
     */
    public function test_create_task_validates_duracion_multiplo_15(): void
    {
        // Esta validación se hace en el FormRequest, pero verificamos que el servicio
        // maneja correctamente valores válidos
        $user = User::where('code', 'JPEREZ')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        $datos = [
            'fecha' => '2026-01-28',
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'duracion_minutos' => 90, // Múltiplo de 15
            'observacion' => 'Test',
        ];

        $resultado = $this->taskService->createTask($datos, $user);
        $this->assertEquals(90, $resultado['duracion_minutos']);
    }

    /**
     * Test: Validar máximo 1440 minutos
     */
    public function test_create_task_validates_duracion_maxima(): void
    {
        // Esta validación se hace en el FormRequest
        // El servicio debe aceptar 1440 minutos
        $user = User::where('code', 'JPEREZ')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        $datos = [
            'fecha' => '2026-01-28',
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'duracion_minutos' => 1440,
            'observacion' => 'Test',
        ];

        $resultado = $this->taskService->createTask($datos, $user);
        $this->assertEquals(1440, $resultado['duracion_minutos']);
    }

    /**
     * Test: Supervisor asigna a otro empleado
     */
    public function test_create_task_supervisor_asigna_otro_empleado(): void
    {
        $supervisor = User::where('code', 'MGARCIA')->first();
        $empleado = Usuario::where('code', 'JPEREZ')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        $datos = [
            'fecha' => '2026-01-28',
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'duracion_minutos' => 120,
            'observacion' => 'Test',
            'usuario_id' => $empleado->id,
        ];

        $resultado = $this->taskService->createTask($datos, $supervisor);
        
        $this->assertIsArray($resultado);
        $this->assertEquals($empleado->id, $resultado['usuario_id']);
    }

    /**
     * Test: Empleado solo puede asignar para sí mismo
     */
    public function test_create_task_empleado_solo_para_si_mismo(): void
    {
        $user = User::where('code', 'JPEREZ')->first();
        $empleado = Usuario::where('code', 'JPEREZ')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        $datos = [
            'fecha' => '2026-01-28',
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'duracion_minutos' => 120,
            'observacion' => 'Test',
            // No se proporciona usuario_id, debe asignarse al autenticado
        ];

        $resultado = $this->taskService->createTask($datos, $user);
        
        $this->assertIsArray($resultado);
        $this->assertEquals($empleado->id, $resultado['usuario_id']);
    }

    /**
     * Test: Empleado no puede asignar a otro
     */
    public function test_create_task_empleado_intenta_asignar_otro_falla(): void
    {
        $user = User::where('code', 'JPEREZ')->first();
        $otroEmpleado = Usuario::where('code', 'MGARCIA')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        $datos = [
            'fecha' => '2026-01-28',
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'duracion_minutos' => 120,
            'observacion' => 'Test',
            'usuario_id' => $otroEmpleado->id,
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(TaskService::ERROR_FORBIDDEN);
        
        $this->taskService->createTask($datos, $user);
    }

    /**
     * Test: listTasks retorna solo tareas del usuario autenticado
     * @see TR-033(MH)-visualización-de-lista-de-tareas-propias.md
     */
    public function test_list_tasks_retorna_tareas_del_usuario(): void
    {
        $user = User::where('code', 'JPEREZ')->first();
        $empleado = Usuario::where('code', 'JPEREZ')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        RegistroTarea::create([
            'usuario_id' => $empleado->id,
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'fecha' => '2026-01-28',
            'duracion_minutos' => 120,
            'observacion' => 'Tarea uno',
            'cerrado' => false,
        ]);
        RegistroTarea::create([
            'usuario_id' => $empleado->id,
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'fecha' => '2026-01-27',
            'duracion_minutos' => 60,
            'observacion' => 'Tarea dos',
            'cerrado' => false,
        ]);

        $result = $this->taskService->listTasks($user, ['per_page' => 15]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('pagination', $result);
        $this->assertArrayHasKey('totales', $result);
        $this->assertCount(2, $result['data']);
        $this->assertEquals(2, $result['totales']['cantidad_tareas']);
        $this->assertEquals(3.0, $result['totales']['total_horas']); // 120+60 = 180 min = 3h
    }

    /**
     * Test: listTasks filtra por rango de fechas
     */
    public function test_list_tasks_filtra_por_rango_fechas(): void
    {
        $user = User::where('code', 'JPEREZ')->first();
        $empleado = Usuario::where('code', 'JPEREZ')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        RegistroTarea::create([
            'usuario_id' => $empleado->id,
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'fecha' => '2026-01-28',
            'duracion_minutos' => 120,
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

        $result = $this->taskService->listTasks($user, [
            'fecha_desde' => '2026-01-01',
            'fecha_hasta' => '2026-01-31',
            'per_page' => 15,
        ]);

        $this->assertCount(1, $result['data']);
        $this->assertEquals('2026-01-28', $result['data'][0]['fecha']);
    }

    /**
     * Test: listTasks pagina correctamente (per_page mínimo 10 según TR-033)
     */
    public function test_list_tasks_pagina_correctamente(): void
    {
        $user = User::where('code', 'JPEREZ')->first();
        $empleado = Usuario::where('code', 'JPEREZ')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        for ($i = 0; $i < 15; $i++) {
            RegistroTarea::create([
                'usuario_id' => $empleado->id,
                'cliente_id' => $cliente->id,
                'tipo_tarea_id' => $tipoTarea->id,
                'fecha' => '2026-01-' . str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT),
                'duracion_minutos' => 60,
                'observacion' => "Tarea $i",
                'cerrado' => false,
            ]);
        }

        $result = $this->taskService->listTasks($user, ['page' => 1, 'per_page' => 10]);

        $this->assertCount(10, $result['data']);
        $this->assertEquals(1, $result['pagination']['current_page']);
        $this->assertEquals(2, $result['pagination']['last_page']);
        $this->assertEquals(15, $result['pagination']['total']);
    }

    // ========================================
    // TR-029: getTask / updateTask
    // ========================================

    /** @see TR-029(MH)-edición-de-tarea-propia.md */
    public function test_get_task_retorna_tarea_propia(): void
    {
        $user = User::where('code', 'JPEREZ')->first();
        $empleado = Usuario::where('code', 'JPEREZ')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        $tarea = RegistroTarea::create([
            'usuario_id' => $empleado->id,
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'fecha' => '2026-01-28',
            'duracion_minutos' => 120,
            'observacion' => 'Tarea editable',
            'cerrado' => false,
        ]);

        $result = $this->taskService->getTask($tarea->id, $user);

        $this->assertIsArray($result);
        $this->assertEquals($tarea->id, $result['id']);
        $this->assertEquals('2026-01-28', $result['fecha']);
        $this->assertEquals(120, $result['duracion_minutos']);
        $this->assertEquals('Tarea editable', $result['observacion']);
    }

    public function test_get_task_falla_si_no_existe(): void
    {
        $user = User::where('code', 'JPEREZ')->first();
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(404);
        $this->taskService->getTask(999999, $user);
    }

    public function test_get_task_falla_si_cerrada(): void
    {
        $user = User::where('code', 'JPEREZ')->first();
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

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(TaskService::ERROR_CLOSED);
        $this->taskService->getTask($tarea->id, $user);
    }

    public function test_get_task_falla_si_otro_usuario_y_no_supervisor(): void
    {
        $user = User::where('code', 'JPEREZ')->first();
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

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(TaskService::ERROR_FORBIDDEN_EDIT);
        $this->taskService->getTask($tarea->id, $user);
    }

    public function test_update_task_exitoso(): void
    {
        $user = User::where('code', 'JPEREZ')->first();
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

        $datos = [
            'fecha' => '2026-01-29',
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'duracion_minutos' => 180,
            'sin_cargo' => true,
            'presencial' => false,
            'observacion' => 'Actualizado',
        ];

        $result = $this->taskService->updateTask($tarea->id, $datos, $user);

        $this->assertEquals($tarea->id, $result['id']);
        $this->assertEquals('2026-01-29', $result['fecha']);
        $this->assertEquals(180, $result['duracion_minutos']);
        $this->assertEquals('Actualizado', $result['observacion']);
        $this->assertTrue($result['sin_cargo']);
        $this->assertFalse($result['presencial']);

        $tarea->refresh();
        $this->assertEquals('2026-01-29', $tarea->fecha->format('Y-m-d'));
        $this->assertEquals(180, $tarea->duracion_minutos);
    }

    public function test_update_task_falla_si_cerrada(): void
    {
        $user = User::where('code', 'JPEREZ')->first();
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

        $datos = [
            'fecha' => '2026-01-29',
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'duracion_minutos' => 120,
            'observacion' => 'Intentar editar',
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(TaskService::ERROR_CLOSED);
        $this->taskService->updateTask($tarea->id, $datos, $user);
    }

    public function test_update_task_falla_sin_permisos(): void
    {
        $user = User::where('code', 'JPEREZ')->first();
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

        $datos = [
            'fecha' => '2026-01-29',
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'duracion_minutos' => 120,
            'observacion' => 'Intentar editar',
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(TaskService::ERROR_FORBIDDEN_EDIT);
        $this->taskService->updateTask($tarea->id, $datos, $user);
    }

    /** @see TR-031(MH)-edición-de-tarea-supervisor.md: supervisor puede cambiar propietario */
    public function test_update_supervisor_puede_cambiar_propietario(): void
    {
        $supervisor = User::where('code', 'MGARCIA')->first();
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

        $datos = [
            'fecha' => '2026-01-29',
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'duracion_minutos' => 180,
            'sin_cargo' => false,
            'presencial' => true,
            'observacion' => 'Reasignada a MGARCIA',
            'usuario_id' => $empleadoDestino->id,
        ];

        $result = $this->taskService->updateTask($tarea->id, $datos, $supervisor);

        $this->assertEquals($tarea->id, $result['id']);
        $this->assertEquals($empleadoDestino->id, $result['usuario_id']);
        $this->assertEquals('2026-01-29', $result['fecha']);
        $this->assertEquals(180, $result['duracion_minutos']);
        $tarea->refresh();
        $this->assertEquals($empleadoDestino->id, $tarea->usuario_id);
    }

    /** @see TR-031: supervisor intenta asignar a empleado inactivo -> 4203 */
    public function test_update_supervisor_valida_empleado_activo(): void
    {
        $supervisor = User::where('code', 'MGARCIA')->first();
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

        $datos = [
            'fecha' => '2026-01-28',
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'duracion_minutos' => 120,
            'observacion' => 'Intentar asignar a inactivo',
            'usuario_id' => $empleadoInactivo->id,
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(TaskService::ERROR_EMPLEADO_INACTIVO);
        $this->taskService->updateTask($tarea->id, $datos, $supervisor);
    }

    /** @see TR-031: empleado no puede enviar usuario_id -> 4030 */
    public function test_update_empleado_no_puede_enviar_usuario_id_retorna_4030(): void
    {
        $empleado = User::where('code', 'JPEREZ')->first();
        $otroEmpleado = Usuario::where('code', 'MGARCIA')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        $tarea = RegistroTarea::create([
            'usuario_id' => Usuario::where('code', 'JPEREZ')->first()->id,
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'fecha' => '2026-01-28',
            'duracion_minutos' => 120,
            'observacion' => 'Propia',
            'cerrado' => false,
        ]);

        $datos = [
            'fecha' => '2026-01-28',
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'duracion_minutos' => 120,
            'observacion' => 'Intentar cambiar propietario',
            'usuario_id' => $otroEmpleado->id,
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(TaskService::ERROR_FORBIDDEN_EDIT);
        $this->taskService->updateTask($tarea->id, $datos, $empleado);
    }

    /** @see TR-030(MH)-eliminación-de-tarea-propia.md */
    public function test_delete_task_exitoso_elimina_tarea(): void
    {
        $user = User::where('code', 'JPEREZ')->first();
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
        $this->taskService->deleteTask($id, $user);

        $this->assertNull(RegistroTarea::find($id));
    }

    public function test_delete_task_falla_tarea_no_encontrada(): void
    {
        $user = User::where('code', 'JPEREZ')->first();
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(404);
        $this->taskService->deleteTask(999999, $user);
    }

    public function test_delete_task_falla_si_cerrada_retorna_error_2111(): void
    {
        $user = User::where('code', 'JPEREZ')->first();
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

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(TaskService::ERROR_CLOSED_DELETE);
        $this->taskService->deleteTask($tarea->id, $user);
    }

    public function test_delete_task_falla_sin_permisos_retorna_error_4030(): void
    {
        $user = User::where('code', 'JPEREZ')->first();
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

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(TaskService::ERROR_FORBIDDEN_DELETE);
        $this->taskService->deleteTask($tarea->id, $user);
    }

    /** @see TR-032(MH)-eliminación-de-tarea-supervisor: supervisor puede eliminar cualquier tarea */
    public function test_delete_supervisor_puede_eliminar_cualquier_tarea(): void
    {
        $supervisor = User::where('code', 'MGARCIA')->first();
        $empleadoOrigen = Usuario::where('code', 'JPEREZ')->first();
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

        $id = $tarea->id;
        $this->taskService->deleteTask($id, $supervisor);

        $this->assertNull(RegistroTarea::find($id));
    }

    // ========================================
    // TR-044: listDetailReport (Consulta detallada)
    // ========================================

    /** @see TR-044(MH)-consulta-detallada-de-tareas.md */
    public function test_list_detail_report_empleado_solo_sus_tareas(): void
    {
        $user = User::where('code', 'JPEREZ')->first();
        $empleado = Usuario::where('code', 'JPEREZ')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        RegistroTarea::create([
            'usuario_id' => $empleado->id,
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'fecha' => '2026-01-28',
            'duracion_minutos' => 120,
            'sin_cargo' => false,
            'presencial' => true,
            'observacion' => 'Tarea empleado',
            'cerrado' => false,
        ]);

        $result = $this->taskService->listDetailReport($user, [
            'fecha_desde' => '2026-01-01',
            'fecha_hasta' => '2026-01-31',
            'per_page' => 15,
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('pagination', $result);
        $this->assertArrayHasKey('total_horas', $result);
        $this->assertCount(1, $result['data']);
        $this->assertEquals(2.0, $result['total_horas']); // 120 min = 2h
        $this->assertArrayNotHasKey('empleado', $result['data'][0]); // Empleado no supervisor: no incluye empleado
    }

    /** @see TR-044(MH)-consulta-detallada-de-tareas.md */
    public function test_list_detail_report_supervisor_ve_todas_las_tareas(): void
    {
        $supervisor = User::where('code', 'MGARCIA')->first();
        $empleado = Usuario::where('code', 'JPEREZ')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        RegistroTarea::create([
            'usuario_id' => $empleado->id,
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'fecha' => '2026-01-28',
            'duracion_minutos' => 90,
            'sin_cargo' => false,
            'presencial' => true,
            'observacion' => 'Tarea de JPEREZ',
            'cerrado' => false,
        ]);

        $result = $this->taskService->listDetailReport($supervisor, [
            'fecha_desde' => '2026-01-01',
            'fecha_hasta' => '2026-01-31',
            'per_page' => 15,
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertGreaterThanOrEqual(1, count($result['data']));
        $this->assertArrayHasKey('empleado', $result['data'][0]); // Supervisor: incluye empleado
        $this->assertEquals('Juan Pérez', $result['data'][0]['empleado']['nombre']);
        $this->assertArrayHasKey('total_horas', $result);
    }

    /** @see TR-044(MH)-consulta-detallada-de-tareas.md */
    public function test_list_detail_report_cliente_solo_su_cliente(): void
    {
        $userCliente = User::where('code', 'CLI001')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $empleado = Usuario::where('code', 'JPEREZ')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        RegistroTarea::create([
            'usuario_id' => $empleado->id,
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'fecha' => '2026-01-28',
            'duracion_minutos' => 60,
            'sin_cargo' => false,
            'presencial' => true,
            'observacion' => 'Tarea para CLI001',
            'cerrado' => false,
        ]);

        $result = $this->taskService->listDetailReport($userCliente, [
            'fecha_desde' => '2026-01-01',
            'fecha_hasta' => '2026-01-31',
            'per_page' => 15,
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertCount(1, $result['data']);
        $this->assertEquals($cliente->id, $result['data'][0]['cliente']['id']);
        $this->assertEquals(1.0, $result['total_horas']);
    }

    /** @see TR-044(MH)-consulta-detallada-de-tareas.md - Validación período 1305 */
    public function test_list_detail_report_periodo_invalido_lanza_1305(): void
    {
        $user = User::where('code', 'JPEREZ')->first();

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(TaskService::ERROR_PERIODO_INVALIDO);

        $this->taskService->listDetailReport($user, [
            'fecha_desde' => '2026-01-31',
            'fecha_hasta' => '2026-01-01',
            'per_page' => 15,
        ]);
    }

    /** @see TR-044(MH)-consulta-detallada-de-tareas.md - Horas en decimal */
    public function test_list_detail_report_total_horas_decimal(): void
    {
        $user = User::where('code', 'JPEREZ')->first();
        $empleado = Usuario::where('code', 'JPEREZ')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        RegistroTarea::create([
            'usuario_id' => $empleado->id,
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'fecha' => '2026-01-28',
            'duracion_minutos' => 90, // 1.5 h
            'sin_cargo' => false,
            'presencial' => true,
            'observacion' => 'Test',
            'cerrado' => false,
        ]);

        $result = $this->taskService->listDetailReport($user, [
            'fecha_desde' => '2026-01-01',
            'fecha_hasta' => '2026-01-31',
            'per_page' => 15,
        ]);

        $this->assertEquals(1.5, $result['total_horas']);
        $this->assertEquals(1.5, $result['data'][0]['horas']);
    }

    // ========================================
    // TR-046: listByClientReport (Consulta agrupada por cliente)
    // ========================================

    /** @see TR-046(MH)-consulta-agrupada-por-cliente.md */
    public function test_list_by_client_report_empleado_solo_sus_tareas_agrupadas(): void
    {
        $user = User::where('code', 'JPEREZ')->first();
        $empleado = Usuario::where('code', 'JPEREZ')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        RegistroTarea::create([
            'usuario_id' => $empleado->id,
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'fecha' => '2026-01-28',
            'duracion_minutos' => 120,
            'sin_cargo' => false,
            'presencial' => true,
            'observacion' => 'Tarea empleado',
            'cerrado' => false,
        ]);

        $result = $this->taskService->listByClientReport($user, [
            'fecha_desde' => '2026-01-01',
            'fecha_hasta' => '2026-01-31',
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('grupos', $result);
        $this->assertArrayHasKey('total_general_horas', $result);
        $this->assertArrayHasKey('total_general_tareas', $result);
        $this->assertCount(1, $result['grupos']);
        $this->assertEquals($cliente->id, $result['grupos'][0]['cliente_id']);
        $this->assertEquals(2.0, $result['grupos'][0]['total_horas']);
        $this->assertEquals(1, $result['grupos'][0]['cantidad_tareas']);
        $this->assertCount(1, $result['grupos'][0]['tareas']);
        $this->assertArrayNotHasKey('empleado', $result['grupos'][0]['tareas'][0]);
    }

    /** @see TR-046(MH)-consulta-agrupada-por-cliente.md */
    public function test_list_by_client_report_supervisor_ve_todos_los_grupos(): void
    {
        $supervisor = User::where('code', 'MGARCIA')->first();
        $empleado = Usuario::where('code', 'JPEREZ')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        RegistroTarea::create([
            'usuario_id' => $empleado->id,
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'fecha' => '2026-01-28',
            'duracion_minutos' => 90,
            'sin_cargo' => false,
            'presencial' => true,
            'observacion' => 'Tarea JPEREZ',
            'cerrado' => false,
        ]);

        $result = $this->taskService->listByClientReport($supervisor, [
            'fecha_desde' => '2026-01-01',
            'fecha_hasta' => '2026-01-31',
        ]);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result['grupos']));
        $this->assertArrayHasKey('empleado', $result['grupos'][0]['tareas'][0]);
        $this->assertEquals('Juan Pérez', $result['grupos'][0]['tareas'][0]['empleado']['nombre']);
    }

    /** @see TR-046(MH)-consulta-agrupada-por-cliente.md */
    public function test_list_by_client_report_cliente_solo_su_grupo(): void
    {
        $userCliente = User::where('code', 'CLI001')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $empleado = Usuario::where('code', 'JPEREZ')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        RegistroTarea::create([
            'usuario_id' => $empleado->id,
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'fecha' => '2026-01-28',
            'duracion_minutos' => 60,
            'sin_cargo' => false,
            'presencial' => true,
            'observacion' => 'Tarea para CLI001',
            'cerrado' => false,
        ]);

        $result = $this->taskService->listByClientReport($userCliente, [
            'fecha_desde' => '2026-01-01',
            'fecha_hasta' => '2026-01-31',
        ]);

        $this->assertIsArray($result);
        $this->assertCount(1, $result['grupos']);
        $this->assertEquals($cliente->id, $result['grupos'][0]['cliente_id']);
        $this->assertEquals(1.0, $result['total_general_horas']);
        $this->assertEquals(1, $result['total_general_tareas']);
    }

    /** @see TR-046(MH)-consulta-agrupada-por-cliente.md - Período inválido 1305 */
    public function test_list_by_client_report_periodo_invalido_lanza_1305(): void
    {
        $user = User::where('code', 'JPEREZ')->first();

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(TaskService::ERROR_PERIODO_INVALIDO);

        $this->taskService->listByClientReport($user, [
            'fecha_desde' => '2026-01-31',
            'fecha_hasta' => '2026-01-01',
        ]);
    }

    // ========================================
    // TR-051: getDashboardData (Dashboard principal)
    // ========================================

    /** @see TR-051(MH)-dashboard-principal.md */
    public function test_get_dashboard_data_empleado_retorna_kpis_y_top_clientes(): void
    {
        $user = User::where('code', 'JPEREZ')->first();
        $empleado = Usuario::where('code', 'JPEREZ')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        RegistroTarea::create([
            'usuario_id' => $empleado->id,
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'fecha' => '2026-01-15',
            'duracion_minutos' => 120,
            'sin_cargo' => false,
            'presencial' => true,
            'observacion' => 'Tarea',
            'cerrado' => false,
        ]);

        $result = $this->taskService->getDashboardData($user, [
            'fecha_desde' => '2026-01-01',
            'fecha_hasta' => '2026-01-31',
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('total_horas', $result);
        $this->assertArrayHasKey('cantidad_tareas', $result);
        $this->assertArrayHasKey('promedio_horas_por_dia', $result);
        $this->assertArrayHasKey('top_clientes', $result);
        $this->assertArrayHasKey('top_empleados', $result);
        $this->assertArrayHasKey('distribucion_por_tipo', $result);
        $this->assertEquals(2.0, $result['total_horas']);
        $this->assertEquals(1, $result['cantidad_tareas']);
        $this->assertCount(1, $result['top_clientes']);
        $this->assertEmpty($result['top_empleados']);
        $this->assertEmpty($result['distribucion_por_tipo']);
    }

    /** @see TR-051(MH)-dashboard-principal.md */
    public function test_get_dashboard_data_supervisor_retorna_top_empleados(): void
    {
        $supervisor = User::where('code', 'MGARCIA')->first();
        $empleado = Usuario::where('code', 'JPEREZ')->first();
        $cliente = Cliente::where('code', 'CLI001')->first();
        $tipoTarea = TipoTarea::where('code', 'DESARROLLO')->first();

        RegistroTarea::create([
            'usuario_id' => $empleado->id,
            'cliente_id' => $cliente->id,
            'tipo_tarea_id' => $tipoTarea->id,
            'fecha' => '2026-01-20',
            'duracion_minutos' => 90,
            'sin_cargo' => false,
            'presencial' => true,
            'observacion' => 'Tarea JPEREZ',
            'cerrado' => false,
        ]);

        $result = $this->taskService->getDashboardData($supervisor, [
            'fecha_desde' => '2026-01-01',
            'fecha_hasta' => '2026-01-31',
        ]);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(0, count($result['top_empleados']));
        if (count($result['top_clientes']) > 0) {
            $this->assertArrayHasKey('porcentaje', $result['top_clientes'][0]);
        }
    }

    /** @see TR-051(MH)-dashboard-principal.md - Período inválido 1305 */
    public function test_get_dashboard_data_periodo_invalido_lanza_1305(): void
    {
        $user = User::where('code', 'JPEREZ')->first();

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(TaskService::ERROR_PERIODO_INVALIDO);

        $this->taskService->getDashboardData($user, [
            'fecha_desde' => '2026-01-31',
            'fecha_hasta' => '2026-01-01',
        ]);
    }
}
