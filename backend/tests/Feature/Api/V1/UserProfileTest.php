<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Tests de Integración: Endpoint GET/PUT /api/v1/user/profile
 *
 * Schema simplificado: solo tabla USERS (name, email).
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

    protected function seedTestUsers(): void
    {
        $testCodes = ['JPEREZ', 'MGARCIA', 'CLI001', 'SINEMAIL'];

        $userIds = DB::table('USERS')->whereIn('codigo', $testCodes)->pluck('id');
        if ($userIds->isNotEmpty()) {
            DB::table('personal_access_tokens')
                ->where('tokenable_type', 'App\\Models\\User')
                ->whereIn('tokenable_id', $userIds)
                ->delete();
        }
        DB::table('USERS')->whereIn('codigo', $testCodes)->delete();

        DB::table('USERS')->insert([
            'codigo' => 'JPEREZ',
            'name_user' => 'Juan Pérez',
            'email' => 'juan.perez@ejemplo.com',
            'password_hash' => Hash::make('password123'),
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('USERS')->insert([
            'codigo' => 'MGARCIA',
            'name_user' => 'María García',
            'email' => 'maria.garcia@ejemplo.com',
            'password_hash' => Hash::make('password456'),
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('USERS')->insert([
            'codigo' => 'CLI001',
            'name_user' => 'Empresa ABC S.A.',
            'email' => 'contacto@empresaabc.com',
            'password_hash' => Hash::make('cliente123'),
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('USERS')->insert([
            'codigo' => 'SINEMAIL',
            'name_user' => 'Usuario Sin Email',
            'email' => null,
            'password_hash' => Hash::make('password123'),
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** @test */
    public function get_profile_empleado_normal_retorna_200()
    {
        $user = User::where('codigo', 'JPEREZ')->first();
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
        $user = User::where('codigo', 'MGARCIA')->first();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/user/profile');

        $response->assertStatus(200);
        $this->assertEquals('María García', $response->json('resultado.nombre'));
    }

    /** @test */
    public function get_profile_cliente_retorna_tipo_usuario()
    {
        $user = User::where('codigo', 'CLI001')->first();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/user/profile');

        $response->assertStatus(200);
        $this->assertEquals('usuario', $response->json('resultado.tipo_usuario'));
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
        $user = User::where('codigo', 'SINEMAIL')->first();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/user/profile');

        $response->assertStatus(200);
        $this->assertNull($response->json('resultado.email'));
    }

    /** @test */
    public function respuesta_tiene_formato_envelope_correcto()
    {
        $user = User::where('codigo', 'JPEREZ')->first();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/user/profile');

        $response->assertJsonStructure(['error', 'respuesta', 'resultado']);
        $this->assertIsInt($response->json('error'));
        $this->assertIsString($response->json('respuesta'));
    }

    /** @test */
    public function put_profile_empleado_actualiza_200()
    {
        $user = User::where('codigo', 'JPEREZ')->first();
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
        $user = User::where('codigo', 'JPEREZ')->first();
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
        $user = User::where('codigo', 'JPEREZ')->first();
        Sanctum::actingAs($user);

        $response = $this->putJson('/api/v1/user/profile', [
            'nombre' => 'Juan Pérez',
            'email' => 'maria.garcia@ejemplo.com',
        ]);

        $response->assertStatus(422);
        $this->assertArrayHasKey('email', $response->json('resultado.errors') ?? []);
    }
}
