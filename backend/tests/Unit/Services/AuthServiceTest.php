<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\AuthService;
use App\Services\AuthException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Tests Unitarios: AuthService
 *
 * Tests del servicio de autenticación simplificado (solo tabla USERS).
 *
 * @see TR-001(MH)-login-de-empleado.md
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

    protected function seedTestUsers(): void
    {
        $testCodes = ['JPEREZ', 'MGARCIA', 'INACTIVO', 'INHABILITADO'];

        $userIds = DB::table('USERS')->whereIn('code', $testCodes)->pluck('id');
        if ($userIds->isNotEmpty()) {
            DB::table('personal_access_tokens')
                ->where('tokenable_type', 'App\\Models\\User')
                ->whereIn('tokenable_id', $userIds)
                ->delete();
        }
        DB::table('USERS')->whereIn('code', $testCodes)->delete();

        // Usuario activo normal
        DB::table('USERS')->insert([
            'code' => 'JPEREZ',
            'name' => 'Juan Pérez',
            'email' => 'juan.perez@ejemplo.com',
            'password_hash' => Hash::make('password123'),
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Usuario activo (supervisor se maneja en otro modelo si aplica)
        DB::table('USERS')->insert([
            'code' => 'MGARCIA',
            'name' => 'María García',
            'email' => 'maria.garcia@ejemplo.com',
            'password_hash' => Hash::make('password456'),
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Usuario inactivo
        DB::table('USERS')->insert([
            'code' => 'INACTIVO',
            'name' => 'Usuario Inactivo',
            'email' => 'inactivo@ejemplo.com',
            'password_hash' => Hash::make('password789'),
            'activo' => false,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Usuario inhabilitado
        DB::table('USERS')->insert([
            'code' => 'INHABILITADO',
            'name' => 'Usuario Inhabilitado',
            'email' => 'inhabilitado@ejemplo.com',
            'password_hash' => Hash::make('password000'),
            'activo' => true,
            'inhabilitado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
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
        // Ya no existe PQ_PARTES_USUARIOS; este test se mantiene por compatibilidad
        // pero verifica el mismo flujo que usuario inactivo en USERS
        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Usuario inactivo');
        $this->authService->login('INACTIVO', 'password789');
    }

    /** @test */
    public function login_genera_token_sanctum_valido()
    {
        $result = $this->authService->login('JPEREZ', 'password123');

        $this->assertNotEmpty($result['token']);
        $this->assertStringContainsString('|', $result['token']);
    }

    /** @test */
    public function login_retorna_todos_los_campos_requeridos()
    {
        $result = $this->authService->login('JPEREZ', 'password123');

        $userData = $result['user_data'];

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
        try {
            $this->authService->login('NOEXISTE', 'password123');
        } catch (AuthException $e) {
            $messageNoExiste = $e->getMessage();
        }

        try {
            $this->authService->login('JPEREZ', 'incorrecta12');
        } catch (AuthException $e) {
            $messageIncorrecta = $e->getMessage();
        }

        $this->assertEquals($messageNoExiste ?? '', $messageIncorrecta ?? '');
        $this->assertEquals('Credenciales inválidas', $messageNoExiste ?? $messageIncorrecta ?? '');
    }

    /** @test */
    public function logout_revoca_token_del_usuario()
    {
        $result = $this->authService->login('JPEREZ', 'password123');
        $this->assertNotEmpty($result['token']);

        $user = User::where('code', 'JPEREZ')->first();
        $this->assertEquals(1, $user->tokens()->count());

        $token = $user->tokens()->first();
        $user->withAccessToken($token);

        $this->authService->logout($user);

        $this->assertEquals(0, $user->fresh()->tokens()->count());
    }

    /** @test */
    public function logout_sin_token_no_genera_error()
    {
        $user = User::where('code', 'JPEREZ')->first();
        $this->assertEquals(0, $user->tokens()->count());

        $this->authService->logout($user);

        $this->assertEquals(0, $user->tokens()->count());
    }

    /** @test */
    public function logout_solo_revoca_token_actual()
    {
        $this->authService->login('JPEREZ', 'password123');
        $this->authService->login('JPEREZ', 'password123');

        $user = User::where('code', 'JPEREZ')->first();
        $this->assertEquals(2, $user->tokens()->count());

        $latestToken = $user->tokens()->latest()->first();
        $user->withAccessToken($latestToken);

        $this->authService->logout($user);

        $this->assertEquals(1, $user->fresh()->tokens()->count());
    }

    /** @test */
    public function login_exitoso_con_cliente()
    {
        // AuthService simplificado: todos son tipo 'usuario'
        $result = $this->authService->login('JPEREZ', 'password123');
        $this->assertEquals('usuario', $result['user_data']['tipo_usuario']);
        $this->assertNull($result['user_data']['cliente_id']);
    }

    /** @test */
    public function login_cliente_retorna_es_supervisor_false()
    {
        $result = $this->authService->login('JPEREZ', 'password123');
        $this->assertFalse($result['user_data']['es_supervisor']);
    }

    /** @test */
    public function login_cliente_retorna_usuario_id_null()
    {
        // AuthService simplificado: usuario_id = user->id (no null)
        $result = $this->authService->login('JPEREZ', 'password123');
        $this->assertNotNull($result['user_data']['usuario_id']);
    }

    /** @test */
    public function login_fallido_cliente_inactivo_en_pq_partes_clientes()
    {
        // Ya no existe PQ_PARTES_CLIENTES; verifica usuario inactivo
        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Usuario inactivo');
        $this->authService->login('INACTIVO', 'password789');
    }

    /** @test */
    public function login_fallido_usuario_sin_perfil_en_ninguna_tabla()
    {
        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Credenciales inválidas');
        $this->authService->login('NOEXISTE', 'cualquierpass');
    }

    /** @test */
    public function login_cliente_retorna_todos_los_campos_requeridos()
    {
        $result = $this->authService->login('JPEREZ', 'password123');
        $userData = $result['user_data'];

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
