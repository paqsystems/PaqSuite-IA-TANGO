<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PqGridLayout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Controller: GridLayoutController
 *
 * CRUD de layouts persistentes de grillas DevExtreme.
 *
 * @see docs/04-tareas/000-Generalidades/TR-001-layouts-grilla.md
 */
class GridLayoutController extends Controller
{
    /**
     * GET /api/v1/grid-layouts?proceso={proceso}&gridId={gridId}
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'error' => 401,
                'respuesta' => 'No autenticado',
                'resultado' => (object) [],
            ], 401);
        }

        $proceso = $request->query('proceso');
        $gridId = $request->query('gridId', 'default');

        if (!$proceso) {
            return response()->json([
                'error' => 422,
                'respuesta' => 'El parámetro proceso es obligatorio',
                'resultado' => (object) [],
            ], 422);
        }

        $layouts = PqGridLayout::forProcesoGrid($proceso, $gridId)
            ->orderBy('layout_name')
            ->get()
            ->map(fn (PqGridLayout $l) => $this->toResource($l, $user->id));

        return response()->json([
            'error' => 0,
            'respuesta' => 'OK',
            'resultado' => ['items' => $layouts],
        ]);
    }

    /**
     * GET /api/v1/grid-layouts/last-used?proceso={proceso}&gridId={gridId}
     */
    public function lastUsed(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'error' => 401,
                'respuesta' => 'No autenticado',
                'resultado' => (object) [],
            ], 401);
        }

        $proceso = $request->query('proceso');
        $gridId = $request->query('gridId', 'default');

        if (!$proceso) {
            return response()->json([
                'error' => 422,
                'respuesta' => 'El parámetro proceso es obligatorio',
                'resultado' => (object) [],
            ], 422);
        }

        $lastUsed = DB::table('pq_grid_layout_last_used')
            ->where('user_id', $user->id)
            ->where('proceso', $proceso)
            ->where('grid_id', $gridId)
            ->first();

        if (!$lastUsed) {
            return response()->json([
                'error' => 404,
                'respuesta' => 'No hay layout usado recientemente',
                'resultado' => (object) [],
            ], 404);
        }

        $layout = PqGridLayout::find($lastUsed->layout_id);
        if (!$layout) {
            return response()->json([
                'error' => 404,
                'respuesta' => 'Layout no encontrado',
                'resultado' => (object) [],
            ], 404);
        }

        return response()->json([
            'error' => 0,
            'respuesta' => 'OK',
            'resultado' => $this->toResource($layout, $user->id),
        ]);
    }

    /**
     * POST /api/v1/grid-layouts
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'error' => 401,
                'respuesta' => 'No autenticado',
                'resultado' => (object) [],
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'proceso' => ['required', 'string', 'max:150'],
            'gridId' => ['nullable', 'string', 'max:50'],
            'layoutName' => ['required', 'string', 'max:100'],
            'layoutData' => ['nullable', 'array'],
            'isDefault' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 422,
                'respuesta' => 'Errores de validación',
                'resultado' => ['errors' => $validator->errors()->toArray()],
            ], 422);
        }

        $data = $validator->validated();
        $gridId = $data['gridId'] ?? 'default';

        $exists = PqGridLayout::where('user_id', $user->id)
            ->where('proceso', $data['proceso'])
            ->where('grid_id', $gridId)
            ->where('layout_name', $data['layoutName'])
            ->exists();

        if ($exists) {
            return response()->json([
                'error' => 422,
                'respuesta' => 'Ya existe un layout con ese nombre para este proceso y grilla',
                'resultado' => (object) [],
            ], 422);
        }

        $layout = PqGridLayout::create([
            'user_id' => $user->id,
            'proceso' => $data['proceso'],
            'grid_id' => $gridId,
            'layout_name' => $data['layoutName'],
            'layout_data' => $data['layoutData'] ?? null,
            'is_default' => $data['isDefault'] ?? false,
        ]);

        return response()->json([
            'error' => 0,
            'respuesta' => 'Layout creado',
            'resultado' => $this->toResource($layout, $user->id),
        ], 201);
    }

    /**
     * PUT /api/v1/grid-layouts/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'error' => 401,
                'respuesta' => 'No autenticado',
                'resultado' => (object) [],
            ], 401);
        }

        $layout = PqGridLayout::find($id);
        if (!$layout) {
            return response()->json([
                'error' => 404,
                'respuesta' => 'Layout no encontrado',
                'resultado' => (object) [],
            ], 404);
        }

        if ($layout->user_id !== (int) $user->id) {
            return response()->json([
                'error' => 403,
                'respuesta' => 'Solo el creador puede modificar este layout',
                'resultado' => (object) [],
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'layoutName' => ['nullable', 'string', 'max:100'],
            'layoutData' => ['nullable', 'array'],
            'isDefault' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 422,
                'respuesta' => 'Errores de validación',
                'resultado' => ['errors' => $validator->errors()->toArray()],
            ], 422);
        }

        $data = $validator->validated();
        if (array_key_exists('layoutName', $data)) {
            $layout->layout_name = $data['layoutName'];
        }
        if (array_key_exists('layoutData', $data)) {
            $layout->layout_data = $data['layoutData'];
        }
        if (array_key_exists('isDefault', $data)) {
            $layout->is_default = $data['isDefault'];
        }
        $layout->save();

        return response()->json([
            'error' => 0,
            'respuesta' => 'Layout actualizado',
            'resultado' => $this->toResource($layout, $user->id),
        ]);
    }

    /**
     * DELETE /api/v1/grid-layouts/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'error' => 401,
                'respuesta' => 'No autenticado',
                'resultado' => (object) [],
            ], 401);
        }

        $layout = PqGridLayout::find($id);
        if (!$layout) {
            return response()->json([
                'error' => 404,
                'respuesta' => 'Layout no encontrado',
                'resultado' => (object) [],
            ], 404);
        }

        if ($layout->user_id !== (int) $user->id) {
            return response()->json([
                'error' => 403,
                'respuesta' => 'Solo el creador puede eliminar este layout',
                'resultado' => (object) [],
            ], 403);
        }

        $layout->delete();

        return response()->json([
            'error' => 0,
            'respuesta' => 'Layout eliminado',
            'resultado' => (object) [],
        ], 200);
    }

    /**
     * POST /api/v1/grid-layouts/{id}/use
     * Registra que el usuario usó este layout (para last-used).
     */
    public function markAsUsed(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'error' => 401,
                'respuesta' => 'No autenticado',
                'resultado' => (object) [],
            ], 401);
        }

        $layout = PqGridLayout::find($id);
        if (!$layout) {
            return response()->json([
                'error' => 404,
                'respuesta' => 'Layout no encontrado',
                'resultado' => (object) [],
            ], 404);
        }

        DB::table('pq_grid_layout_last_used')->updateOrInsert(
            [
                'user_id' => $user->id,
                'proceso' => $layout->proceso,
                'grid_id' => $layout->grid_id,
            ],
            [
                'layout_id' => $layout->id,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return response()->json([
            'error' => 0,
            'respuesta' => 'OK',
            'resultado' => (object) [],
        ]);
    }

    private function toResource(PqGridLayout $layout, int $currentUserId): array
    {
        return [
            'id' => $layout->id,
            'userId' => $layout->user_id,
            'proceso' => $layout->proceso,
            'gridId' => $layout->grid_id,
            'layoutName' => $layout->layout_name,
            'layoutData' => $layout->layout_data,
            'isDefault' => $layout->is_default,
            'createdAt' => $layout->created_at?->toIso8601String(),
            'updatedAt' => $layout->updated_at?->toIso8601String(),
            'isOwner' => $layout->user_id === $currentUserId,
        ];
    }
}
