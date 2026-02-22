<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TipoTarea;
use App\Services\TipoTareaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Controller: TipoTareaController
 *
 * ABM de tipos de tarea. TR-023(MH), TR-024(MH), TR-025(MH), TR-026(MH), TR-027(SH).
 *
 * - GET /api/v1/tipos-tarea: listado (con page => paginado; sin page => todos para selector)
 * - POST /api/v1/tipos-tarea: crear
 * - GET /api/v1/tipos-tarea/{id}: detalle (con ?clientes=1 para TR-027)
 * - PUT /api/v1/tipos-tarea/{id}: actualizar
 * - DELETE /api/v1/tipos-tarea/{id}: eliminar (422 si en uso, código 2114)
 */
class TipoTareaController extends Controller
{
    public const ERROR_FORBIDDEN = 3101;

    public function __construct(
        private TipoTareaService $tipoTareaService
    ) {
    }

    /**
     * GET /api/v1/tipos-tarea
     * Sin query page: lista simple para selector (todos).
     * Con page, page_size, search, is_generico, is_default, activo, inhabilitado: listado paginado ABM.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isSupervisor()) {
            return response()->json([
                'error' => self::ERROR_FORBIDDEN,
                'respuesta' => 'No tiene permiso para acceder a esta funcionalidad',
                'resultado' => (object) [],
            ], 403);
        }

        if (!$request->has('page')) {
            $tipos = TipoTarea::orderBy('descripcion')
                ->get(['id', 'code', 'descripcion', 'is_generico', 'is_default', 'activo', 'inhabilitado']);
            $resultado = $tipos->map(fn ($t) => [
                'id' => $t->id,
                'code' => $t->code,
                'descripcion' => $t->descripcion,
                'is_generico' => $t->is_generico,
                'is_default' => $t->is_default,
                'activo' => $t->activo,
                'inhabilitado' => $t->inhabilitado,
            ])->values()->all();
            return response()->json([
                'error' => 0,
                'respuesta' => 'Tipos de tarea obtenidos correctamente',
                'resultado' => $resultado,
            ], 200);
        }

        $page = max(1, (int) $request->query('page', 1));
        $pageSize = min(100, max(1, (int) $request->query('page_size', 20)));
        $search = $request->query('search');
        $isGenerico = $request->query('is_generico');
        $isDefault = $request->query('is_default');
        $activo = $request->query('activo');
        $inhabilitado = $request->query('inhabilitado');
        $sort = $request->query('sort', 'descripcion');
        $sortDir = strtolower($request->query('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $result = $this->tipoTareaService->listado(
            $page,
            $pageSize,
            $search,
            $isGenerico,
            $isDefault,
            $activo,
            $inhabilitado,
            $sort,
            $sortDir
        );
        return response()->json([
            'error' => 0,
            'respuesta' => 'Tipos de tarea obtenidos correctamente',
            'resultado' => $result,
        ], 200);
    }

    /**
     * GET /api/v1/tipos-tarea/{id}
     * Query clientes=1: incluir clientes asociados (para detalle TR-027).
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isSupervisor()) {
            return response()->json([
                'error' => self::ERROR_FORBIDDEN,
                'respuesta' => 'No tiene permiso para acceder a esta funcionalidad',
                'resultado' => (object) [],
            ], 403);
        }
        try {
            $conClientes = $request->query('clientes') === '1' || $request->query('clientes') === 'true';
            $tipo = $conClientes
                ? $this->tipoTareaService->getByIdConClientes($id)
                : $this->tipoTareaService->getById($id);
            return response()->json([
                'error' => 0,
                'respuesta' => 'Tipo de tarea obtenido correctamente',
                'resultado' => $tipo,
            ], 200);
        } catch (\Exception $e) {
            if ((int) $e->getCode() === TipoTareaService::ERROR_NOT_FOUND) {
                return response()->json([
                    'error' => TipoTareaService::ERROR_NOT_FOUND,
                    'respuesta' => $e->getMessage(),
                    'resultado' => (object) [],
                ], 404);
            }
            throw $e;
        }
    }

    /**
     * POST /api/v1/tipos-tarea
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isSupervisor()) {
            return response()->json([
                'error' => self::ERROR_FORBIDDEN,
                'respuesta' => 'No tiene permiso para acceder a esta funcionalidad',
                'resultado' => (object) [],
            ], 403);
        }
        try {
            $data = $request->validate([
                'code' => 'required|string|max:50',
                'descripcion' => 'required|string|max:255',
                'is_generico' => 'boolean',
                'is_default' => 'boolean',
                'activo' => 'boolean',
                'inhabilitado' => 'boolean',
            ]);
        } catch (ValidationException $e) {
            $first = $e->errors();
            $message = is_array($first) ? (reset($first)[0] ?? 'Error de validación') : 'Error de validación';
            return response()->json([
                'error' => 1000,
                'respuesta' => $message,
                'resultado' => ['errors' => $e->errors()],
            ], 422);
        }
        try {
            $tipo = $this->tipoTareaService->create($data);
            return response()->json([
                'error' => 0,
                'respuesta' => 'Tipo de tarea creado correctamente',
                'resultado' => $tipo,
            ], 201);
        } catch (ValidationException $e) {
            $first = $e->errors();
            $message = is_array($first) ? (reset($first)[0] ?? 'Error de validación') : 'Error de validación';
            return response()->json([
                'error' => 1000,
                'respuesta' => $message,
                'resultado' => ['errors' => $e->errors()],
            ], 422);
        } catch (\Exception $e) {
            $code = (int) $e->getCode();
            if ($code === TipoTareaService::ERROR_CODE_DUPLICATE) {
                return response()->json([
                    'error' => $code,
                    'respuesta' => $e->getMessage(),
                    'resultado' => (object) [],
                ], 409);
            }
            if ($code === TipoTareaService::ERROR_YA_HAY_POR_DEFECTO) {
                return response()->json([
                    'error' => $code,
                    'respuesta' => $e->getMessage(),
                    'resultado' => (object) [],
                ], 422);
            }
            throw $e;
        }
    }

    /**
     * PUT /api/v1/tipos-tarea/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isSupervisor()) {
            return response()->json([
                'error' => self::ERROR_FORBIDDEN,
                'respuesta' => 'No tiene permiso para acceder a esta funcionalidad',
                'resultado' => (object) [],
            ], 403);
        }
        try {
            $data = $request->validate([
                'descripcion' => 'required|string|max:255',
                'is_generico' => 'boolean',
                'is_default' => 'boolean',
                'activo' => 'boolean',
                'inhabilitado' => 'boolean',
            ]);
        } catch (ValidationException $e) {
            $first = $e->errors();
            $message = is_array($first) ? (reset($first)[0] ?? 'Error de validación') : 'Error de validación';
            return response()->json([
                'error' => 1000,
                'respuesta' => $message,
                'resultado' => ['errors' => $e->errors()],
            ], 422);
        }
        try {
            $tipo = $this->tipoTareaService->update($id, $data);
            return response()->json([
                'error' => 0,
                'respuesta' => 'Tipo de tarea actualizado correctamente',
                'resultado' => $tipo,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 1000,
                'respuesta' => $e->getMessage(),
                'resultado' => ['errors' => $e->errors()],
            ], 422);
        } catch (\Exception $e) {
            $code = (int) $e->getCode();
            if ($code === TipoTareaService::ERROR_NOT_FOUND) {
                return response()->json([
                    'error' => $code,
                    'respuesta' => $e->getMessage(),
                    'resultado' => (object) [],
                ], 404);
            }
            if ($code === TipoTareaService::ERROR_YA_HAY_POR_DEFECTO) {
                return response()->json([
                    'error' => $code,
                    'respuesta' => $e->getMessage(),
                    'resultado' => (object) [],
                ], 422);
            }
            throw $e;
        }
    }

    /**
     * DELETE /api/v1/tipos-tarea/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isSupervisor()) {
            return response()->json([
                'error' => self::ERROR_FORBIDDEN,
                'respuesta' => 'No tiene permiso para acceder a esta funcionalidad',
                'resultado' => (object) [],
            ], 403);
        }
        try {
            $this->tipoTareaService->delete($id);
            return response()->json([
                'error' => 0,
                'respuesta' => 'Tipo de tarea eliminado correctamente',
                'resultado' => (object) [],
            ], 200);
        } catch (\Exception $e) {
            $code = (int) $e->getCode();
            if ($code === TipoTareaService::ERROR_NOT_FOUND) {
                return response()->json([
                    'error' => $code,
                    'respuesta' => $e->getMessage(),
                    'resultado' => (object) [],
                ], 404);
            }
            if ($code === TipoTareaService::ERROR_EN_USO) {
                return response()->json([
                    'error' => $code,
                    'respuesta' => $e->getMessage(),
                    'resultado' => (object) [],
                ], 422);
            }
            throw $e;
        }
    }
}
