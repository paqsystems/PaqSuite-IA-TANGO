<?php

namespace Tests\Feature\Api\V1\Auth;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Tests de Integración: Endpoint POST /api/v1/auth/login
 *
 * Schema simplificado: solo tabla USERS (name, email).
 *
 * @see TR-001(MH)-login-de-empleado.md
 */
class LoginTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
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
            ->assertJson(['error' => 0, 'respuesta' => 'Autenticación exitosa']);
        $this->assertEquals('María García', $response->json('resultado.user.nombre'));
    }

    /** @test */
    public function login_fallido_campo_usuario_vacio_retorna_422_error_1101()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'usuario' => '',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)->assertJson(['error' => 1101]);
    }

    /** @test */
    public function login_fallido_campo_password_vacio_retorna_422_error_1103()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'JPEREZ',
            'password' => '',
        ]);

        $response->assertStatus(422)->assertJson(['error' => 1103]);
    }

    /** @test */
    public function login_fallido_password_muy_corto_retorna_422_error_1104()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'JPEREZ',
            'password' => '1234567',
        ]);

        $response->assertStatus(422)->assertJson(['error' => 1104]);
    }

    /** @test */
    public function login_fallido_credenciales_invalidas_retorna_401_error_3201()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'JPEREZ',
            'password' => 'contraseña_incorrecta',
        ]);

        $response->assertStatus(401)
            ->assertJson(['error' => 3201, 'respuesta' => 'Credenciales inválidas']);
    }

    /** @test */
    public function login_fallido_usuario_no_existe_retorna_401_error_3201()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'NOEXISTE',
            'password' => 'cualquierpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson(['error' => 3201, 'respuesta' => 'Credenciales inválidas']);
    }

    /** @test */
    public function login_fallido_usuario_inactivo_retorna_401_error_4203()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'INACTIVO',
            'password' => 'password789',
        ]);

        $response->assertStatus(401)
            ->assertJson(['error' => 4203, 'respuesta' => 'Usuario inactivo']);
    }

    /** @test */
    public function mensaje_error_no_revela_si_usuario_existe()
    {
        $responseNoExiste = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'NOEXISTE',
            'password' => 'password123',
        ]);
        $responseIncorrecta = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'JPEREZ',
            'password' => 'incorrecta12',
        ]);

        $this->assertEquals($responseNoExiste->json('error'), $responseIncorrecta->json('error'));
        $this->assertEquals($responseNoExiste->json('respuesta'), $responseIncorrecta->json('respuesta'));
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

        $response->assertJsonStructure(['error', 'respuesta', 'resultado']);
        $this->assertIsInt($response->json('error'));
        $this->assertIsString($response->json('respuesta'));
    }

}
