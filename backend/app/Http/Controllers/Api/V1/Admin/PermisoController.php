<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

/**
 * CRUD permisos (asignaciones usuario-empresa-rol).
 *
 * @see docs/04-tareas/001-Seguridad/TR-013-administracion-permisos.md
 */
class PermisoController extends Controller
{
    private function table(): string
    {
        return 'pq_permiso';
    }

    /**
     * GET /api/v1/admin/permisos
     */
    public function index(Request $request): JsonResponse
    {
        if (!Schema::hasTable($this->table())) {
            return response()->json([
                'error' => 0,
                'respuesta' => 'OK',
                'resultado' => ['items' => []],
            ]);
        }

        $query = DB::table($this->table() . ' as p')
            ->leftJoin('USERS as u', 'p.id_usuario', '=', 'u.id')
            ->leftJoin('pq_empresa as e', 'p.id_empresa', '=', 'e.id')
            ->leftJoin('pq_rol as r', 'p.id_rol', '=', 'r.id')
            ->select('p.id', 'p.id_usuario', 'p.id_empresa', 'p.id_rol', 'u.codigo as usuario_code', 'u.name_user as usuario_name', 'e.nombre_empresa', 'r.nombre_rol');

        if ($request->filled('id_usuario')) {
            $query->where('p.id_usuario', $request->id_usuario);
        }
        if ($request->filled('id_empresa')) {
            $query->where('p.id_empresa', $request->id_empresa);
        }
        if ($request->filled('id_rol')) {
            $query->where('p.id_rol', $request->id_rol);
        }

        $items = $query->orderBy('p.id')->get();
        $data = $items->map(fn ($row) => [
            'id' => $row->id,
            'idUsuario' => $row->id_usuario,
            'idEmpresa' => $row->id_empresa,
            'idRol' => $row->id_rol,
            'usuarioCode' => $row->usuario_code,
            'usuarioName' => $row->usuario_name,
            'nombreEmpresa' => $row->nombre_empresa,
            'nombreRol' => $row->nombre_rol,
        ]);

        return response()->json([
            'error' => 0,
            'respuesta' => 'OK',
            'resultado' => ['items' => $data],
        ]);
    }

    /**
     * POST /api/v1/admin/permisos
     */
    public function store(Request $request): JsonResponse
    {
        if (!Schema::hasTable($this->table())) {
            return response()->json(['error' => 500, 'respuesta' => 'Tabla no disponible', 'resultado' => (object) []], 500);
        }

        $validator = Validator::make($request->all(), [
            'idUsuario' => ['required', 'integer', 'exists:USERS,id'],
            'idEmpresa' => ['required', 'integer', 'exists:pq_empresa,id'],
            'idRol' => ['required', 'integer', 'exists:pq_rol,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 422,
                'respuesta' => 'Errores de validación',
                'resultado' => ['errors' => $validator->errors()->toArray()],
            ], 422);
        }

        $data = $validator->validated();
        $exists = DB::table($this->table())
            ->where('id_usuario', $data['idUsuario'])
            ->where('id_empresa', $data['idEmpresa'])
            ->where('id_rol', $data['idRol'])
            ->exists();

        if ($exists) {
            return response()->json([
                'error' => 422,
                'respuesta' => 'La combinación usuario-empresa-rol ya existe',
                'resultado' => (object) [],
            ], 422);
        }

        $id = DB::table($this->table())->insertGetId([
            'id_usuario' => $data['idUsuario'],
            'id_empresa' => $data['idEmpresa'],
            'id_rol' => $data['idRol'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $row = DB::table($this->table())
            ->leftJoin('USERS as u', 'pq_permiso.id_usuario', '=', 'u.id')
            ->leftJoin('pq_empresa as e', 'pq_permiso.id_empresa', '=', 'e.id')
            ->leftJoin('pq_rol as r', 'pq_permiso.id_rol', '=', 'r.id')
            ->where('pq_permiso.id', $id)
            ->select('pq_permiso.*', 'u.codigo as usuario_code', 'u.name_user as usuario_name', 'e.nombre_empresa', 'r.nombre_rol')
            ->first();

        return response()->json([
            'error' => 0,
            'respuesta' => 'Permiso creado',
            'resultado' => [
                'id' => $row->id,
                'idUsuario' => $row->id_usuario,
                'idEmpresa' => $row->id_empresa,
                'idRol' => $row->id_rol,
                'usuarioCode' => $row->usuario_code,
                'usuarioName' => $row->usuario_name,
                'nombreEmpresa' => $row->nombre_empresa,
                'nombreRol' => $row->nombre_rol,
            ],
        ], 201);
    }

    /**
     * PUT /api/v1/admin/permisos/{id} - Cambiar rol
     */
    public function update(Request $request, int $id): JsonResponse
    {
        if (!Schema::hasTable($this->table())) {
            return response()->json(['error' => 500, 'respuesta' => 'Tabla no disponible', 'resultado' => (object) []], 500);
        }

        $permiso = DB::table($this->table())->where('id', $id)->first();
        if (!$permiso) {
            return response()->json(['error' => 404, 'respuesta' => 'Permiso no encontrado', 'resultado' => (object) []], 404);
        }

        $validator = Validator::make($request->all(), [
            'idRol' => ['required', 'integer', 'exists:pq_rol,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 422,
                'respuesta' => 'Errores de validación',
                'resultado' => ['errors' => $validator->errors()->toArray()],
            ], 422);
        }

        DB::table($this->table())->where('id', $id)->update([
            'id_rol' => $validator->validated()['idRol'],
            'updated_at' => now(),
        ]);

        $row = DB::table($this->table())
            ->leftJoin('USERS as u', 'pq_permiso.id_usuario', '=', 'u.id')
            ->leftJoin('pq_empresa as e', 'pq_permiso.id_empresa', '=', 'e.id')
            ->leftJoin('pq_rol as r', 'pq_permiso.id_rol', '=', 'r.id')
            ->where('pq_permiso.id', $id)
            ->select('pq_permiso.*', 'u.codigo as usuario_code', 'u.name_user as usuario_name', 'e.nombre_empresa', 'r.nombre_rol')
            ->first();

        return response()->json([
            'error' => 0,
            'respuesta' => 'Permiso actualizado',
            'resultado' => [
                'id' => $row->id,
                'idUsuario' => $row->id_usuario,
                'idEmpresa' => $row->id_empresa,
                'idRol' => $row->id_rol,
                'usuarioCode' => $row->usuario_code,
                'usuarioName' => $row->usuario_name,
                'nombreEmpresa' => $row->nombre_empresa,
                'nombreRol' => $row->nombre_rol,
            ],
        ]);
    }

    /**
     * DELETE /api/v1/admin/permisos/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        if (!Schema::hasTable($this->table())) {
            return response()->json(['error' => 500, 'respuesta' => 'Tabla no disponible', 'resultado' => (object) []], 500);
        }

        $deleted = DB::table($this->table())->where('id', $id)->delete();
        if (!$deleted) {
            return response()->json(['error' => 404, 'respuesta' => 'Permiso no encontrado', 'resultado' => (object) []], 404);
        }

        return response()->json([
            'error' => 0,
            'respuesta' => 'Permiso eliminado',
            'resultado' => (object) [],
        ]);
    }
}
