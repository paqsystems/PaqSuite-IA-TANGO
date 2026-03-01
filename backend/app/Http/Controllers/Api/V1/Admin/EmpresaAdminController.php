<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class EmpresaAdminController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (!Schema::hasTable('pq_empresa')) {
            return response()->json([
                'error' => 0,
                'respuesta' => 'OK',
                'resultado' => ['items' => [], 'page' => 1, 'page_size' => 20, 'total' => 0, 'total_pages' => 0],
            ]);
        }
        $query = DB::table('pq_empresa');
        if ($request->filled('nombre')) {
            $query->where('nombre_empresa', 'like', '%' . $request->nombre . '%');
        }
        if ($request->has('habilita')) {
            $val = $request->habilita;
            if ($val === '1' || $val === 'true') {
                $query->where(function ($q) {
                    $q->where('habilita', 1)->orWhereNull('habilita');
                });
            } else {
                $query->where('habilita', 0);
            }
        }
        $sort = $request->get('sort', 'nombre_empresa');
        $sortDir = $request->get('sort_dir', 'asc');
        if (in_array($sort, ['nombre_empresa', 'nombre_bd', 'habilita', 'created_at'])) {
            $query->orderBy($sort, $sortDir === 'desc' ? 'desc' : 'asc');
        }
        $total = $query->count();
        $page = max(1, (int) $request->get('page', 1));
        $pageSize = min(100, max(1, (int) $request->get('page_size', 20)));
        $items = $query->skip(($page - 1) * $pageSize)->take($pageSize)->get();
        $data = $items->map(fn ($row) => [
            'id' => $row->id,
            'nombreEmpresa' => $row->nombre_empresa,
            'nombreBd' => $row->nombre_bd,
            'habilita' => $row->habilita,
            'imagen' => $row->imagen,
            'theme' => $row->theme ?? 'default',
            'created_at' => $row->created_at,
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

    public function show(int $id): JsonResponse
    {
        if (!Schema::hasTable('pq_empresa')) {
            return response()->json(['error' => 404, 'respuesta' => 'Empresa no encontrada', 'resultado' => (object) []], 404);
        }
        $row = DB::table('pq_empresa')->where('id', $id)->first();
        if (!$row) {
            return response()->json(['error' => 404, 'respuesta' => 'Empresa no encontrada', 'resultado' => (object) []], 404);
        }
        return response()->json([
            'error' => 0,
            'respuesta' => 'OK',
            'resultado' => [
                'id' => $row->id,
                'nombreEmpresa' => $row->nombre_empresa,
                'nombreBd' => $row->nombre_bd,
                'habilita' => $row->habilita,
                'imagen' => $row->imagen,
                'theme' => $row->theme ?? 'default',
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if (!Schema::hasTable('pq_empresa')) {
            return response()->json(['error' => 500, 'respuesta' => 'Tabla no disponible', 'resultado' => (object) []], 500);
        }
        $validator = Validator::make($request->all(), [
            'nombreEmpresa' => ['required', 'string', 'max:100'],
            'nombreBd' => ['required', 'string', 'max:100', 'unique:pq_empresa,nombre_bd'],
            'habilita' => ['nullable', 'integer', 'in:0,1'],
            'imagen' => ['nullable', 'string', 'max:100'],
            'theme' => ['nullable', 'string', 'max:100'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => 422,
                'respuesta' => 'Errores de validación',
                'resultado' => ['errors' => $validator->errors()->toArray()],
            ], 422);
        }
        $data = $validator->validated();
        $id = DB::table('pq_empresa')->insertGetId([
            'nombre_empresa' => $data['nombreEmpresa'],
            'nombre_bd' => $data['nombreBd'],
            'habilita' => $data['habilita'] ?? 1,
            'imagen' => $data['imagen'] ?? null,
            'theme' => $data['theme'] ?? 'default',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $row = DB::table('pq_empresa')->where('id', $id)->first();
        return response()->json([
            'error' => 0,
            'respuesta' => 'Empresa creada',
            'resultado' => [
                'id' => $row->id,
                'nombreEmpresa' => $row->nombre_empresa,
                'nombreBd' => $row->nombre_bd,
                'habilita' => $row->habilita,
                'imagen' => $row->imagen,
                'theme' => $row->theme ?? 'default',
            ],
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        if (!Schema::hasTable('pq_empresa')) {
            return response()->json(['error' => 500, 'respuesta' => 'Tabla no disponible', 'resultado' => (object) []], 500);
        }
        $exists = DB::table('pq_empresa')->where('id', $id)->exists();
        if (!$exists) {
            return response()->json(['error' => 404, 'respuesta' => 'Empresa no encontrada', 'resultado' => (object) []], 404);
        }
        $validator = Validator::make($request->all(), [
            'nombreEmpresa' => ['required', 'string', 'max:100'],
            'nombreBd' => ['required', 'string', 'max:100', \Illuminate\Validation\Rule::unique('pq_empresa', 'nombre_bd')->ignore($id)],
            'habilita' => ['nullable', 'integer', 'in:0,1'],
            'imagen' => ['nullable', 'string', 'max:100'],
            'theme' => ['nullable', 'string', 'max:100'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => 422,
                'respuesta' => 'Errores de validación',
                'resultado' => ['errors' => $validator->errors()->toArray()],
            ], 422);
        }
        $data = $validator->validated();
        DB::table('pq_empresa')->where('id', $id)->update([
            'nombre_empresa' => $data['nombreEmpresa'],
            'nombre_bd' => $data['nombreBd'],
            'habilita' => $data['habilita'] ?? 1,
            'imagen' => $data['imagen'] ?? null,
            'theme' => $data['theme'] ?? 'default',
            'updated_at' => now(),
        ]);
        $row = DB::table('pq_empresa')->where('id', $id)->first();
        return response()->json([
            'error' => 0,
            'respuesta' => 'Empresa actualizada',
            'resultado' => [
                'id' => $row->id,
                'nombreEmpresa' => $row->nombre_empresa,
                'nombreBd' => $row->nombre_bd,
                'habilita' => $row->habilita,
                'imagen' => $row->imagen,
                'theme' => $row->theme ?? 'default',
            ],
        ]);
    }
}
