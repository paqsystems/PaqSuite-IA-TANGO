<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Service: AuthService
 *
 * Servicio de autenticación simplificado (sin sistema de partes).
 * Login por code + password contra tabla USERS.
 *
 * @see TR-001(MH)-login-de-empleado.md
 * @see TR-003(MH)-logout.md
 * @see TR-005(SH)-cambio-de-contraseña-usuario-autenticado.md
 */
class AuthService
{
    public const ERROR_INVALID_CREDENTIALS = 3201;
    public const ERROR_USER_NOT_FOUND = 3202;
    public const ERROR_WRONG_PASSWORD = 3203;
    public const ERROR_CURRENT_PASSWORD_INVALID = 3204;
    public const ERROR_NOT_AUTHENTICATED = 4001;
    public const ERROR_USER_INACTIVE = 4203;

    private const MIN_PASSWORD_LENGTH = 8;

    public function login(string $usuario, string $password): array
    {
        $user = User::where('code', $usuario)->first();

        if (!$user) {
            throw new AuthException('Credenciales inválidas', self::ERROR_INVALID_CREDENTIALS);
        }

        if (!$user->activo || $user->inhabilitado) {
            throw new AuthException('Usuario inactivo', self::ERROR_USER_INACTIVE);
        }

        if (!Hash::check($password, $user->password_hash)) {
            throw new AuthException('Credenciales inválidas', self::ERROR_INVALID_CREDENTIALS);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'token' => $token,
            'user_data' => [
                'user_id' => $user->id,
                'user_code' => $user->code,
                'nombre' => $user->name ?? $user->code,
                'email' => $user->email,
                'tipo_usuario' => 'usuario',
                'usuario_id' => $user->id,
                'cliente_id' => null,
                'es_supervisor' => false,
            ],
        ];
    }

    public function logout(User $user): void
    {
        $currentToken = $user->currentAccessToken();
        if ($currentToken) {
            $currentToken->delete();
        }
    }

    public function changePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (!Hash::check($currentPassword, $user->password_hash)) {
            throw new AuthException('La contraseña actual es incorrecta', self::ERROR_CURRENT_PASSWORD_INVALID);
        }

        if (strlen($newPassword) < self::MIN_PASSWORD_LENGTH) {
            throw new AuthException(
                'La nueva contraseña debe tener al menos ' . self::MIN_PASSWORD_LENGTH . ' caracteres',
                422
            );
        }

        $user->password_hash = Hash::make($newPassword);
        $user->save();
    }
}

class AuthException extends \Exception
{
    protected int $errorCode;

    public function __construct(string $message, int $errorCode)
    {
        parent::__construct($message, 0);
        $this->errorCode = $errorCode;
    }

    public function getErrorCode(): int
    {
        return $this->errorCode;
    }
}
