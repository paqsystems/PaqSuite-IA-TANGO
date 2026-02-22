<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Models\Usuario;
use App\Services\AuthService;
use App\Services\AuthException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Tests Unitarios: AuthService
 * 
 * Tests del servicio de autenticación cubriendo todos los casos de uso.
 * Usa DatabaseTransactions para mejor rendimiento con SQL Server remoto.
 * 
 * @see TR-001(MH)-login-de-empleado.md
 * @see TR-002(SH)-login-de-cliente.md
 * @see TR-003(MH)-logout.md
 * @see TR-005(SH)-cambio-de-contraseña-usuario-autenticado.md
 */
class AuthServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();
        $this->seedTestUsers();
    }

    /**
     * Crear usuarios de prueba para los tests
     * Usa DB::table con now() para compatibilidad con MySQL y SQL Server
     * Primero elimina usuarios existentes para evitar conflictos de clave única
     */
    protected function seedTestUsers(): void
    {
        // Limpiar usuarios existentes que podrían causar conflictos
        $testCodes = ['JPEREZ', 'MGARCIA', 'INACTIVO', 'INHABILITADO', 'USUINACTIVO', 'CLI001', 'CLIINACTIVO', 'SINPERFIL'];
        
        // Eliminar registros de tarea que referencian a estos usuarios (FK fk_registro_usuario)
        $usuarioIds = DB::table('PQ_PARTES_USUARIOS')->whereIn('code', $testCodes)->pluck('id');
        if ($usuarioIds->isNotEmpty()) {
            DB::table('PQ_PARTES_REGISTRO_TAREA')->whereIn('usuario_id', $usuarioIds)->delete();
        }
        // Eliminar de PQ_PARTES_USUARIOS y PQ_PARTES_CLIENTES
        DB::table('PQ_PARTES_USUARIOS')->whereIn('code', $testCodes)->delete();
        DB::table('PQ_PARTES_CLIENTES')->whereIn('code', $testCodes)->delete();
        
        // Eliminar tokens asociados a usuarios de prueba
        $userIds = DB::table('USERS')->whereIn('code', $testCodes)->pluck('id');
        if ($userIds->isNotEmpty()) {
            DB::table('personal_access_tokens')
                ->where('tokenable_type', 'App\\Models\\User')
                ->whereIn('tokenable_id', $userIds)
                ->delete();
        }
        
        // Eliminar de USERS
        DB::table('USERS')->whereIn('code', $testCodes)->delete();

        // Usuario activo normal
        DB::table('USERS')->insert([
            'code' => 'JPEREZ',
            'password_hash' => Hash::make('password123'),
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $jperezId = DB::table('USERS')->where('code', 'JPEREZ')->value('id');

        DB::table('PQ_PARTES_USUARIOS')->insert([
            'user_id' => $jperezId,
            'code' => 'JPEREZ',
            'nombre' => 'Juan Pérez',
            'email' => 'juan.perez@ejemplo.com',
            'supervisor' => false,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Usuario activo supervisor
        DB::table('USERS')->insert([
            'code' => 'MGARCIA',
            'password_hash' => Hash::make('password456'),
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $mgarciaId = DB::table('USERS')->where('code', 'MGARCIA')->value('id');

        DB::table('PQ_PARTES_USUARIOS')->insert([
            'user_id' => $mgarciaId,
            'code' => 'MGARCIA',
            'nombre' => 'María García',
            'email' => 'maria.garcia@ejemplo.com',
            'supervisor' => true,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Usuario inactivo en USERS
        DB::table('USERS')->insert([
            'code' => 'INACTIVO',
            'password_hash' => Hash::make('password789'),
            'activo' => false,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $inactivoId = DB::table('USERS')->where('code', 'INACTIVO')->value('id');

        DB::table('PQ_PARTES_USUARIOS')->insert([
            'user_id' => $inactivoId,
            'code' => 'INACTIVO',
            'nombre' => 'Usuario Inactivo',
            'email' => 'inactivo@ejemplo.com',
            'supervisor' => false,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Usuario inhabilitado en USERS
        DB::table('USERS')->insert([
            'code' => 'INHABILITADO',
            'password_hash' => Hash::make('password000'),
            'activo' => true,
            'inhabilitado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $inhabilitadoId = DB::table('USERS')->where('code', 'INHABILITADO')->value('id');

        DB::table('PQ_PARTES_USUARIOS')->insert([
            'user_id' => $inhabilitadoId,
            'code' => 'INHABILITADO',
            'nombre' => 'Usuario Inhabilitado',
            'email' => 'inhabilitado@ejemplo.com',
            'supervisor' => false,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Usuario con empleado inactivo en PQ_PARTES_USUARIOS
        DB::table('USERS')->insert([
            'code' => 'USUINACTIVO',
            'password_hash' => Hash::make('password111'),
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $usuInactivoId = DB::table('USERS')->where('code', 'USUINACTIVO')->value('id');

        DB::table('PQ_PARTES_USUARIOS')->insert([
            'user_id' => $usuInactivoId,
            'code' => 'USUINACTIVO',
            'nombre' => 'Usuario Inactivo en Empleados',
            'email' => 'usuinactivo@ejemplo.com',
            'supervisor' => false,
            'activo' => false,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ========================================
        // Clientes de prueba (TR-002)
        // ========================================

        // Cliente activo
        DB::table('USERS')->insert([
            'code' => 'CLI001',
            'password_hash' => Hash::make('cliente123'),
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cli001Id = DB::table('USERS')->where('code', 'CLI001')->value('id');

        // Cliente inactivo en PQ_PARTES_CLIENTES
        DB::table('USERS')->insert([
            'code' => 'CLIINACTIVO',
            'password_hash' => Hash::make('cliente456'),
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cliInactivoId = DB::table('USERS')->where('code', 'CLIINACTIVO')->value('id');

        // Usuario sin perfil en ninguna tabla
        DB::table('USERS')->insert([
            'code' => 'SINPERFIL',
            'password_hash' => Hash::make('sinperfil123'),
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Obtener tipo de cliente
        $tipoClienteId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->where('code', 'CORP')->value('id');
        if (!$tipoClienteId) {
            $tipoClienteId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->first()?->id;
        }

        // Insertar clientes en PQ_PARTES_CLIENTES
        if ($tipoClienteId && $cli001Id) {
            DB::table('PQ_PARTES_CLIENTES')->insert([
                'user_id' => $cli001Id,
                'code' => 'CLI001',
                'nombre' => 'Empresa ABC S.A.',
                'email' => 'contacto@empresaabc.com',
                'tipo_cliente_id' => $tipoClienteId,
                'activo' => true,
                'inhabilitado' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($tipoClienteId && $cliInactivoId) {
            DB::table('PQ_PARTES_CLIENTES')->insert([
                'user_id' => $cliInactivoId,
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
    }

    /** @test */
    public function login_exitoso_con_empleado_normal()
    {
        $result = $this->authService->login('JPEREZ', 'password123');

        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('user_data', $result);
        $this->assertEquals('JPEREZ', $result['user_data']['user_code']);
        $this->assertEquals('usuario', $result['user_data']['tipo_usuario']);
        $this->assertFalse($result['user_data']['es_supervisor']);
        $this->assertEquals('Juan Pérez', $result['user_data']['nombre']);
        $this->assertNull($result['user_data']['cliente_id']);
    }

    /** @test */
    public function login_exitoso_con_empleado_supervisor()
    {
        $result = $this->authService->login('MGARCIA', 'password456');

        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('user_data', $result);
        $this->assertEquals('MGARCIA', $result['user_data']['user_code']);
        $this->assertEquals('usuario', $result['user_data']['tipo_usuario']);
        $this->assertTrue($result['user_data']['es_supervisor']);
        $this->assertEquals('María García', $result['user_data']['nombre']);
    }

    /** @test */
    public function login_fallido_usuario_no_encontrado()
    {
        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Credenciales inválidas');

        try {
            $this->authService->login('NOEXISTE', 'cualquierpass');
        } catch (AuthException $e) {
            $this->assertEquals(AuthService::ERROR_INVALID_CREDENTIALS, $e->getErrorCode());
            throw $e;
        }
    }

    /** @test */
    public function login_fallido_contrasena_incorrecta()
    {
        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Credenciales inválidas');

        try {
            $this->authService->login('JPEREZ', 'contraseñaincorrecta');
        } catch (AuthException $e) {
            $this->assertEquals(AuthService::ERROR_INVALID_CREDENTIALS, $e->getErrorCode());
            throw $e;
        }
    }

    /** @test */
    public function login_fallido_usuario_inactivo_en_users()
    {
        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Usuario inactivo');

        try {
            $this->authService->login('INACTIVO', 'password789');
        } catch (AuthException $e) {
            $this->assertEquals(AuthService::ERROR_USER_INACTIVE, $e->getErrorCode());
            throw $e;
        }
    }

    /** @test */
    public function login_fallido_usuario_inhabilitado_en_users()
    {
        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Usuario inactivo');

        try {
            $this->authService->login('INHABILITADO', 'password000');
        } catch (AuthException $e) {
            $this->assertEquals(AuthService::ERROR_USER_INACTIVE, $e->getErrorCode());
            throw $e;
        }
    }

    /** @test */
    public function login_fallido_usuario_inactivo_en_pq_partes_usuarios()
    {
        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Usuario inactivo');

        try {
            $this->authService->login('USUINACTIVO', 'password111');
        } catch (AuthException $e) {
            $this->assertEquals(AuthService::ERROR_USER_INACTIVE, $e->getErrorCode());
            throw $e;
        }
    }

    /** @test */
    public function login_genera_token_sanctum_valido()
    {
        $result = $this->authService->login('JPEREZ', 'password123');

        $this->assertNotEmpty($result['token']);
        // El token de Sanctum tiene formato "id|token"
        $this->assertStringContainsString('|', $result['token']);
    }

    /** @test */
    public function login_retorna_todos_los_campos_requeridos()
    {
        $result = $this->authService->login('JPEREZ', 'password123');

        $userData = $result['user_data'];

        // Verificar que todos los campos requeridos estén presentes
        $this->assertArrayHasKey('user_id', $userData);
        $this->assertArrayHasKey('user_code', $userData);
        $this->assertArrayHasKey('tipo_usuario', $userData);
        $this->assertArrayHasKey('usuario_id', $userData);
        $this->assertArrayHasKey('cliente_id', $userData);
        $this->assertArrayHasKey('es_supervisor', $userData);
        $this->assertArrayHasKey('nombre', $userData);
        $this->assertArrayHasKey('email', $userData);
    }

    /** @test */
    public function error_no_revela_si_usuario_existe()
    {
        // Intento con usuario que no existe
        try {
            $this->authService->login('NOEXISTE', 'password123');
        } catch (AuthException $e) {
            $messageNoExiste = $e->getMessage();
        }

        // Intento con contraseña incorrecta (usuario sí existe)
        try {
            $this->authService->login('JPEREZ', 'incorrecta12');
        } catch (AuthException $e) {
            $messageIncorrecta = $e->getMessage();
        }

        // Ambos mensajes deben ser idénticos (no revelar si usuario existe)
        $this->assertEquals($messageNoExiste, $messageIncorrecta);
        $this->assertEquals('Credenciales inválidas', $messageNoExiste);
    }

    // ========================================
    // Tests de Logout (TR-003)
    // ========================================

    /** @test */
    public function logout_revoca_token_del_usuario()
    {
        // Primero hacer login para obtener un token
        $result = $this->authService->login('JPEREZ', 'password123');
        $this->assertNotEmpty($result['token']);

        // Obtener el usuario
        $user = User::where('code', 'JPEREZ')->first();
        
        // Verificar que el token existe en la base de datos
        $this->assertEquals(1, $user->tokens()->count());

        // Obtener el token y establecerlo como token actual
        $token = $user->tokens()->first();
        $user->withAccessToken($token);

        // Hacer logout
        $this->authService->logout($user);

        // Verificar que el token fue eliminado
        $this->assertEquals(0, $user->fresh()->tokens()->count());
    }

    /** @test */
    public function logout_sin_token_no_genera_error()
    {
        // Obtener usuario que no tiene token
        $user = User::where('code', 'JPEREZ')->first();
        
        // Verificar que no tiene tokens
        $this->assertEquals(0, $user->tokens()->count());

        // Logout no debe generar excepción
        $this->authService->logout($user);

        // Sigue sin tokens
        $this->assertEquals(0, $user->tokens()->count());
    }

    /** @test */
    public function logout_solo_revoca_token_actual()
    {
        // Login para crear primer token
        $result1 = $this->authService->login('JPEREZ', 'password123');
        
        // Login otra vez para crear segundo token
        $result2 = $this->authService->login('JPEREZ', 'password123');

        $user = User::where('code', 'JPEREZ')->first();
        
        // Debe tener 2 tokens
        $this->assertEquals(2, $user->tokens()->count());

        // Obtener el token actual (el más reciente para simular autenticación)
        // En la práctica, el middleware auth:sanctum establece el token actual
        // Para el test, usamos el último creado
        $latestToken = $user->tokens()->latest()->first();
        
        // Simular que el token actual es el último creado
        // Esto se hace normalmente a través de Sanctum cuando se autentica
        $user->withAccessToken($latestToken);

        // Hacer logout (debe eliminar solo el token actual)
        $this->authService->logout($user);

        // Debe quedar 1 token (el primero)
        $this->assertEquals(1, $user->fresh()->tokens()->count());
    }

    // ========================================
    // Tests de Login Cliente (TR-002)
    // ========================================

    /** @test */
    public function login_exitoso_con_cliente()
    {
        $result = $this->authService->login('CLI001', 'cliente123');

        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('user_data', $result);
        $this->assertEquals('CLI001', $result['user_data']['user_code']);
        $this->assertEquals('cliente', $result['user_data']['tipo_usuario']);
        $this->assertFalse($result['user_data']['es_supervisor']);
        $this->assertEquals('Empresa ABC S.A.', $result['user_data']['nombre']);
        $this->assertNull($result['user_data']['usuario_id']);
        $this->assertNotNull($result['user_data']['cliente_id']);
    }

    /** @test */
    public function login_cliente_retorna_es_supervisor_false()
    {
        $result = $this->authService->login('CLI001', 'cliente123');

        // Clientes nunca son supervisores
        $this->assertFalse($result['user_data']['es_supervisor']);
    }

    /** @test */
    public function login_cliente_retorna_usuario_id_null()
    {
        $result = $this->authService->login('CLI001', 'cliente123');

        // Clientes no tienen usuario_id (no son empleados)
        $this->assertNull($result['user_data']['usuario_id']);
    }

    /** @test */
    public function login_fallido_cliente_inactivo_en_pq_partes_clientes()
    {
        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Usuario inactivo');

        try {
            $this->authService->login('CLIINACTIVO', 'cliente456');
        } catch (AuthException $e) {
            $this->assertEquals(AuthService::ERROR_USER_INACTIVE, $e->getErrorCode());
            throw $e;
        }
    }

    /** @test */
    public function login_fallido_usuario_sin_perfil_en_ninguna_tabla()
    {
        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Credenciales inválidas');

        try {
            $this->authService->login('SINPERFIL', 'sinperfil123');
        } catch (AuthException $e) {
            $this->assertEquals(AuthService::ERROR_INVALID_CREDENTIALS, $e->getErrorCode());
            throw $e;
        }
    }

    /** @test */
    public function login_cliente_retorna_todos_los_campos_requeridos()
    {
        $result = $this->authService->login('CLI001', 'cliente123');

        $userData = $result['user_data'];

        // Verificar que todos los campos requeridos estén presentes
        $this->assertArrayHasKey('user_id', $userData);
        $this->assertArrayHasKey('user_code', $userData);
        $this->assertArrayHasKey('tipo_usuario', $userData);
        $this->assertArrayHasKey('usuario_id', $userData);
        $this->assertArrayHasKey('cliente_id', $userData);
        $this->assertArrayHasKey('es_supervisor', $userData);
        $this->assertArrayHasKey('nombre', $userData);
        $this->assertArrayHasKey('email', $userData);

        // Verificar valores específicos de cliente
        $this->assertEquals('cliente', $userData['tipo_usuario']);
        $this->assertNull($userData['usuario_id']);
        $this->assertNotNull($userData['cliente_id']);
        $this->assertFalse($userData['es_supervisor']);
    }

    // ========================================
    // Tests de Cambio de Contraseña (TR-005)
    // ========================================

    /** @test */
    public function change_password_exitoso_actualiza_hash_en_users()
    {
        $user = User::where('code', 'JPEREZ')->first();
        $this->authService->changePassword($user, 'password123', 'nuevaContraseña456');

        $user->refresh();
        $this->assertTrue(Hash::check('nuevaContraseña456', $user->password_hash));
        $this->assertFalse(Hash::check('password123', $user->password_hash));
    }

    /** @test */
    public function change_password_contrasena_actual_incorrecta_lanza_excepcion()
    {
        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('La contraseña actual es incorrecta');

        $user = User::where('code', 'JPEREZ')->first();
        try {
            $this->authService->changePassword($user, 'contraseñaIncorrecta', 'nuevaContraseña456');
        } catch (AuthException $e) {
            $this->assertEquals(AuthService::ERROR_CURRENT_PASSWORD_INVALID, $e->getErrorCode());
            throw $e;
        }
    }

    /** @test */
    public function change_password_nueva_contrasena_muy_corta_lanza_excepcion()
    {
        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('al menos');

        $user = User::where('code', 'JPEREZ')->first();
        try {
            $this->authService->changePassword($user, 'password123', 'short');
        } catch (AuthException $e) {
            $this->assertEquals(422, $e->getErrorCode());
            throw $e;
        }
    }

    /** @test */
    public function change_password_exitoso_usuario_puede_login_con_nueva_contrasena()
    {
        $user = User::where('code', 'JPEREZ')->first();
        $this->authService->changePassword($user, 'password123', 'nuevaContraseña789');

        $result = $this->authService->login('JPEREZ', 'nuevaContraseña789');
        $this->assertArrayHasKey('token', $result);
        $this->assertEquals('JPEREZ', $result['user_data']['user_code']);
    }
}
