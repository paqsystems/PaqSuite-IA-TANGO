<?php

namespace App\Services;

use App\Models\User;
use App\Models\Usuario;
use App\Models\Cliente;
use Illuminate\Support\Facades\Hash;

/**
 * Service: AuthService
 * 
 * Servicio de autenticación que maneja la lógica de login y logout.
 * 
 * Flujo de autenticación (login):
 * 1. Buscar usuario en USERS por code
 * 2. Validar que usuario esté activo y no inhabilitado en USERS
 * 3. Validar contraseña con Hash::check()
 * 4. Buscar usuario en PQ_PARTES_USUARIOS por code (empleado)
 * 5. Si no es empleado, buscar en PQ_PARTES_CLIENTES por code (cliente)
 * 6. Validar que esté activo y no inhabilitado en la tabla correspondiente
 * 7. Determinar tipo_usuario y es_supervisor
 * 8. Generar token Sanctum
 * 9. Retornar respuesta con todos los campos requeridos
 * 
 * Flujo de logout:
 * 1. Obtener token actual del usuario
 * 2. Revocar/eliminar el token de la base de datos
 * 
 * Códigos de error:
 * - 3201: Credenciales inválidas (genérico, no revela si usuario existe)
 * - 3202: Usuario no encontrado (interno, no se expone)
 * - 3203: Contraseña incorrecta (interno, no se expone)
 * - 4001: No autenticado (logout sin token válido)
 * - 4203: Usuario inactivo
 * 
 * @see TR-001(MH)-login-de-empleado.md
 * @see TR-002(SH)-login-de-cliente.md
 * @see TR-003(MH)-logout.md
 * @see TR-005(SH)-cambio-de-contraseña-usuario-autenticado.md
 */
class AuthService
{
    /**
     * Códigos de error
     */
    public const ERROR_INVALID_CREDENTIALS = 3201;
    public const ERROR_USER_NOT_FOUND = 3202;
    public const ERROR_WRONG_PASSWORD = 3203;
    public const ERROR_CURRENT_PASSWORD_INVALID = 3204;
    public const ERROR_NOT_AUTHENTICATED = 4001;
    public const ERROR_USER_INACTIVE = 4203;

    /**
     * Longitud mínima de contraseña
     */
    private const MIN_PASSWORD_LENGTH = 8;

    /**
     * Intentar login de usuario (empleado o cliente)
     *
     * @param string $usuario Código de usuario
     * @param string $password Contraseña
     * @return array Respuesta con token y datos del usuario
     * @throws AuthException Si las credenciales son inválidas
     */
    public function login(string $usuario, string $password): array
    {
        // 1. Buscar usuario en USERS por code
        $user = User::where('code', $usuario)->first();
        
        if (!$user) {
            // Usuario no encontrado - retornar error genérico por seguridad
            throw new AuthException(
                'Credenciales inválidas',
                self::ERROR_INVALID_CREDENTIALS
            );
        }

        // 2. Validar que usuario esté activo y no inhabilitado en USERS
        if (!$user->activo || $user->inhabilitado) {
            throw new AuthException(
                'Usuario inactivo',
                self::ERROR_USER_INACTIVE
            );
        }

        // 3. Validar contraseña
        if (!Hash::check($password, $user->password_hash)) {
            // Contraseña incorrecta - retornar error genérico por seguridad
            throw new AuthException(
                'Credenciales inválidas',
                self::ERROR_INVALID_CREDENTIALS
            );
        }

        // 4. Buscar usuario en PQ_PARTES_USUARIOS por code (empleado)
        $empleado = Usuario::where('code', $usuario)->first();
        
        if ($empleado) {
            // Es un empleado - validar estado y retornar datos de empleado
            return $this->loginEmpleado($user, $empleado);
        }

        // 5. Si no es empleado, buscar en PQ_PARTES_CLIENTES por code (cliente)
        $cliente = Cliente::where('code', $usuario)->first();
        
        if ($cliente) {
            // Es un cliente - validar estado y retornar datos de cliente
            return $this->loginCliente($user, $cliente);
        }

        // Usuario existe en USERS pero no tiene perfil en ninguna tabla
        // Retornar error genérico por seguridad
        throw new AuthException(
            'Credenciales inválidas',
            self::ERROR_INVALID_CREDENTIALS
        );
    }

    /**
     * Procesar login de empleado
     *
     * @param User $user Datos de autenticación
     * @param Usuario $empleado Datos del empleado
     * @return array Token y datos del usuario
     * @throws AuthException Si el empleado está inactivo
     */
    private function loginEmpleado(User $user, Usuario $empleado): array
    {
        // Validar que empleado esté activo y no inhabilitado
        if (!$empleado->activo || $empleado->inhabilitado) {
            throw new AuthException(
                'Usuario inactivo',
                self::ERROR_USER_INACTIVE
            );
        }

        // Generar token Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        // Retornar respuesta de empleado
        return [
            'token' => $token,
            'user_data' => [
                'user_id' => $user->id,
                'user_code' => $user->code,
                'tipo_usuario' => 'usuario', // Empleado
                'usuario_id' => $empleado->id,
                'cliente_id' => null, // No es cliente
                'es_supervisor' => (bool) $empleado->supervisor,
                'nombre' => $empleado->nombre,
                'email' => $empleado->email,
            ]
        ];
    }

    /**
     * Procesar login de cliente
     *
     * @param User $user Datos de autenticación
     * @param Cliente $cliente Datos del cliente
     * @return array Token y datos del usuario
     * @throws AuthException Si el cliente está inactivo
     * 
     * @see TR-002(SH)-login-de-cliente.md
     */
    private function loginCliente(User $user, Cliente $cliente): array
    {
        // Validar que cliente esté activo y no inhabilitado
        if (!$cliente->activo || $cliente->inhabilitado) {
            throw new AuthException(
                'Usuario inactivo',
                self::ERROR_USER_INACTIVE
            );
        }

        // Generar token Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        // Retornar respuesta de cliente
        return [
            'token' => $token,
            'user_data' => [
                'user_id' => $user->id,
                'user_code' => $user->code,
                'tipo_usuario' => 'cliente', // Cliente
                'usuario_id' => null, // No es empleado
                'cliente_id' => $cliente->id,
                'es_supervisor' => false, // Clientes nunca son supervisores
                'nombre' => $cliente->nombre,
                'email' => $cliente->email,
            ]
        ];
    }

    /**
     * Cerrar sesión del usuario
     * 
     * Revoca el token actual del usuario autenticado.
     *
     * @param User $user Usuario autenticado
     * @return void
     * @throws AuthException Si no hay token para revocar
     * 
     * @see TR-003(MH)-logout.md
     */
    public function logout(User $user): void
    {
        // Obtener y eliminar el token actual
        $currentToken = $user->currentAccessToken();
        
        if ($currentToken) {
            $currentToken->delete();
        }
    }

    /**
     * Cambiar contraseña del usuario autenticado.
     *
     * Valida la contraseña actual, luego actualiza el password_hash en USERS.
     * La sesión actual se mantiene (no se revoca el token).
     *
     * @param User $user Usuario autenticado
     * @param string $currentPassword Contraseña actual
     * @param string $newPassword Nueva contraseña (ya validada: min 8, confirmación)
     * @return void
     * @throws AuthException Si la contraseña actual es incorrecta
     *
     * @see TR-005(SH)-cambio-de-contraseña-usuario-autenticado.md
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (!Hash::check($currentPassword, $user->password_hash)) {
            throw new AuthException(
                'La contraseña actual es incorrecta',
                self::ERROR_CURRENT_PASSWORD_INVALID
            );
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

/**
 * Exception: AuthException
 * 
 * Excepción personalizada para errores de autenticación.
 */
class AuthException extends \Exception
{
    /**
     * Código de error personalizado
     */
    protected int $errorCode;

    /**
     * Constructor
     *
     * @param string $message Mensaje de error
     * @param int $errorCode Código de error personalizado
     */
    public function __construct(string $message, int $errorCode)
    {
        parent::__construct($message, 0);
        $this->errorCode = $errorCode;
    }

    /**
     * Obtener código de error personalizado
     *
     * @return int
     */
    public function getErrorCode(): int
    {
        return $this->errorCode;
    }
}
