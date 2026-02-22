<?php

namespace App\Services;

use App\Models\User;
use App\Models\Usuario;
use App\Models\Cliente;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

/**
 * Servicio de recuperación de contraseña (forgot/reset).
 * Busca usuario por code o email; genera token y envía correo si tiene email.
 * Restablece contraseña validando token (un solo uso, expiración 1 h).
 *
 * @see TR-004(SH)-recuperación-de-contraseña.md
 */
class PasswordResetService
{
    /**
     * Minutos de validez del token de recuperación
     */
    public const TOKEN_EXPIRATION_MINUTES = 60;

    /**
     * Longitud mínima de la nueva contraseña
     */
    private const MIN_PASSWORD_LENGTH = 8;

    /**
     * Solicitar recuperación: busca usuario por code o email; si tiene email, genera token y envía correo.
     * Siempre retorna éxito genérico (no revelar si el usuario existe o si se envió el correo).
     */
    public function requestReset(string $codeOrEmail): void
    {
        $user = $this->findUserByCodeOrEmail($codeOrEmail);
        if (!$user) {
            return;
        }

        $email = $this->getEmailForUser($user);
        if (!$email || trim($email) === '') {
            return;
        }

        $token = Str::random(64);
        $this->storeToken($email, $token);
        $resetUrl = $this->buildResetUrl($token);
        Mail::to($email)->send(new ResetPasswordMail($resetUrl));
    }

    /**
     * Restablecer contraseña con token. Valida token (existencia, no expirado, un solo uso).
     * Actualiza password_hash en USERS e invalida el token.
     *
     * @throws AuthException Si el token es inválido, expirado o ya usado
     */
    public function resetPassword(string $token, string $password): void
    {
        $row = DB::table('password_reset_tokens')
            ->where('token', $token)
            ->first();

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

        $user = $this->findUserByEmail($row->email);
        if (!$user) {
            $this->deleteTokenByToken($token);
            throw new AuthException('El enlace de recuperación no es válido o ha expirado.', 3205);
        }

        $user->password_hash = Hash::make($password);
        $user->save();
        $this->deleteTokenByToken($token);
    }

    /**
     * Busca User por código (USERS.code) o por email (en Usuario o Cliente).
     */
    private function findUserByCodeOrEmail(string $codeOrEmail): ?User
    {
        $codeOrEmail = trim($codeOrEmail);
        if ($codeOrEmail === '') {
            return null;
        }

        if (str_contains($codeOrEmail, '@')) {
            return $this->findUserByEmail($codeOrEmail);
        }

        return User::where('code', $codeOrEmail)->first();
    }

    /**
     * Busca User por email (en PQ_PARTES_USUARIOS o PQ_PARTES_CLIENTES).
     */
    private function findUserByEmail(string $email): ?User
    {
        $usuario = Usuario::where('email', $email)->first();
        if ($usuario) {
            return $usuario->user;
        }
        $cliente = Cliente::where('email', $email)->first();
        if ($cliente) {
            return $cliente->user;
        }
        return null;
    }

    /**
     * Obtiene el email del usuario (desde Usuario o Cliente).
     */
    private function getEmailForUser(User $user): ?string
    {
        $usuario = $user->usuario;
        if ($usuario && !empty(trim($usuario->email ?? ''))) {
            return trim($usuario->email);
        }
        $cliente = $user->cliente;
        if ($cliente && !empty(trim($cliente->email ?? ''))) {
            return trim($cliente->email);
        }
        return null;
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
