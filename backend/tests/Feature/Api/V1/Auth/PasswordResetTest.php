<?php

namespace Tests\Feature\Api\V1\Auth;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Tests de Integración: Endpoints POST forgot-password y POST reset-password
 *
 * @see TR-004(SH)-recuperación-de-contraseña.md
 */
class PasswordResetTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->seedTestUserWithEmail();
    }

    protected function seedTestUserWithEmail(): void
    {
        $testCodes = ['JPEREZ'];
        $userIds = DB::table('USERS')->whereIn('code', $testCodes)->pluck('id');
        if ($userIds->isNotEmpty()) {
            DB::table('personal_access_tokens')
                ->where('tokenable_type', 'App\\Models\\User')
                ->whereIn('tokenable_id', $userIds)
                ->delete();
        }
        DB::table('password_reset_tokens')->where('email', 'juan.perez@ejemplo.com')->delete();
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
    }

    /** @test */
    public function forgot_password_retorna_200_con_mensaje_generico()
    {
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'code_or_email' => 'JPEREZ',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'error' => 0,
                'respuesta' => 'Si el usuario existe y tiene email configurado, recibirá un enlace para restablecer la contraseña.',
            ])
            ->assertJsonPath('resultado', []);
    }

    /** @test */
    public function forgot_password_por_email_retorna_200()
    {
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'code_or_email' => 'juan.perez@ejemplo.com',
        ]);

        $response->assertStatus(200)->assertJson(['error' => 0]);
    }

    /** @test */
    public function forgot_password_sin_code_or_email_retorna_422()
    {
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'code_or_email' => '',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function reset_password_con_token_valido_retorna_200()
    {
        $this->postJson('/api/v1/auth/forgot-password', ['code_or_email' => 'JPEREZ']);
        $row = DB::table('password_reset_tokens')->where('email', 'juan.perez@ejemplo.com')->first();
        $this->assertNotNull($row, 'Token debe existir tras forgot-password');

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'token' => $row->token,
            'password' => 'nuevaPassword123',
            'password_confirmation' => 'nuevaPassword123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'error' => 0,
                'respuesta' => 'Contraseña restablecida correctamente.',
            ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'JPEREZ',
            'password' => 'nuevaPassword123',
        ]);
        $loginResponse->assertStatus(200)->assertJson(['error' => 0]);
    }

    /** @test */
    public function reset_password_con_token_invalido_retorna_422()
    {
        $response = $this->postJson('/api/v1/auth/reset-password', [
            'token' => 'token-inexistente-12345',
            'password' => 'nuevaPassword123',
            'password_confirmation' => 'nuevaPassword123',
        ]);

        $response->assertStatus(422)->assertJsonMissing(['error' => 0]);
    }

    /** @test */
    public function reset_password_contraseña_corta_retorna_422()
    {
        $this->postJson('/api/v1/auth/forgot-password', ['code_or_email' => 'JPEREZ']);
        $row = DB::table('password_reset_tokens')->where('email', 'juan.perez@ejemplo.com')->first();

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'token' => $row->token,
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function reset_password_sin_confirmacion_coincidente_retorna_422()
    {
        $this->postJson('/api/v1/auth/forgot-password', ['code_or_email' => 'JPEREZ']);
        $row = DB::table('password_reset_tokens')->where('email', 'juan.perez@ejemplo.com')->first();

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'token' => $row->token,
            'password' => 'nuevaPassword123',
            'password_confirmation' => 'otraPassword456',
        ]);

        $response->assertStatus(422);
    }
}
