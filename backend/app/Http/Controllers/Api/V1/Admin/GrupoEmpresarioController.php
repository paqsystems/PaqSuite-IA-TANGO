<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\PqGrupoEmpresario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

/**
 * Controlador: Grupos empresarios (Admin)
 *
 * @see docs/04-tareas/002-GruposEmpresarios/TR-001-listado-grupos-empresarios.md
 * @see docs/04-tareas/002-GruposEmpresarios/TR-002-creacion-grupo-empresario.md
 */
class GrupoEmpresarioController extends Controller
{
    public function index(): JsonResponse
    {
        if (!Schema::hasTable('pq_grupo_empresario')) {
            return response()->json([
                'error' => 0,
                'respuesta' => 'OK',
                'resultado' => ['items' => [], 'page' => 1, 'page_size' => 20, 'total' => 0, 'total_pages' => 0],
            ]);
        }

        $items = PqGrupoEmpresario::query()
            ->withCount('empresas')
            ->orderBy('descripcion')
            ->get()
            ->map(fn ($row) => [
                'id' => $row->id,
                'descripcion' => $row->descripcion,
                'cantidadEmpresas' => $row->empresas_count,
            ]);

        return response()->json([
            'error' => 0,
            'respuesta' => 'OK',
            'resultado' => [
                'items' => $items,
                'page' => 1,
                'page_size' => (int) $items->count(),
                'total' => $items->count(),
                'total_pages' => 1,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if (!Schema::hasTable('pq_grupo_empresario') || !Schema::hasTable('pq_grupo_empresario_empresas')) {
            return response()->json(['error' => 500, 'respuesta' => 'Tablas no disponibles', 'resultado' => (object) []], 500);
        }

        $empresaIds = $request->input('empresaIds', []);
        if (!is_array($empresaIds)) {
            $empresaIds = [];
        }
        $empresaIds = array_values(array_unique(array_map('intval', $empresaIds)));

        $empresaIdCol = Schema::hasColumn('pq_empresa', 'IDEmpresa') ? 'IDEmpresa' : 'id';
        $validator = Validator::make(array_merge($request->all(), ['empresaIds' => $empresaIds]), [
            'descripcion' => ['required', 'string', 'max:100'],
            'empresaIds' => ['required', 'array', 'min:1'],
            'empresaIds.*' => ['integer', "exists:pq_empresa,{$empresaIdCol}"],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 422,
                'respuesta' => 'Errores de validación',
                'resultado' => ['errors' => $validator->errors()->toArray()],
            ], 422);
        }

        $descripcion = $validator->validated()['descripcion'];
        $empresaIds = $validator->validated()['empresaIds'];

        $id = DB::transaction(function () use ($descripcion, $empresaIds) {
            $id = DB::table('pq_grupo_empresario')->insertGetId([
                'descripcion' => $descripcion,
                'created_at' => DB::raw('GETDATE()'),
                'updated_at' => DB::raw('GETDATE()'),
            ]);

            foreach ($empresaIds as $idEmpresa) {
                DB::table('pq_grupo_empresario_empresas')->insert([
                    'id_grupo' => $id,
                    'id_empresa' => $idEmpresa,
                ]);
            }

            return $id;
        });

        $grupo = PqGrupoEmpresario::withCount('empresas')->find($id);

        return response()->json([
            'error' => 0,
            'respuesta' => 'Grupo empresario creado',
            'resultado' => [
                'id' => $grupo->id,
                'descripcion' => $grupo->descripcion,
                'cantidadEmpresas' => $grupo->empresas_count,
            ],
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        if (!Schema::hasTable('pq_grupo_empresario')) {
            return response()->json(['error' => 404, 'respuesta' => 'Grupo no encontrado', 'resultado' => (object) []], 404);
        }

        $empresaIdCol = Schema::hasColumn('pq_empresa', 'IDEmpresa') ? 'IDEmpresa' : 'id';
        $empresaNombreCol = Schema::hasColumn('pq_empresa', 'NombreEmpresa') ? 'NombreEmpresa' : 'nombre_empresa';
        $grupo = PqGrupoEmpresario::with("empresas:{$empresaIdCol},{$empresaNombreCol}")->find($id);
        if (!$grupo) {
            return response()->json(['error' => 404, 'respuesta' => 'Grupo no encontrado', 'resultado' => (object) []], 404);
        }

        $empresas = $grupo->empresas->map(fn ($e) => [
            'id' => $e->getKey(),
            'nombreEmpresa' => $e->NombreEmpresa ?? $e->nombre_empresa ?? '',
        ]);

        return response()->json([
            'error' => 0,
            'respuesta' => 'OK',
            'resultado' => [
                'id' => $grupo->id,
                'descripcion' => $grupo->descripcion,
                'empresaIds' => $empresas->pluck('id')->values()->all(),
                'empresas' => $empresas->all(),
            ],
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        if (!Schema::hasTable('pq_grupo_empresario') || !Schema::hasTable('pq_grupo_empresario_empresas')) {
            return response()->json(['error' => 500, 'respuesta' => 'Tablas no disponibles', 'resultado' => (object) []], 500);
        }

        $grupo = PqGrupoEmpresario::find($id);
        if (!$grupo) {
            return response()->json(['error' => 404, 'respuesta' => 'Grupo no encontrado', 'resultado' => (object) []], 404);
        }

        $empresaIds = $request->input('empresaIds', []);
        if (!is_array($empresaIds)) {
            $empresaIds = [];
        }
        $empresaIds = array_values(array_unique(array_map('intval', $empresaIds)));

        $empresaIdCol = Schema::hasColumn('pq_empresa', 'IDEmpresa') ? 'IDEmpresa' : 'id';
        $validator = Validator::make(array_merge($request->all(), ['empresaIds' => $empresaIds]), [
            'descripcion' => ['required', 'string', 'max:100'],
            'empresaIds' => ['required', 'array', 'min:1'],
            'empresaIds.*' => ['integer', "exists:pq_empresa,{$empresaIdCol}"],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 422,
                'respuesta' => 'Errores de validación',
                'resultado' => ['errors' => $validator->errors()->toArray()],
            ], 422);
        }

        $descripcion = $validator->validated()['descripcion'];
        $empresaIds = $validator->validated()['empresaIds'];

        DB::transaction(function () use ($grupo, $descripcion, $empresaIds) {
            $grupo->update(['descripcion' => $descripcion, 'updated_at' => DB::raw('GETDATE()')]);
            DB::table('pq_grupo_empresario_empresas')->where('id_grupo', $grupo->id)->delete();
            foreach ($empresaIds as $idEmpresa) {
                DB::table('pq_grupo_empresario_empresas')->insert([
                    'id_grupo' => $grupo->id,
                    'id_empresa' => $idEmpresa,
                ]);
            }
        });

        $grupo->refresh();
        $grupo->loadCount('empresas');

        return response()->json([
            'error' => 0,
            'respuesta' => 'Grupo empresario actualizado',
            'resultado' => [
                'id' => $grupo->id,
                'descripcion' => $grupo->descripcion,
                'cantidadEmpresas' => $grupo->empresas_count,
            ],
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        if (!Schema::hasTable('pq_grupo_empresario')) {
            return response()->json(['error' => 404, 'respuesta' => 'Grupo no encontrado', 'resultado' => (object) []], 404);
        }

        $grupo = PqGrupoEmpresario::find($id);
        if (!$grupo) {
            return response()->json(['error' => 404, 'respuesta' => 'Grupo no encontrado', 'resultado' => (object) []], 404);
        }

        if ($this->tieneDependencias($id)) {
            return response()->json([
                'error' => 409,
                'respuesta' => 'El grupo tiene dependencias y no puede eliminarse',
                'resultado' => ['mensaje' => 'El grupo está referenciado en parámetros u otros módulos.'],
            ], 409);
        }

        DB::transaction(function () use ($grupo) {
            DB::table('pq_grupo_empresario_empresas')->where('id_grupo', $grupo->id)->delete();
            $grupo->delete();
        });

        return response()->json(null, 204);
    }

    private function tieneDependencias(int $idGrupo): bool
    {
        return false;
    }
}
