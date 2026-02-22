<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\TipoCliente;
use App\Services\ClienteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Controller: ClienteController
 *
 * Listado, creación, edición y eliminación de clientes (solo supervisores). TR-008(MH), TR-009(MH), TR-010(MH), TR-011(MH).
 *
 * Endpoints:
 * - GET /api/v1/clientes - Listado paginado con búsqueda y filtros
 * - GET /api/v1/clientes/{id} - Detalle de cliente para edición (TR-010)
 * - GET /api/v1/tipos-cliente - Lista de tipos de cliente (para filtro)
 * - POST /api/v1/clientes - Crear cliente (TR-009)
 * - PUT /api/v1/clientes/{id} - Actualizar cliente (TR-010)
 * - DELETE /api/v1/clientes/{id} - Eliminar cliente (TR-011)
 * - GET /api/v1/clientes/{id}/tipos-tarea - Tipos de tarea asignados (TR-012)
 * - PUT /api/v1/clientes/{id}/tipos-tarea - Actualizar asignación tipos (TR-012)
 *
 * @see TR-008(MH)-listado-de-clientes.md
 * @see TR-009(MH)-creación-de-cliente.md
 * @see TR-010(MH)-edición-de-cliente.md
 * @see TR-011(MH)-eliminación-de-cliente.md
 * @see specs/endpoints/clientes-list.md
 * @see specs/endpoints/clientes-create.md
 */
class ClienteController extends Controller
{
    public function __construct(
        private ClienteService $clienteService
    ) {
    }
    /** Código error 403: solo supervisores */
    public const ERROR_FORBIDDEN = 3101;

    /** Whitelist para ordenamiento */
    private const SORT_WHITELIST = ['code', 'nombre', 'created_at', 'updated_at'];

    /**
     * GET /api/v1/clientes
     *
     * Listado paginado de clientes con búsqueda y filtros. Solo supervisores.
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

        $page = max(1, (int) $request->query('page', 1));
        $pageSize = min(100, max(1, (int) $request->query('page_size', 20)));
        $search = $request->query('search');
        $tipoClienteId = $request->query('tipo_cliente_id');
        $activo = $request->query('activo');
        $inhabilitado = $request->query('inhabilitado');
        $sort = $request->query('sort', 'nombre');
        $sortDir = strtolower($request->query('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (!in_array($sort, self::SORT_WHITELIST, true)) {
            $sort = 'nombre';
        }

        $query = Cliente::with('tipoCliente')
            ->when($search !== null && $search !== '', function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('code', 'like', '%' . $search . '%')
                        ->orWhere('nombre', 'like', '%' . $search . '%');
                });
            })
            ->when($tipoClienteId !== null && $tipoClienteId !== '', function ($q) use ($tipoClienteId) {
                $q->where('tipo_cliente_id', (int) $tipoClienteId);
            })
            ->when($activo !== null && $activo !== '', function ($q) use ($activo) {
                $q->where('activo', filter_var($activo, FILTER_VALIDATE_BOOLEAN));
            })
            ->when($inhabilitado !== null && $inhabilitado !== '', function ($q) use ($inhabilitado) {
                $q->where('inhabilitado', filter_var($inhabilitado, FILTER_VALIDATE_BOOLEAN));
            })
            ->orderBy($sort, $sortDir);

        $paginator = $query->paginate($pageSize, ['*'], 'page', $page);

        $items = $paginator->getCollection()->map(function (Cliente $c) {
            return [
                'id' => $c->id,
                'code' => $c->code,
                'nombre' => $c->nombre,
                'tipo_cliente' => $c->tipoCliente ? [
                    'id' => $c->tipoCliente->id,
                    'code' => $c->tipoCliente->code,
                    'descripcion' => $c->tipoCliente->descripcion,
                ] : null,
                'email' => $c->email,
                'activo' => $c->activo,
                'inhabilitado' => $c->inhabilitado,
                'created_at' => $c->created_at?->toIso8601String(),
                'updated_at' => $c->updated_at?->toIso8601String(),
            ];
        })->values()->all();

        return response()->json([
            'error' => 0,
            'respuesta' => 'Clientes obtenidos correctamente',
            'resultado' => [
                'items' => $items,
                'page' => $paginator->currentPage(),
                'page_size' => $paginator->perPage(),
                'total' => $paginator->total(),
                'total_pages' => $paginator->lastPage(),
            ],
        ], 200);
    }

    /**
     * GET /api/v1/tipos-cliente
     *
     * Lista de tipos de cliente (para filtro en listado). Solo supervisores.
     */
    public function tiposCliente(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isSupervisor()) {
            return response()->json([
                'error' => self::ERROR_FORBIDDEN,
                'respuesta' => 'No tiene permiso para acceder a esta funcionalidad',
                'resultado' => (object) [],
            ], 403);
        }

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

    /**
     * GET /api/v1/clientes/{id}
     *
     * Obtener detalle del cliente para edición. Solo supervisores. TR-010(MH).
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
            $cliente = $this->clienteService->getById($id);
            return response()->json([
                'error' => 0,
                'respuesta' => 'Cliente obtenido correctamente',
                'resultado' => $cliente,
            ], 200);
        } catch (\Exception $e) {
            if ((int) $e->getCode() === ClienteService::ERROR_NOT_FOUND) {
                return response()->json([
                    'error' => ClienteService::ERROR_NOT_FOUND,
                    'respuesta' => $e->getMessage(),
                    'resultado' => (object) [],
                ], 404);
            }
            throw $e;
        }
    }

    /**
     * POST /api/v1/clientes
     *
     * Crear un nuevo cliente. Solo supervisores. TR-009(MH).
     * Si habilitar_acceso: crear User y luego Cliente con user_id.
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
                'nombre' => 'required|string|max:255',
                'tipo_cliente_id' => 'required|integer|min:1',
                'email' => 'nullable|string|email|max:255',
                'password' => 'nullable|string|min:8',
                'habilitar_acceso' => 'nullable|boolean',
                'activo' => 'nullable|boolean',
                'inhabilitado' => 'nullable|boolean',
            ]);
            $data['habilitar_acceso'] = !empty($data['habilitar_acceso'] ?? false);
        } catch (ValidationException $e) {
            $first = $e->errors();
            $message = is_array($first) ? (reset($first)[0] ?? 'Error de validación') : 'Error de validación';
            return response()->json([
                'error' => 422,
                'respuesta' => $message,
                'resultado' => ['errors' => $e->errors()],
            ], 422);
        }

        try {
            $cliente = $this->clienteService->create($data);
            return response()->json([
                'error' => 0,
                'respuesta' => 'Cliente creado correctamente',
                'resultado' => $cliente,
            ], 201);
        } catch (ValidationException $e) {
            $first = $e->errors();
            $message = is_array($first) ? (reset($first)[0] ?? 'Error de validación') : 'Error de validación';
            return response()->json([
                'error' => 422,
                'respuesta' => $message,
                'resultado' => ['errors' => $e->errors()],
            ], 422);
        } catch (\Exception $e) {
            $code = (int) $e->getCode();
            if ($code === ClienteService::ERROR_CODE_DUPLICATE || $code === ClienteService::ERROR_EMAIL_DUPLICATE) {
                return response()->json([
                    'error' => $code,
                    'respuesta' => $e->getMessage(),
                    'resultado' => (object) [],
                ], 409);
            }
            if ($code === ClienteService::ERROR_SIN_TIPOS_TAREA) {
                return response()->json([
                    'error' => $code,
                    'respuesta' => $e->getMessage(),
                    'resultado' => (object) [],
                ], 422);
            }
            return response()->json([
                'error' => 9999,
                'respuesta' => 'Error inesperado del servidor',
                'resultado' => (object) [],
            ], 500);
        }
    }

    /**
     * PUT /api/v1/clientes/{id}
     *
     * Actualizar cliente existente. Solo supervisores. TR-010(MH).
     * Código no modificable. Sincroniza USERS si tiene user_id.
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
                'nombre' => 'required|string|max:255',
                'tipo_cliente_id' => 'required|integer|min:1',
                'email' => 'nullable|string|email|max:255',
                'password' => 'nullable|string|min:8',
                'habilitar_acceso' => 'nullable|boolean',
                'activo' => 'nullable|boolean',
                'inhabilitado' => 'nullable|boolean',
            ]);
            $data['habilitar_acceso'] = !empty($data['habilitar_acceso'] ?? false);
        } catch (ValidationException $e) {
            $first = $e->errors();
            $message = is_array($first) ? (reset($first)[0] ?? 'Error de validación') : 'Error de validación';
            return response()->json([
                'error' => 422,
                'respuesta' => $message,
                'resultado' => ['errors' => $e->errors()],
            ], 422);
        }

        try {
            $cliente = $this->clienteService->update($id, $data);
            return response()->json([
                'error' => 0,
                'respuesta' => 'Cliente actualizado correctamente',
                'resultado' => $cliente,
            ], 200);
        } catch (ValidationException $e) {
            $first = $e->errors();
            $message = is_array($first) ? (reset($first)[0] ?? 'Error de validación') : 'Error de validación';
            return response()->json([
                'error' => 422,
                'respuesta' => $message,
                'resultado' => ['errors' => $e->errors()],
            ], 422);
        } catch (\Exception $e) {
            $code = (int) $e->getCode();
            if ($code === ClienteService::ERROR_NOT_FOUND) {
                return response()->json([
                    'error' => $code,
                    'respuesta' => $e->getMessage(),
                    'resultado' => (object) [],
                ], 404);
            }
            if ($code === ClienteService::ERROR_CODE_DUPLICATE || $code === ClienteService::ERROR_EMAIL_DUPLICATE) {
                return response()->json([
                    'error' => $code,
                    'respuesta' => $e->getMessage(),
                    'resultado' => (object) [],
                ], 409);
            }
            if ($code === ClienteService::ERROR_SIN_TIPOS_TAREA) {
                return response()->json([
                    'error' => $code,
                    'respuesta' => $e->getMessage(),
                    'resultado' => (object) [],
                ], 422);
            }
            return response()->json([
                'error' => 9999,
                'respuesta' => 'Error inesperado del servidor',
                'resultado' => (object) [],
            ], 500);
        }
    }

    /**
     * DELETE /api/v1/clientes/{id}
     *
     * Eliminar un cliente. Solo supervisores. TR-011(MH).
     * No se puede eliminar si tiene tareas asociadas (422, error 2112).
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
            $this->clienteService->delete($id);
            return response()->json([
                'error' => 0,
                'respuesta' => 'Cliente eliminado correctamente',
                'resultado' => (object) [],
            ], 200);
        } catch (\Exception $e) {
            $code = (int) $e->getCode();
            if ($code === ClienteService::ERROR_NOT_FOUND) {
                return response()->json([
                    'error' => $code,
                    'respuesta' => $e->getMessage(),
                    'resultado' => (object) [],
                ], 404);
            }
            if ($code === ClienteService::ERROR_TIENE_TAREAS) {
                return response()->json([
                    'error' => $code,
                    'respuesta' => $e->getMessage(),
                    'resultado' => (object) [],
                ], 422);
            }
            return response()->json([
                'error' => 9999,
                'respuesta' => 'Error inesperado del servidor',
                'resultado' => (object) [],
            ], 500);
        }
    }

    /**
     * GET /api/v1/clientes/{id}/tipos-tarea
     *
     * Obtener tipos de tarea asignados al cliente (no genéricos). Solo supervisores. TR-012(MH).
     */
    public function tiposTarea(Request $request, int $id): JsonResponse
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
            $tipos = $this->clienteService->getTiposTareaCliente($id);
            return response()->json([
                'error' => 0,
                'respuesta' => 'Tipos de tarea obtenidos correctamente',
                'resultado' => $tipos,
            ], 200);
        } catch (\Exception $e) {
            if ((int) $e->getCode() === ClienteService::ERROR_NOT_FOUND) {
                return response()->json([
                    'error' => ClienteService::ERROR_NOT_FOUND,
                    'respuesta' => $e->getMessage(),
                    'resultado' => (object) [],
                ], 404);
            }
            throw $e;
        }
    }

    /**
     * PUT /api/v1/clientes/{id}/tipos-tarea
     *
     * Actualizar asignación de tipos de tarea del cliente. Body: tipo_tarea_ids[]. Solo supervisores. TR-012(MH).
     */
    public function updateTiposTarea(Request $request, int $id): JsonResponse
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
                'tipo_tarea_ids' => 'present|array',
                'tipo_tarea_ids.*' => 'integer|min:1',
            ]);
        } catch (ValidationException $e) {
            $first = $e->errors();
            $message = is_array($first) ? (reset($first)[0] ?? 'Error de validación') : 'Error de validación';
            return response()->json([
                'error' => 422,
                'respuesta' => $message,
                'resultado' => ['errors' => $e->errors()],
            ], 422);
        }

        try {
            $tipos = $this->clienteService->updateTiposTareaCliente($id, $data['tipo_tarea_ids']);
            return response()->json([
                'error' => 0,
                'respuesta' => 'Tipos de tarea actualizados correctamente',
                'resultado' => $tipos,
            ], 200);
        } catch (\Exception $e) {
            $code = (int) $e->getCode();
            if ($code === ClienteService::ERROR_NOT_FOUND) {
                return response()->json([
                    'error' => $code,
                    'respuesta' => $e->getMessage(),
                    'resultado' => (object) [],
                ], 404);
            }
            if (in_array($code, [
                ClienteService::ERROR_SIN_TIPOS_TAREA,
                ClienteService::ERROR_TIPO_GENERICO,
                ClienteService::ERROR_TIPO_TAREA_NOT_FOUND,
                ClienteService::ERROR_TIPO_TAREA_INACTIVO,
            ], true)) {
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
