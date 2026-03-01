<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Tests de Integración: Endpoint POST /api/v1/auth/logout
 * 
 * Tests del endpoint de logout con diferentes escenarios.
 * Usa DatabaseTransactions para mejor rendimiento con SQL Server remoto.
 * 
 * @see TR-003(MH)-logout.md
 */
class LogoutTest extends TestCase
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
        $testCodes = ['JPEREZ'];
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
    }

    /** @test */
    public function logout_exitoso_retorna_200()
    {
        // Crear usuario y autenticar con Sanctum
        $user = User::where('codigo', 'JPEREZ')->first();
        Sanctum::actingAs($user);

        // Hacer logout
        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'error' => 0,
                'respuesta' => 'Sesión cerrada correctamente',
            ]);
    }

    /** @test */
    public function logout_sin_token_retorna_401()
    {
        // Hacer logout sin autenticación
        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(401);
    }

    /** @test */
    public function logout_revoca_token_en_base_de_datos()
    {
        // Crear usuario y token real
        $user = User::where('codigo', 'JPEREZ')->first();
        $token = $user->createToken('test_token')->plainTextToken;

        // Verificar que el token existe
        $this->assertEquals(1, $user->tokens()->count());

        // Hacer logout con el token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/auth/logout');

        $response->assertStatus(200);

        // Verificar que el token fue eliminado
        $this->assertEquals(0, $user->fresh()->tokens()->count());
    }

    /** @test */
    public function logout_con_token_invalido_retorna_401()
    {
        // Usar token inválido
        $response = $this->withHeaders([
            'Authorization' => 'Bearer token_invalido_123',
        ])->postJson('/api/v1/auth/logout');

        $response->assertStatus(401);
    }

    /** @test */
    public function logout_respuesta_tiene_formato_envelope_correcto()
    {
        $user = User::where('codigo', 'JPEREZ')->first();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'error',
                'respuesta',
                'resultado',
            ]);

        // Verificar que resultado es un objeto/array vacío, no null
        $data = $response->json();
        $this->assertNotNull($data['resultado']);
        $this->assertEmpty($data['resultado']); // {} o [] ambos son válidos y vacíos
    }

    /** @test */
    public function token_usado_para_logout_es_eliminado_de_base_de_datos()
    {
        // Crear usuario y token real
        $user = User::where('codigo', 'JPEREZ')->first();
        $tokenResult = $user->createToken('test_token');
        $token = $tokenResult->plainTextToken;
        $tokenId = $tokenResult->accessToken->id;

        // Verificar que el token existe en la base de datos
        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $tokenId,
        ]);

        // Hacer logout con el token
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/auth/logout')
            ->assertStatus(200);

        // Verificar que el token fue eliminado de la base de datos
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenId,
        ]);
    }

    /** @test */
    public function logout_solo_revoca_token_actual_no_todos()
    {
        $user = User::where('codigo', 'JPEREZ')->first();
        
        // Crear dos tokens (simulando login desde dos dispositivos)
        $token1 = $user->createToken('device_1')->plainTextToken;
        $token2 = $user->createToken('device_2')->plainTextToken;

        // Verificar que tiene 2 tokens
        $this->assertEquals(2, $user->tokens()->count());

        // Hacer logout con el segundo token
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token2,
        ])->postJson('/api/v1/auth/logout')
            ->assertStatus(200);

        // Debe quedar 1 token (el primero)
        $this->assertEquals(1, $user->fresh()->tokens()->count());

        // El primer token debe seguir funcionando
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token1,
        ])->getJson('/api/v1/user')
            ->assertStatus(200);
    }
}
