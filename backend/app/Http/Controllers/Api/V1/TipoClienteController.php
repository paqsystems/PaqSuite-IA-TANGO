<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TipoCliente;
use App\Services\TipoClienteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Controller: TipoClienteController
 *
 * ABM de tipos de cliente. TR-014(MH), TR-015(MH), TR-016(MH), TR-017(MH).
 *
 * - GET /api/v1/tipos-cliente: listado (con page => paginado; sin page => todos activos para selector)
 * - POST /api/v1/tipos-cliente: crear
 * - GET /api/v1/tipos-cliente/{id}: detalle para edición
 * - PUT /api/v1/tipos-cliente/{id}: actualizar
 * - DELETE /api/v1/tipos-cliente/{id}: eliminar (422 si tiene clientes)
 */
class TipoClienteController extends Controller
{
    public const ERROR_FORBIDDEN = 3101;

    public function __construct(
        private TipoClienteService $tipoClienteService
    ) {
    }

    /**
     * GET /api/v1/tipos-cliente
     * Sin query page: lista simple para selector (activos, no inhabilitados).
     * Con page, page_size, search, activo, inhabilitado: listado paginado ABM.
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
            $tipos = TipoCliente::where('activo', true)
                ->where('inhabilitado', false)
                ->orderBy('descripcion')
                ->get(['id', 'code', 'descripcion', 'activo', 'inhabilitado']);
            $resultado = $tipos->map(fn ($t) => [
                'id' => $t->id,
                'code' => $t->code,
                'descripcion' => $t->descripcion,
                'activo' => $t->activo,
                'inhabilitado' => $t->inhabilitado,
            ])->values()->all();
            return response()->json([
                'error' => 0,
                'respuesta' => 'Tipos de cliente obtenidos correctamente',
                'resultado' => $resultado,
            ], 200);
        }

        $page = max(1, (int) $request->query('page', 1));
        $pageSize = min(100, max(1, (int) $request->query('page_size', 20)));
        $search = $request->query('search');
        $activo = $request->query('activo');
        $inhabilitado = $request->query('inhabilitado');
        $sort = $request->query('sort', 'descripcion');
        $sortDir = strtolower($request->query('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $result = $this->tipoClienteService->listado($page, $pageSize, $search, $activo, $inhabilitado, $sort, $sortDir);
        return response()->json([
            'error' => 0,
            'respuesta' => 'Tipos de cliente obtenidos correctamente',
            'resultado' => $result,
        ], 200);
    }

    /**
     * GET /api/v1/tipos-cliente/{id}
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
            $tipo = $this->tipoClienteService->getById($id);
            return response()->json([
                'error' => 0,
                'respuesta' => 'Tipo de cliente obtenido correctamente',
                'resultado' => $tipo,
            ], 200);
        } catch (\Exception $e) {
            if ((int) $e->getCode() === TipoClienteService::ERROR_NOT_FOUND) {
                return response()->json([
                    'error' => TipoClienteService::ERROR_NOT_FOUND,
                    'respuesta' => $e->getMessage(),
                    'resultado' => (object) [],
                ], 404);
            }
            throw $e;
        }
    }

    /**
     * POST /api/v1/tipos-cliente
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
            $tipo = $this->tipoClienteService->create($data);
            return response()->json([
                'error' => 0,
                'respuesta' => 'Tipo de cliente creado correctamente',
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
            if ((int) $e->getCode() === TipoClienteService::ERROR_CODE_DUPLICATE) {
                return response()->json([
                    'error' => TipoClienteService::ERROR_CODE_DUPLICATE,
                    'respuesta' => $e->getMessage(),
                    'resultado' => (object) [],
                ], 409);
            }
            throw $e;
        }
    }

    /**
     * PUT /api/v1/tipos-cliente/{id}
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
            $tipo = $this->tipoClienteService->update($id, $data);
            return response()->json([
                'error' => 0,
                'respuesta' => 'Tipo de cliente actualizado correctamente',
                'resultado' => $tipo,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 1000,
                'respuesta' => $e->getMessage(),
                'resultado' => ['errors' => $e->errors()],
            ], 422);
        } catch (\Exception $e) {
            if ((int) $e->getCode() === TipoClienteService::ERROR_NOT_FOUND) {
                return response()->json([
                    'error' => TipoClienteService::ERROR_NOT_FOUND,
                    'respuesta' => $e->getMessage(),
                    'resultado' => (object) [],
                ], 404);
            }
            throw $e;
        }
    }

    /**
     * DELETE /api/v1/tipos-cliente/{id}
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
            $this->tipoClienteService->delete($id);
            return response()->json([
                'error' => 0,
                'respuesta' => 'Tipo de cliente eliminado correctamente',
                'resultado' => (object) [],
            ], 200);
        } catch (\Exception $e) {
            $code = (int) $e->getCode();
            if ($code === TipoClienteService::ERROR_NOT_FOUND) {
                return response()->json([
                    'error' => $code,
                    'respuesta' => $e->getMessage(),
                    'resultado' => (object) [],
                ], 404);
            }
            if ($code === TipoClienteService::ERROR_TIENE_CLIENTES) {
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
