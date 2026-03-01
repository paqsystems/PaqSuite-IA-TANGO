<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

/**
 * CRUD roles (solo administradores).
 * @see docs/04-tareas/001-Seguridad/TR-012-administracion-roles.md
 */
class RolController extends Controller
{
    private function table(): string
    {
        return 'pq_rol';
    }

    public function index(Request $request): JsonResponse
    {
        if (!Schema::hasTable($this->table())) {
            return response()->json(['error' => 0, 'respuesta' => 'OK', 'resultado' => ['items' => []]]);
        }
        $items = DB::table($this->table())->orderBy('nombre_rol')->get();
        $data = $items->map(fn ($row) => [
            'id' => $row->id,
            'nombreRol' => $row->nombre_rol,
            'descripcionRol' => $row->descripcion_rol,
            'accesoTotal' => (bool) $row->acceso_total,
        ]);
        return response()->json(['error' => 0, 'respuesta' => 'OK', 'resultado' => ['items' => $data]]);
    }

    public function show(int $id): JsonResponse
    {
        if (!Schema::hasTable($this->table())) {
            return response()->json(['error' => 404, 'respuesta' => 'Rol no encontrado', 'resultado' => (object) []], 404);
        }
        $row = DB::table($this->table())->where('id', $id)->first();
        if (!$row) {
            return response()->json(['error' => 404, 'respuesta' => 'Rol no encontrado', 'resultado' => (object) []], 404);
        }
        return response()->json([
            'error' => 0,
            'respuesta' => 'OK',
            'resultado' => [
                'id' => $row->id,
                'nombreRol' => $row->nombre_rol,
                'descripcionRol' => $row->descripcion_rol,
                'accesoTotal' => (bool) $row->acceso_total,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if (!Schema::hasTable($this->table())) {
            return response()->json(['error' => 500, 'respuesta' => 'Tabla no disponible', 'resultado' => (object) []], 500);
        }
        $validator = Validator::make($request->all(), [
            'nombreRol' => ['required', 'string', 'max:100'],
            'descripcionRol' => ['nullable', 'string', 'max:100'],
            'accesoTotal' => ['boolean'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => 422,
                'respuesta' => 'Errores de validación',
                'resultado' => ['errors' => $validator->errors()->toArray()],
            ], 422);
        }
        $data = $validator->validated();
        $id = DB::table($this->table())->insertGetId([
            'nombre_rol' => $data['nombreRol'],
            'descripcion_rol' => $data['descripcionRol'] ?? null,
            'acceso_total' => $data['accesoTotal'] ?? false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $row = DB::table($this->table())->where('id', $id)->first();
        return response()->json([
            'error' => 0,
            'respuesta' => 'Rol creado',
            'resultado' => [
                'id' => $row->id,
                'nombreRol' => $row->nombre_rol,
                'descripcionRol' => $row->descripcion_rol,
                'accesoTotal' => (bool) $row->acceso_total,
            ],
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        if (!Schema::hasTable($this->table())) {
            return response()->json(['error' => 500, 'respuesta' => 'Tabla no disponible', 'resultado' => (object) []], 500);
        }
        $exists = DB::table($this->table())->where('id', $id)->exists();
        if (!$exists) {
            return response()->json(['error' => 404, 'respuesta' => 'Rol no encontrado', 'resultado' => (object) []], 404);
        }
        $validator = Validator::make($request->all(), [
            'nombreRol' => ['required', 'string', 'max:100'],
            'descripcionRol' => ['nullable', 'string', 'max:100'],
            'accesoTotal' => ['boolean'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => 422,
                'respuesta' => 'Errores de validación',
                'resultado' => ['errors' => $validator->errors()->toArray()],
            ], 422);
        }
        $data = $validator->validated();
        DB::table($this->table())->where('id', $id)->update([
            'nombre_rol' => $data['nombreRol'],
            'descripcion_rol' => $data['descripcionRol'] ?? null,
            'acceso_total' => $data['accesoTotal'] ?? false,
            'updated_at' => now(),
        ]);
        $row = DB::table($this->table())->where('id', $id)->first();
        return response()->json([
            'error' => 0,
            'respuesta' => 'Rol actualizado',
            'resultado' => [
                'id' => $row->id,
                'nombreRol' => $row->nombre_rol,
                'descripcionRol' => $row->descripcion_rol,
                'accesoTotal' => (bool) $row->acceso_total,
            ],
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        if (!Schema::hasTable($this->table())) {
            return response()->json(['error' => 500, 'respuesta' => 'Tabla no disponible', 'resultado' => (object) []], 500);
        }
        $exists = DB::table($this->table())->where('id', $id)->exists();
        if (!$exists) {
            return response()->json(['error' => 404, 'respuesta' => 'Rol no encontrado', 'resultado' => (object) []], 404);
        }
        if (Schema::hasTable('pq_permiso') && DB::table('pq_permiso')->where('id_rol', $id)->exists()) {
            return response()->json([
                'error' => 422,
                'respuesta' => 'No se puede eliminar: el rol tiene permisos asignados',
                'resultado' => (object) [],
            ], 422);
        }
        DB::table($this->table())->where('id', $id)->delete();
        return response()->json(['error' => 0, 'respuesta' => 'Rol eliminado', 'resultado' => (object) []]);
    }
}
