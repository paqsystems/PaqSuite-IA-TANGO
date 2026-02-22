<?php

namespace App\Services;

use App\Models\Usuario;
use App\Models\User;
use App\Models\RegistroTarea;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Servicio: EmpleadoService
 *
 * Lógica de negocio para empleados. TR-018 (listado), TR-019 (creación), TR-020 (edición), TR-021 (eliminación), TR-022 (detalle).
 *
 * @see TR-018(MH)-listado-de-empleados.md
 * @see TR-019(MH)-creación-de-empleado.md
 * @see TR-020(MH)-edición-de-empleado.md
 * @see TR-021(MH)-eliminación-de-empleado.md
 * @see TR-022(SH)-visualización-de-detalle-de-empleado.md
 */
class EmpleadoService
{
    /** Error 403: solo supervisores */
    public const ERROR_FORBIDDEN = 3101;

    /** Error 404: empleado no encontrado */
    public const ERROR_NOT_FOUND = 4003;

    /** Error conflicto código duplicado (409) */
    public const ERROR_CODE_DUPLICATE = 4101;

    /** Error conflicto email duplicado (409) */
    public const ERROR_EMAIL_DUPLICATE = 4102;

    /** Error 422: empleado con tareas asociadas (no se puede eliminar) */
    public const ERROR_TIENE_TAREAS = 2113;

    /** Whitelist para ordenamiento */
    private const SORT_WHITELIST = ['code', 'nombre', 'email', 'created_at', 'updated_at'];

    /**
     * Listar empleados con búsqueda, filtros y paginación.
     * Solo supervisores (validado en controller).
     *
     * @param array $params page, page_size, search, supervisor, activo, inhabilitado, sort, sort_dir
     * @return array ['items' => [], 'page' => int, 'page_size' => int, 'total' => int, 'total_pages' => int]
     */
    public function list(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $pageSize = min(100, max(1, (int) ($params['page_size'] ?? 20)));
        $search = $params['search'] ?? null;
        $supervisor = $params['supervisor'] ?? null;
        $activo = $params['activo'] ?? null;
        $inhabilitado = $params['inhabilitado'] ?? null;
        $sort = $params['sort'] ?? 'nombre';
        $sortDir = strtolower($params['sort_dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        if (!in_array($sort, self::SORT_WHITELIST, true)) {
            $sort = 'nombre';
        }

        $query = Usuario::query()
            ->when($search !== null && $search !== '', function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('code', 'like', '%' . $search . '%')
                        ->orWhere('nombre', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->when($supervisor !== null && $supervisor !== '', function ($q) use ($supervisor) {
                $q->where('supervisor', filter_var($supervisor, FILTER_VALIDATE_BOOLEAN));
            })
            ->when($activo !== null && $activo !== '', function ($q) use ($activo) {
                $q->where('activo', filter_var($activo, FILTER_VALIDATE_BOOLEAN));
            })
            ->when($inhabilitado !== null && $inhabilitado !== '', function ($q) use ($inhabilitado) {
                $q->where('inhabilitado', filter_var($inhabilitado, FILTER_VALIDATE_BOOLEAN));
            })
            ->orderBy($sort, $sortDir);

        $paginator = $query->paginate($pageSize, ['*'], 'page', $page);

        $items = $paginator->getCollection()->map(function (Usuario $u) {
            return [
                'id' => $u->id,
                'code' => $u->code,
                'nombre' => $u->nombre,
                'email' => $u->email,
                'supervisor' => $u->supervisor,
                'activo' => $u->activo,
                'inhabilitado' => $u->inhabilitado,
                'created_at' => $u->created_at?->toIso8601String(),
                'updated_at' => $u->updated_at?->toIso8601String(),
            ];
        })->values()->all();

        return [
            'items' => $items,
            'page' => $paginator->currentPage(),
            'page_size' => $paginator->perPage(),
            'total' => $paginator->total(),
            'total_pages' => $paginator->lastPage(),
        ];
    }

    /**
     * Crear un nuevo empleado. Solo supervisores (validado en controller).
     * Siempre crea primero User en USERS y luego empleado en PQ_PARTES_USUARIOS con user_id.
     * El code del empleado debe coincidir exactamente con el code del User.
     *
     * @param array $data code, nombre, email?, password, supervisor?, activo?, inhabilitado?
     * @return array Empleado creado (array para respuesta API)
     * @throws \Illuminate\Validation\ValidationException con código en el mensaje o \Exception
     */
    public function create(array $data): array
    {
        $code = trim($data['code'] ?? '');
        $nombre = trim($data['nombre'] ?? '');
        $email = isset($data['email']) ? trim($data['email']) : null;
        $password = $data['password'] ?? null;
        $supervisor = isset($data['supervisor']) ? (bool) $data['supervisor'] : false;
        $activo = isset($data['activo']) ? (bool) $data['activo'] : true;
        $inhabilitado = isset($data['inhabilitado']) ? (bool) $data['inhabilitado'] : false;

        // Validaciones
        if ($code === '') {
            throw ValidationException::withMessages(['code' => ['El código es obligatorio.']]);
        }
        if ($nombre === '') {
            throw ValidationException::withMessages(['nombre' => ['El nombre es obligatorio.']]);
        }
        if ($password === null || $password === '') {
            throw ValidationException::withMessages(['password' => ['La contraseña es obligatoria.']]);
        }
        if (strlen($password) < 8) {
            throw ValidationException::withMessages(['password' => ['La contraseña debe tener al menos 8 caracteres.']]);
        }

        if ($email !== null && $email !== '') {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw ValidationException::withMessages(['email' => ['El formato del email no es válido.']]);
            }
        }

        // Validar código único en USERS
        if (User::where('code', $code)->exists()) {
            throw new \Exception('El código del empleado ya existe', self::ERROR_CODE_DUPLICATE);
        }

        // Validar email único si se proporciona
        if ($email !== null && $email !== '') {
            if (Usuario::where('email', $email)->exists()) {
                throw new \Exception('El email del empleado ya existe', self::ERROR_EMAIL_DUPLICATE);
            }
        }

        return DB::transaction(function () use ($code, $nombre, $email, $password, $supervisor, $activo, $inhabilitado) {
            // Crear primero User en USERS
            $user = new User();
            $user->code = $code;
            $user->password_hash = Hash::make($password);
            $user->activo = $activo;
            $user->inhabilitado = $inhabilitado;
            $user->save();

            // Crear empleado en PQ_PARTES_USUARIOS con user_id y mismo code
            $usuario = new Usuario();
            $usuario->user_id = $user->id;
            $usuario->code = $code; // Debe coincidir exactamente con User.code
            $usuario->nombre = $nombre;
            $usuario->email = $email ?: null;
            $usuario->supervisor = $supervisor;
            $usuario->activo = $activo;
            $usuario->inhabilitado = $inhabilitado;
            $usuario->save();

            return [
                'id' => $usuario->id,
                'code' => $usuario->code,
                'nombre' => $usuario->nombre,
                'email' => $usuario->email,
                'supervisor' => $usuario->supervisor,
                'activo' => $usuario->activo,
                'inhabilitado' => $usuario->inhabilitado,
                'created_at' => $usuario->created_at?->toIso8601String(),
                'updated_at' => $usuario->updated_at?->toIso8601String(),
            ];
        });
    }

    /**
     * Obtener un empleado por ID para edición o detalle. Solo supervisores (validado en controller).
     * TR-020: edición (sin estadísticas). TR-022: detalle (opcionalmente con total_tareas).
     *
     * @param int $id ID del empleado
     * @param bool $includeStats Si true, incluye total_tareas (cantidad de registros en PQ_PARTES_REGISTRO_TAREA)
     * @return array Empleado formateado para API
     * @throws \Exception con código ERROR_NOT_FOUND si no existe
     */
    public function getById(int $id, bool $includeStats = false): array
    {
        $usuario = Usuario::find($id);
        if (!$usuario) {
            throw new \Exception('Empleado no encontrado.', self::ERROR_NOT_FOUND);
        }
        $result = [
            'id' => $usuario->id,
            'code' => $usuario->code,
            'nombre' => $usuario->nombre,
            'email' => $usuario->email,
            'supervisor' => $usuario->supervisor,
            'activo' => $usuario->activo,
            'inhabilitado' => $usuario->inhabilitado,
            'created_at' => $usuario->created_at?->toIso8601String(),
            'updated_at' => $usuario->updated_at?->toIso8601String(),
        ];
        if ($includeStats) {
            $result['total_tareas'] = RegistroTarea::where('usuario_id', $id)->count();
        }
        return $result;
    }

    /**
     * Actualizar un empleado existente. Solo supervisores (validado en controller).
     * El código no es modificable. Sincroniza USERS (password_hash si cambia contraseña; activo/inhabilitado).
     *
     * @param int $id ID del empleado
     * @param array $data nombre, email?, password?, supervisor?, activo?, inhabilitado?
     * @return array Empleado actualizado (array para respuesta API)
     * @throws \Exception ERROR_NOT_FOUND, ERROR_EMAIL_DUPLICATE | ValidationException
     */
    public function update(int $id, array $data): array
    {
        $usuario = Usuario::find($id);
        if (!$usuario) {
            throw new \Exception('Empleado no encontrado.', self::ERROR_NOT_FOUND);
        }

        $nombre = trim($data['nombre'] ?? '');
        $email = isset($data['email']) ? trim($data['email']) : null;
        $password = $data['password'] ?? null;
        $supervisor = isset($data['supervisor']) ? (bool) $data['supervisor'] : $usuario->supervisor;
        $activo = isset($data['activo']) ? (bool) $data['activo'] : $usuario->activo;
        $inhabilitado = isset($data['inhabilitado']) ? (bool) $data['inhabilitado'] : $usuario->inhabilitado;

        // Validaciones
        if ($nombre === '') {
            throw ValidationException::withMessages(['nombre' => ['El nombre es obligatorio.']]);
        }

        if ($email !== null && $email !== '') {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw ValidationException::withMessages(['email' => ['El formato del email no es válido.']]);
            }
            // Validar email único excluyendo el propio empleado
            if (Usuario::where('email', $email)->where('id', '!=', $id)->exists()) {
                throw new \Exception('El email ya está registrado.', self::ERROR_EMAIL_DUPLICATE);
            }
        }

        // Validar contraseña si se proporciona
        if ($password !== null && $password !== '') {
            if (strlen($password) < 8) {
                throw ValidationException::withMessages(['password' => ['La contraseña debe tener al menos 8 caracteres.']]);
            }
        }

        return DB::transaction(function () use ($usuario, $nombre, $email, $password, $supervisor, $activo, $inhabilitado) {
            // Actualizar User en USERS si existe
            if ($usuario->user_id) {
                $user = User::find($usuario->user_id);
                if ($user) {
                    // Actualizar password_hash si se proporciona nueva contraseña
                    if ($password !== null && $password !== '') {
                        $user->password_hash = Hash::make($password);
                    }
                    // Sincronizar activo e inhabilitado
                    $user->activo = $activo;
                    $user->inhabilitado = $inhabilitado;
                    $user->save();
                }
            }

            // Actualizar empleado en PQ_PARTES_USUARIOS
            $usuario->nombre = $nombre;
            $usuario->email = $email ?: null;
            $usuario->supervisor = $supervisor;
            $usuario->activo = $activo;
            $usuario->inhabilitado = $inhabilitado;
            $usuario->save();

            return [
                'id' => $usuario->id,
                'code' => $usuario->code,
                'nombre' => $usuario->nombre,
                'email' => $usuario->email,
                'supervisor' => $usuario->supervisor,
                'activo' => $usuario->activo,
                'inhabilitado' => $usuario->inhabilitado,
                'updated_at' => $usuario->updated_at?->toIso8601String(),
            ];
        });
    }

    /**
     * Eliminar un empleado. Solo supervisores (validado en controller).
     * No se puede eliminar si tiene tareas asociadas (RegistroTarea con usuario_id). Error 2113.
     * También elimina el registro en USERS si existe (user_id).
     *
     * @param int $id ID del empleado
     * @throws \Exception ERROR_NOT_FOUND si no existe, ERROR_TIENE_TAREAS si tiene tareas
     */
    public function delete(int $id): void
    {
        $usuario = Usuario::find($id);
        if (!$usuario) {
            throw new \Exception('Empleado no encontrado.', self::ERROR_NOT_FOUND);
        }

        // Verificar si tiene tareas asociadas
        if (RegistroTarea::where('usuario_id', $id)->exists()) {
            throw new \Exception('No se puede eliminar un empleado que tiene tareas asociadas.', self::ERROR_TIENE_TAREAS);
        }

        DB::transaction(function () use ($usuario) {
            // Guardar user_id antes de eliminar el usuario
            $userId = $usuario->user_id;

            // Eliminar primero el empleado en PQ_PARTES_USUARIOS
            $usuario->delete();

            // Luego eliminar el registro en USERS si existe
            if ($userId) {
                $user = User::find($userId);
                if ($user) {
                    $user->delete();
                }
            }
        });
    }
}
