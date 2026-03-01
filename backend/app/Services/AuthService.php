<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Service: AuthService
 *
 * Servicio de autenticación. Login por code + password contra tabla USERS.
 * Retorna empresas del usuario (Pq_Permiso) y redirectTo según cantidad.
 *
 * @see docs/04-tareas/001-Seguridad/TR-001-login-usuario.md
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
    public const ERROR_NO_EMPRESAS = 403;

    private const MIN_PASSWORD_LENGTH = 8;

    /**
     * @param string|null $locale Locale a persistir si se proporciona (TR-004)
     */
    public function login(string $usuario, string $password, ?string $locale = null): array
    {
        $user = User::where('codigo', $usuario)->first();

        if (!$user) {
            throw new AuthException('Credenciales inválidas', self::ERROR_INVALID_CREDENTIALS);
        }

        if (!$user->activo || $user->inhabilitado) {
            throw new AuthException('Usuario inactivo', self::ERROR_USER_INACTIVE);
        }

        if (!Hash::check($password, $user->password_hash)) {
            throw new AuthException('Credenciales inválidas', self::ERROR_INVALID_CREDENTIALS);
        }

        $empresas = $this->getEmpresasDelUsuario($user->id);
        if ($empresas->isEmpty()) {
            throw new AuthException('No tiene empresas asignadas. Contacte al administrador.', self::ERROR_NO_EMPRESAS);
        }

        $redirectTo = $empresas->count() === 1 ? 'layout' : 'selector';
        $esAdmin = $this->userHasAccesoTotal($user->id);

        if ($locale && in_array($locale, ['es', 'en'], true)) {
            $user->locale = $locale;
            $user->save();
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'token' => $token,
            'user_data' => [
                'user_id' => $user->id,
                'user_code' => $user->codigo,
                'nombre' => $user->name_user ?? $user->codigo,
                'email' => $user->email,
                'tipo_usuario' => 'usuario',
                'usuario_id' => $user->id,
                'cliente_id' => null,
                'es_supervisor' => $esAdmin,
                'es_admin' => $esAdmin,
                'locale' => $user->locale ?? 'es',
                'menu_abrir_nueva_pestana' => (bool) ($user->menu_abrir_nueva_pestana ?? false),
            ],
            'empresas' => $empresas->toArray(),
            'redirectTo' => $redirectTo,
        ];
    }

    private function userHasAccesoTotal(int $userId): bool
    {
        if (!Schema::hasTable('pq_permiso') || !Schema::hasTable('pq_rol')) {
            return false;
        }
        return DB::table('pq_permiso as p')
            ->join('pq_rol as r', 'p.id_rol', '=', 'r.id')
            ->where('p.id_usuario', $userId)
            ->where('r.acceso_total', true)
            ->exists();
    }

    /**
     * Obtiene las empresas habilitadas a las que el usuario tiene permiso.
     * Público para uso en EmpresaController (TR-002).
     * Soporta PQ_Empresa (IDEmpresa, NombreEmpresa) y pq_empresa (id, nombre_empresa).
     */
    public function getEmpresasDelUsuario(int $userId): \Illuminate\Support\Collection
    {
        $driver = DB::getDriverName();
        $usePqSchema = $driver === 'sqlsrv' && Schema::hasColumn('pq_empresa', 'IDEmpresa');

        if ($usePqSchema) {
            $rows = DB::table('pq_permiso as p')
                ->join('pq_empresa as e', 'p.id_empresa', '=', 'e.IDEmpresa')
                ->where('p.id_usuario', $userId)
                ->where(function ($q) {
                    $q->whereNull('e.Habilita')->orWhere('e.Habilita', 1);
                })
                ->select('e.IDEmpresa as id', 'e.NombreEmpresa as nombre_empresa', 'e.NombreBD as nombre_bd', 'e.theme', 'e.imagen')
                ->distinct()
                ->get();
        } else {
            $rows = DB::table('pq_permiso as p')
                ->join('pq_empresa as e', 'p.id_empresa', '=', 'e.id')
                ->where('p.id_usuario', $userId)
                ->where(function ($q) {
                    $q->whereNull('e.habilita')->orWhere('e.habilita', 1);
                })
                ->select('e.id', 'e.nombre_empresa', 'e.nombre_bd', 'e.theme', 'e.imagen')
                ->distinct()
                ->get();
        }

        return $rows->map(fn ($row) => [
            'id' => $row->id,
            'nombreEmpresa' => $row->nombre_empresa,
            'nombreBd' => $row->nombre_bd,
            'theme' => $row->theme ?? 'default',
            'imagen' => $row->imagen,
        ]);
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
