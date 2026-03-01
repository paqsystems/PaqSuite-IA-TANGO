<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

/**
 * CRUD atributos de rol (permisos granulares por opción de menú).
 *
 * @see docs/04-tareas/001-Seguridad/TR-014-administracion-atributos-rol.md
 */
class RolAtributoController extends Controller
{
    public function index(int $idRol): JsonResponse
    {
        if (!Schema::hasTable('pq_rol') || !Schema::hasTable('pq_menus')) {
            return response()->json(['error' => 404, 'respuesta' => 'Tablas no disponibles', 'resultado' => (object) []], 404);
        }

        $rol = DB::table('pq_rol')->where('id', $idRol)->first();
        if (!$rol) {
            return response()->json(['error' => 404, 'respuesta' => 'Rol no encontrado', 'resultado' => (object) []], 404);
        }

        $opciones = DB::table('pq_menus')
            ->where('enabled', true)
            ->whereNotNull('procedimiento')
            ->where('procedimiento', '!=', '')
            ->orderBy('idparent')
            ->orderBy('orden')
            ->get(['id', 'text', 'procedimiento']);

        $atributos = [];
        if (Schema::hasTable('pq_rol_atributo')) {
            $atributos = DB::table('pq_rol_atributo')
                ->where('id_rol', $idRol)
                ->get()
                ->keyBy('id_opcion_menu');
        }

        $items = $opciones->map(function ($op) use ($atributos) {
            $atr = $atributos->get($op->id);
            return [
                'idOpcionMenu' => $op->id,
                'text' => $op->text,
                'procedimiento' => $op->procedimiento,
                'permisoAlta' => $atr ? (bool) $atr->permiso_alta : false,
                'permisoBaja' => $atr ? (bool) $atr->permiso_baja : false,
                'permisoModi' => $atr ? (bool) $atr->permiso_modi : false,
                'permisoRepo' => $atr ? (bool) $atr->permiso_repo : false,
            ];
        });

        return response()->json([
            'error' => 0,
            'respuesta' => 'OK',
            'resultado' => [
                'rol' => [
                    'id' => $rol->id,
                    'nombreRol' => $rol->nombre_rol,
                    'accesoTotal' => (bool) $rol->acceso_total,
                ],
                'items' => $items->values()->all(),
            ],
        ]);
    }

    public function update(Request $request, int $idRol): JsonResponse
    {
        if (!Schema::hasTable('pq_rol_atributo') || !Schema::hasTable('pq_menus')) {
            return response()->json(['error' => 500, 'respuesta' => 'Tablas no disponibles', 'resultado' => (object) []], 500);
        }

        $rol = DB::table('pq_rol')->where('id', $idRol)->first();
        if (!$rol) {
            return response()->json(['error' => 404, 'respuesta' => 'Rol no encontrado', 'resultado' => (object) []], 404);
        }

        if ($rol->acceso_total) {
            return response()->json([
                'error' => 422,
                'respuesta' => 'Los roles con acceso total no requieren atributos granulares',
                'resultado' => (object) [],
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'items' => ['required', 'array'],
            'items.*.idOpcionMenu' => ['required', 'integer', 'exists:pq_menus,id'],
            'items.*.permisoAlta' => ['boolean'],
            'items.*.permisoBaja' => ['boolean'],
            'items.*.permisoModi' => ['boolean'],
            'items.*.permisoRepo' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 422,
                'respuesta' => 'Errores de validación',
                'resultado' => ['errors' => $validator->errors()->toArray()],
            ], 422);
        }

        $items = $validator->validated()['items'];

        DB::transaction(function () use ($idRol, $items) {
            DB::table('pq_rol_atributo')->where('id_rol', $idRol)->delete();

            foreach ($items as $item) {
                $alta = $item['permisoAlta'] ?? false;
                $baja = $item['permisoBaja'] ?? false;
                $modi = $item['permisoModi'] ?? false;
                $repo = $item['permisoRepo'] ?? false;

                if ($alta || $baja || $modi || $repo) {
                    DB::table('pq_rol_atributo')->insert([
                        'id_rol' => $idRol,
                        'id_opcion_menu' => $item['idOpcionMenu'],
                        'permiso_alta' => $alta,
                        'permiso_baja' => $baja,
                        'permiso_modi' => $modi,
                        'permiso_repo' => $repo,
                        'created_at' => DB::raw('GETDATE()'),
                        'updated_at' => DB::raw('GETDATE()'),
                    ]);
                }
            }
        });

        return response()->json([
            'error' => 0,
            'respuesta' => 'Atributos actualizados',
            'resultado' => (object) [],
        ]);
    }
}
