<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Tests de Integración: Endpoint POST /api/v1/auth/change-password
 *
 * TR-005(SH) Cambio de contraseña (usuario autenticado).
 *
 * @see TR-005(SH)-cambio-de-contraseña-usuario-autenticado.md
 */
class ChangePasswordTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedTestUsers();
    }

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
            'code' => 'JPEREZ',
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
    public function change_password_exitoso_retorna_200()
    {
        $user = User::where('codigo', 'JPEREZ')->first();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/auth/change-password', [
            'current_password' => 'password123',
            'password' => 'nuevaContraseña456',
            'password_confirmation' => 'nuevaContraseña456',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'error' => 0,
                'respuesta' => 'Contraseña actualizada correctamente.',
            ]);
    }

    /** @test */
    public function change_password_sin_token_retorna_401()
    {
        $response = $this->postJson('/api/v1/auth/change-password', [
            'current_password' => 'password123',
            'password' => 'nuevaContraseña456',
            'password_confirmation' => 'nuevaContraseña456',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function change_password_contrasena_actual_incorrecta_retorna_422()
    {
        $user = User::where('codigo', 'JPEREZ')->first();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/auth/change-password', [
            'current_password' => 'contraseñaIncorrecta',
            'password' => 'nuevaContraseña456',
            'password_confirmation' => 'nuevaContraseña456',
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['respuesta' => 'La contraseña actual es incorrecta']);
    }

    /** @test */
    public function change_password_confirmacion_no_coincide_retorna_422()
    {
        $user = User::where('codigo', 'JPEREZ')->first();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/auth/change-password', [
            'current_password' => 'password123',
            'password' => 'nuevaContraseña456',
            'password_confirmation' => 'otraConfirmacion',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function change_password_nueva_contrasena_corta_retorna_422()
    {
        $user = User::where('codigo', 'JPEREZ')->first();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/auth/change-password', [
            'current_password' => 'password123',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function change_password_exitoso_actualiza_hash_en_base_de_datos()
    {
        $user = User::where('codigo', 'JPEREZ')->first();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/auth/change-password', [
            'current_password' => 'password123',
            'password' => 'nuevaContraseña789',
            'password_confirmation' => 'nuevaContraseña789',
        ])->assertStatus(200);

        $user->refresh();
        $this->assertTrue(Hash::check('nuevaContraseña789', $user->password_hash));
    }
}
