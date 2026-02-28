<?php

namespace App\Services;

use App\Models\User;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

/**
 * Servicio de recuperación de contraseña (forgot/reset).
 * Usa tabla USERS (name, email).
 */
class PasswordResetService
{
    public const TOKEN_EXPIRATION_MINUTES = 60;
    private const MIN_PASSWORD_LENGTH = 8;

    public function requestReset(string $codeOrEmail): void
    {
        $user = $this->findUserByCodeOrEmail($codeOrEmail);
        if (!$user) {
            return;
        }

        $email = $user->email ? trim($user->email) : null;
        if (!$email || $email === '') {
            return;
        }

        $token = Str::random(64);
        $this->storeToken($email, $token);
        $resetUrl = $this->buildResetUrl($token);
        Mail::to($email)->send(new ResetPasswordMail($resetUrl));
    }

    public function resetPassword(string $token, string $password): void
    {
        $row = DB::table('password_reset_tokens')->where('token', $token)->first();

        if (!$row) {
            throw new AuthException('El enlace de recuperación no es válido o ha expirado.', 3205);
        }

        $createdAt = $row->created_at ? \Carbon\Carbon::parse($row->created_at) : null;
        if (!$createdAt || $createdAt->diffInMinutes(now()) > self::TOKEN_EXPIRATION_MINUTES) {
            $this->deleteTokenByToken($token);
            throw new AuthException('El enlace de recuperación ha expirado. Solicite uno nuevo.', 3206);
        }

        if (strlen($password) < self::MIN_PASSWORD_LENGTH) {
            throw new AuthException('La contraseña debe tener al menos ' . self::MIN_PASSWORD_LENGTH . ' caracteres.', 1104);
        }

        $user = User::where('email', $row->email)->first();
        if (!$user) {
            $this->deleteTokenByToken($token);
            throw new AuthException('El enlace de recuperación no es válido o ha expirado.', 3205);
        }

        $user->password_hash = Hash::make($password);
        $user->save();
        $this->deleteTokenByToken($token);
    }

    private function findUserByCodeOrEmail(string $codeOrEmail): ?User
    {
        $codeOrEmail = trim($codeOrEmail);
        if ($codeOrEmail === '') {
            return null;
        }

        if (str_contains($codeOrEmail, '@')) {
            return User::where('email', $codeOrEmail)->first();
        }

        return User::where('code', $codeOrEmail)->first();
    }

    private function storeToken(string $email, string $token): void
    {
        DB::table('password_reset_tokens')->where('email', $email)->delete();
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => now(),
        ]);
    }

    private function deleteTokenByToken(string $token): void
    {
        DB::table('password_reset_tokens')->where('token', $token)->delete();
    }

    private function buildResetUrl(string $token): string
    {
        $frontUrl = rtrim(config('app.frontend_url'), '/');
        return $frontUrl . '/reset-password?token=' . urlencode($token);
    }
}
