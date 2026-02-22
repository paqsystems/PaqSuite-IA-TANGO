<?php

namespace Tests\Feature\Api\V1\Auth;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Tests de Integración: Endpoint POST /api/v1/auth/login
 * 
 * Tests del endpoint de autenticación con diferentes escenarios.
 * Usa DatabaseTransactions para mejor rendimiento con SQL Server remoto.
 * 
 * @see TR-001(MH)-login-de-empleado.md
 * @see TR-002(SH)-login-de-cliente.md
 */
class LoginTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
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
        $testCodes = ['JPEREZ', 'MGARCIA', 'INACTIVO', 'INHABILITADO', 'CLI001', 'CLIINACTIVO'];
        
        // Eliminar registros de tarea que referencian a estos usuarios (FK fk_registro_usuario)
        $usuarioIds = DB::table('PQ_PARTES_USUARIOS')->whereIn('code', $testCodes)->pluck('id');
        if ($usuarioIds->isNotEmpty()) {
            DB::table('PQ_PARTES_REGISTRO_TAREA')->whereIn('usuario_id', $usuarioIds)->delete();
        }
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
    public function login_exitoso_empleado_normal_retorna_200()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'JPEREZ',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'error' => 0,
                'respuesta' => 'Autenticación exitosa',
            ])
            ->assertJsonStructure([
                'error',
                'respuesta',
                'resultado' => [
                    'token',
                    'user' => [
                        'user_id',
                        'user_code',
                        'tipo_usuario',
                        'usuario_id',
                        'cliente_id',
                        'es_supervisor',
                        'nombre',
                        'email',
                    ],
                ],
            ]);

        // Verificar valores específicos
        $this->assertEquals('JPEREZ', $response->json('resultado.user.user_code'));
        $this->assertEquals('usuario', $response->json('resultado.user.tipo_usuario'));
        $this->assertFalse($response->json('resultado.user.es_supervisor'));
        $this->assertNull($response->json('resultado.user.cliente_id'));
    }

    /** @test */
    public function login_exitoso_empleado_supervisor_retorna_es_supervisor_true()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'MGARCIA',
            'password' => 'password456',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'error' => 0,
                'respuesta' => 'Autenticación exitosa',
            ]);

        $this->assertTrue($response->json('resultado.user.es_supervisor'));
        $this->assertEquals('María García', $response->json('resultado.user.nombre'));
    }

    /** @test */
    public function login_fallido_campo_usuario_vacio_retorna_422_error_1101()
    {
        // Campo vacío '' es convertido a null por Laravel middleware
        // Por lo tanto falla en Required, retorna error 1101
        $response = $this->postJson('/api/v1/auth/login', [
            'usuario' => '',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => 1101,
            ]);
    }

    /** @test */
    public function login_fallido_campo_password_vacio_retorna_422_error_1103()
    {
        // Campo vacío '' es convertido a null por Laravel middleware
        // Por lo tanto falla en Required, retorna error 1103
        $response = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'JPEREZ',
            'password' => '',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => 1103,
            ]);
    }

    /** @test */
    public function login_fallido_password_muy_corto_retorna_422_error_1104()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'JPEREZ',
            'password' => '1234567', // Solo 7 caracteres
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => 1104,
            ]);
    }

    /** @test */
    public function login_fallido_credenciales_invalidas_retorna_401_error_3201()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'JPEREZ',
            'password' => 'contraseña_incorrecta',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 3201,
                'respuesta' => 'Credenciales inválidas',
            ]);
    }

    /** @test */
    public function login_fallido_usuario_no_existe_retorna_401_error_3201()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'NOEXISTE',
            'password' => 'cualquierpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 3201,
                'respuesta' => 'Credenciales inválidas',
            ]);
    }

    /** @test */
    public function login_fallido_usuario_inactivo_retorna_401_error_4203()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'INACTIVO',
            'password' => 'password789',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 4203,
                'respuesta' => 'Usuario inactivo',
            ]);
    }

    /** @test */
    public function mensaje_error_no_revela_si_usuario_existe()
    {
        // Intento con usuario que no existe
        $responseNoExiste = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'NOEXISTE',
            'password' => 'password123',
        ]);

        // Intento con contraseña incorrecta (usuario sí existe)
        $responseIncorrecta = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'JPEREZ',
            'password' => 'incorrecta12',
        ]);

        // Ambos deben retornar el mismo mensaje y código
        $this->assertEquals(
            $responseNoExiste->json('error'),
            $responseIncorrecta->json('error')
        );
        $this->assertEquals(
            $responseNoExiste->json('respuesta'),
            $responseIncorrecta->json('respuesta')
        );
    }

    /** @test */
    public function login_exitoso_genera_token_valido()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'JPEREZ',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);

        $token = $response->json('resultado.token');
        $this->assertNotEmpty($token);
        $this->assertStringContainsString('|', $token);
    }

    /** @test */
    public function respuesta_tiene_formato_envelope_correcto()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'JPEREZ',
            'password' => 'password123',
        ]);

        $response->assertJsonStructure([
            'error',
            'respuesta',
            'resultado',
        ]);

        $this->assertIsInt($response->json('error'));
        $this->assertIsString($response->json('respuesta'));
    }

    // ========================================
    // Tests de Login Cliente (TR-002)
    // ========================================

    /** @test */
    public function login_exitoso_cliente_retorna_200()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'CLI001',
            'password' => 'cliente123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'error' => 0,
                'respuesta' => 'Autenticación exitosa',
            ])
            ->assertJsonStructure([
                'error',
                'respuesta',
                'resultado' => [
                    'token',
                    'user' => [
                        'user_id',
                        'user_code',
                        'tipo_usuario',
                        'usuario_id',
                        'cliente_id',
                        'es_supervisor',
                        'nombre',
                        'email',
                    ],
                ],
            ]);
    }

    /** @test */
    public function login_cliente_retorna_tipo_usuario_cliente()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'CLI001',
            'password' => 'cliente123',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('cliente', $response->json('resultado.user.tipo_usuario'));
        $this->assertEquals('CLI001', $response->json('resultado.user.user_code'));
    }

    /** @test */
    public function login_cliente_retorna_es_supervisor_false()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'CLI001',
            'password' => 'cliente123',
        ]);

        $response->assertStatus(200);

        // Clientes nunca son supervisores
        $this->assertFalse($response->json('resultado.user.es_supervisor'));
    }

    /** @test */
    public function login_cliente_retorna_usuario_id_null_y_cliente_id_valido()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'CLI001',
            'password' => 'cliente123',
        ]);

        $response->assertStatus(200);

        // Clientes tienen cliente_id pero no usuario_id
        $this->assertNull($response->json('resultado.user.usuario_id'));
        $this->assertNotNull($response->json('resultado.user.cliente_id'));
    }

    /** @test */
    public function login_fallido_cliente_inactivo_retorna_401_error_4203()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'CLIINACTIVO',
            'password' => 'cliente456',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 4203,
                'respuesta' => 'Usuario inactivo',
            ]);
    }

    /** @test */
    public function login_cliente_genera_token_valido()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'CLI001',
            'password' => 'cliente123',
        ]);

        $response->assertStatus(200);

        $token = $response->json('resultado.token');
        $this->assertNotEmpty($token);
        $this->assertStringContainsString('|', $token);
    }
}
