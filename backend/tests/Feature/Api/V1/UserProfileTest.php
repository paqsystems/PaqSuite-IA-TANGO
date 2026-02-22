<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Tests de Integración: Endpoint GET /api/v1/user/profile
 * 
 * Tests del endpoint de perfil de usuario con diferentes escenarios.
 * Usa DatabaseTransactions para mejor rendimiento con SQL Server remoto.
 * 
 * @see TR-006(MH)-visualización-de-perfil-de-usuario.md
 */
class UserProfileTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedTestUsers();
    }

    /**
     * Crear usuarios de prueba para los tests
     */
    protected function seedTestUsers(): void
    {
        $testCodes = ['JPEREZ', 'MGARCIA', 'CLI001'];
        
        $usuarioIds = DB::table('PQ_PARTES_USUARIOS')->whereIn('code', $testCodes)->pluck('id');
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

        // Empleado normal
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

        // Empleado supervisor
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

        // Cliente
        DB::table('USERS')->insert([
            'code' => 'CLI001',
            'password_hash' => Hash::make('cliente123'),
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cli001Id = DB::table('USERS')->where('code', 'CLI001')->value('id');

        $tipoClienteId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->where('code', 'CORP')->value('id');
        if (!$tipoClienteId) {
            $tipoClienteId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->first()?->id;
        }

        if ($tipoClienteId) {
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
    }

    /** @test */
    public function get_profile_empleado_normal_retorna_200()
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/user/profile');

        $response->assertStatus(200)
            ->assertJson([
                'error' => 0,
                'respuesta' => 'Perfil obtenido correctamente',
            ])
            ->assertJsonStructure([
                'error',
                'respuesta',
                'resultado' => [
                    'user_code',
                    'nombre',
                    'email',
                    'tipo_usuario',
                    'es_supervisor',
                    'created_at',
                ],
            ]);

        $this->assertEquals('JPEREZ', $response->json('resultado.user_code'));
        $this->assertEquals('Juan Pérez', $response->json('resultado.nombre'));
        $this->assertEquals('usuario', $response->json('resultado.tipo_usuario'));
        $this->assertFalse($response->json('resultado.es_supervisor'));
    }

    /** @test */
    public function get_profile_empleado_supervisor_retorna_es_supervisor_true()
    {
        $user = User::where('code', 'MGARCIA')->first();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/user/profile');

        $response->assertStatus(200);
        $this->assertTrue($response->json('resultado.es_supervisor'));
        $this->assertEquals('María García', $response->json('resultado.nombre'));
    }

    /** @test */
    public function get_profile_cliente_retorna_tipo_cliente()
    {
        $user = User::where('code', 'CLI001')->first();
        
        if (!$user) {
            $this->markTestSkipped('Cliente CLI001 no existe en la base de datos');
        }

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/user/profile');

        $response->assertStatus(200);
        $this->assertEquals('cliente', $response->json('resultado.tipo_usuario'));
        $this->assertFalse($response->json('resultado.es_supervisor'));
        $this->assertEquals('Empresa ABC S.A.', $response->json('resultado.nombre'));
    }

    /** @test */
    public function get_profile_sin_autenticacion_retorna_401()
    {
        $response = $this->getJson('/api/v1/user/profile');

        $response->assertStatus(401);
    }

    /** @test */
    public function get_profile_empleado_sin_email_retorna_null()
    {
        // Crear empleado sin email
        DB::table('USERS')->insert([
            'code' => 'SINEMAIL',
            'password_hash' => Hash::make('password123'),
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sinEmailId = DB::table('USERS')->where('code', 'SINEMAIL')->value('id');

        DB::table('PQ_PARTES_USUARIOS')->insert([
            'user_id' => $sinEmailId,
            'code' => 'SINEMAIL',
            'nombre' => 'Usuario Sin Email',
            'email' => null,
            'supervisor' => false,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = User::where('code', 'SINEMAIL')->first();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/user/profile');

        $response->assertStatus(200);
        $this->assertNull($response->json('resultado.email'));
    }

    /** @test */
    public function respuesta_tiene_formato_envelope_correcto()
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/user/profile');

        $response->assertJsonStructure([
            'error',
            'respuesta',
            'resultado',
        ]);

        $this->assertIsInt($response->json('error'));
        $this->assertIsString($response->json('respuesta'));
    }

    /** @test */
    public function put_profile_empleado_actualiza_200()
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);

        $response = $this->putJson('/api/v1/user/profile', [
            'nombre' => 'Juan Pérez Actualizado',
            'email' => 'nuevo@ejemplo.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'error' => 0,
                'respuesta' => 'Perfil actualizado correctamente',
            ])
            ->assertJsonPath('resultado.nombre', 'Juan Pérez Actualizado')
            ->assertJsonPath('resultado.email', 'nuevo@ejemplo.com')
            ->assertJsonPath('resultado.user_code', 'JPEREZ');
    }

    /** @test */
    public function put_profile_sin_autenticacion_retorna_401()
    {
        $response = $this->putJson('/api/v1/user/profile', [
            'nombre' => 'Cualquier Nombre',
            'email' => null,
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function put_profile_nombre_vacio_retorna_422()
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);

        $response = $this->putJson('/api/v1/user/profile', [
            'nombre' => '',
            'email' => 'juan@ejemplo.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error', 1000);
    }

    /** @test */
    public function put_profile_email_duplicado_retorna_422()
    {
        $user = User::where('code', 'JPEREZ')->first();
        Sanctum::actingAs($user);

        $response = $this->putJson('/api/v1/user/profile', [
            'nombre' => 'Juan Pérez',
            'email' => 'maria.garcia@ejemplo.com',
        ]);

        $response->assertStatus(422);
        $this->assertArrayHasKey('email', $response->json('resultado.errors') ?? []);
    }
}
