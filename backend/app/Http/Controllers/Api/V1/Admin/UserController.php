<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * CRUD usuarios (solo administradores).
 *
 * @see docs/04-tareas/001-Seguridad/TR-010-administracion-usuarios.md
 */
class UserController extends Controller
{
    private const MIN_PASSWORD_LENGTH = 8;

    /**
     * GET /api/v1/admin/users
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        if ($request->filled('codigo')) {
            $query->where('codigo', 'like', '%' . $request->codigo . '%');
        }
        if ($request->filled('nombre')) {
            $query->where('name_user', 'like', '%' . $request->nombre . '%');
        }
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }
        if ($request->has('activo')) {
            $query->where('activo', filter_var($request->activo, FILTER_VALIDATE_BOOLEAN));
        }
        if ($request->has('inhabilitado')) {
            $query->where('inhabilitado', filter_var($request->inhabilitado, FILTER_VALIDATE_BOOLEAN));
        }

        $sort = $request->get('sort', 'codigo');
        $sortDir = $request->get('sort_dir', 'asc');
        if (in_array($sort, ['codigo', 'name_user', 'email', 'activo', 'inhabilitado', 'created_at'])) {
            $query->orderBy($sort, $sortDir === 'desc' ? 'desc' : 'asc');
        }

        $page = max(1, (int) $request->get('page', 1));
        $pageSize = min(100, max(1, (int) $request->get('page_size', 20)));
        $total = $query->count();
        $items = $query->skip(($page - 1) * $pageSize)->take($pageSize)->get();

        $data = $items->map(fn (User $u) => [
            'id' => $u->id,
            'codigo' => $u->codigo,
            'name' => $u->name_user,
            'email' => $u->email,
            'activo' => $u->activo,
            'inhabilitado' => $u->inhabilitado,
            'created_at' => $u->created_at?->toIso8601String(),
        ]);

        return response()->json([
            'error' => 0,
            'respuesta' => 'OK',
            'resultado' => [
                'items' => $data,
                'page' => $page,
                'page_size' => $pageSize,
                'total' => $total,
                'total_pages' => (int) ceil($total / $pageSize),
            ],
        ]);
    }

    /**
     * GET /api/v1/admin/users/{id}
     */
    public function show(int $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'error' => 404,
                'respuesta' => 'Usuario no encontrado',
                'resultado' => (object) [],
            ], 404);
        }

        return response()->json([
            'error' => 0,
            'respuesta' => 'OK',
            'resultado' => [
                'id' => $user->id,
                'codigo' => $user->codigo,
                'name' => $user->name_user,
                'email' => $user->email,
                'activo' => $user->activo,
                'inhabilitado' => $user->inhabilitado,
                'created_at' => $user->created_at?->toIso8601String(),
                'updated_at' => $user->updated_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * POST /api/v1/admin/users
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'codigo' => ['required', 'string', 'max:50', 'unique:USERS,codigo'],
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:USERS,email'],
            'password' => ['required', 'string', 'min:' . self::MIN_PASSWORD_LENGTH],
            'activo' => ['boolean'],
            'inhabilitado' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 422,
                'respuesta' => 'Errores de validación',
                'resultado' => ['errors' => $validator->errors()->toArray()],
            ], 422);
        }

        $data = $validator->validated();
        $user = User::create([
            'codigo' => $data['codigo'],
            'name_user' => $data['nombre'],
            'email' => $data['email'] ?? null,
            'password_hash' => Hash::make($data['password']),
            'activo' => $data['activo'] ?? true,
            'inhabilitado' => $data['inhabilitado'] ?? false,
        ]);

        return response()->json([
            'error' => 0,
            'respuesta' => 'Usuario creado',
            'resultado' => [
                'id' => $user->id,
                'codigo' => $user->codigo,
                'name' => $user->name_user,
                'email' => $user->email,
                'activo' => $user->activo,
                'inhabilitado' => $user->inhabilitado,
            ],
        ], 201);
    }

    /**
     * PUT /api/v1/admin/users/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'error' => 404,
                'respuesta' => 'Usuario no encontrado',
                'resultado' => (object) [],
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('USERS', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:' . self::MIN_PASSWORD_LENGTH],
            'activo' => ['boolean'],
            'inhabilitado' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 422,
                'respuesta' => 'Errores de validación',
                'resultado' => ['errors' => $validator->errors()->toArray()],
            ], 422);
        }

        $data = $validator->validated();
        $user->name_user = $data['nombre'];
        $user->email = $data['email'] ?? null;
        $user->activo = $data['activo'] ?? $user->activo;
        $user->inhabilitado = $data['inhabilitado'] ?? $user->inhabilitado;
        if (!empty($data['password'])) {
            $user->password_hash = Hash::make($data['password']);
        }
        $user->save();

        return response()->json([
            'error' => 0,
            'respuesta' => 'Usuario actualizado',
            'resultado' => [
                'id' => $user->id,
                'codigo' => $user->codigo,
                'name' => $user->name_user,
                'email' => $user->email,
                'activo' => $user->activo,
                'inhabilitado' => $user->inhabilitado,
            ],
        ]);
    }

    /**
     * PUT /api/v1/admin/users/{id}/inhabilitar - Soft delete
     */
    public function inhabilitar(int $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'error' => 404,
                'respuesta' => 'Usuario no encontrado',
                'resultado' => (object) [],
            ], 404);
        }

        $user->inhabilitado = true;
        $user->save();

        return response()->json([
            'error' => 0,
            'respuesta' => 'Usuario inhabilitado',
            'resultado' => (object) [],
        ]);
    }
}
