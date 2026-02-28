<?php

namespace Tests\Unit\Services;

use App\Mail\ResetPasswordMail;
use App\Services\AuthException;
use App\Services\PasswordResetService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Tests unitarios: PasswordResetService (requestReset, resetPassword)
 *
 * Usa tabla USERS (code, name, email).
 *
 * @see TR-004(SH)-recuperación-de-contraseña.md
 */
class PasswordResetServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected PasswordResetService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->service = new PasswordResetService();
        $this->seedTestUser();
    }

    protected function seedTestUser(): void
    {
        $testCodes = ['PWUSER'];
        $userIds = DB::table('USERS')->whereIn('code', $testCodes)->pluck('id');
        if ($userIds->isNotEmpty()) {
            DB::table('personal_access_tokens')
                ->where('tokenable_type', 'App\\Models\\User')
                ->whereIn('tokenable_id', $userIds)
                ->delete();
        }
        DB::table('USERS')->whereIn('code', $testCodes)->delete();
        DB::table('password_reset_tokens')->where('email', 'pwuser@test.com')->delete();

        DB::table('USERS')->insert([
            'code' => 'PWUSER',
            'name' => 'Password User',
            'email' => 'pwuser@test.com',
            'password_hash' => Hash::make('oldpass'),
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** @test */
    public function request_reset_con_codigo_valido_envia_correo()
    {
        $this->service->requestReset('PWUSER');

        Mail::assertSent(ResetPasswordMail::class, function ($mail) {
            return true;
        });
        $row = DB::table('password_reset_tokens')->where('email', 'pwuser@test.com')->first();
        $this->assertNotNull($row);
        $this->assertNotEmpty($row->token);
    }

    /** @test */
    public function request_reset_con_email_valido_envia_correo()
    {
        $this->service->requestReset('pwuser@test.com');

        Mail::assertSent(ResetPasswordMail::class);
        $row = DB::table('password_reset_tokens')->where('email', 'pwuser@test.com')->first();
        $this->assertNotNull($row);
    }

    /** @test */
    public function request_reset_usuario_inexistente_no_lanza_y_no_envia_correo()
    {
        $this->service->requestReset('NOEXISTE');

        Mail::assertNotSent(ResetPasswordMail::class);
    }

    /** @test */
    public function reset_password_con_token_valido_actualiza_password_y_borra_token()
    {
        $this->service->requestReset('PWUSER');
        $row = DB::table('password_reset_tokens')->where('email', 'pwuser@test.com')->first();

        $this->service->resetPassword($row->token, 'newPassword123');

        $user = DB::table('USERS')->where('code', 'PWUSER')->first();
        $this->assertTrue(Hash::check('newPassword123', $user->password_hash));
        $this->assertNull(DB::table('password_reset_tokens')->where('token', $row->token)->first());
    }

    /** @test */
    public function reset_password_token_invalido_lanza_auth_exception()
    {
        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('El enlace de recuperación no es válido o ha expirado.');

        $this->service->resetPassword('token-invalido-xyz', 'newPassword123');
    }

    /** @test */
    public function reset_password_contraseña_corta_lanza_auth_exception()
    {
        $this->service->requestReset('PWUSER');
        $row = DB::table('password_reset_tokens')->where('email', 'pwuser@test.com')->first();

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('La contraseña debe tener al menos 8 caracteres.');

        $this->service->resetPassword($row->token, 'short');
    }

    /** @test */
    public function reset_password_token_expirado_lanza_auth_exception()
    {
        $this->service->requestReset('PWUSER');
        $row = DB::table('password_reset_tokens')->where('email', 'pwuser@test.com')->first();
        DB::table('password_reset_tokens')
            ->where('email', 'pwuser@test.com')
            ->update(['created_at' => now()->subMinutes(61)]);

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('El enlace de recuperación ha expirado');

        $this->service->resetPassword($row->token, 'newPassword123');
    }
}
