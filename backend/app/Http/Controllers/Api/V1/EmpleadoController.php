<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\EmpleadoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Controller: EmpleadoController
 *
 * Listado, creación, edición y eliminación de empleados (solo supervisores). TR-018(MH), TR-019(MH), TR-020(MH), TR-021(MH).
 *
 * Endpoints:
 * - GET /api/v1/empleados - Listado paginado con búsqueda y filtros
 * - POST /api/v1/empleados - Crear empleado
 * - GET /api/v1/empleados/{id} - Obtener empleado para edición
 * - PUT /api/v1/empleados/{id} - Actualizar empleado
 * - DELETE /api/v1/empleados/{id} - Eliminar empleado
 *
 * @see TR-018(MH)-listado-de-empleados.md
 * @see TR-019(MH)-creación-de-empleado.md
 * @see TR-020(MH)-edición-de-empleado.md
 * @see TR-021(MH)-eliminación-de-empleado.md
 * @see specs/endpoints/empleados-list.md
 * @see specs/endpoints/empleados-create.md
 * @see specs/endpoints/empleados-update.md
 * @see specs/endpoints/empleados-delete.md
 */
class EmpleadoController extends Controller
{
    public function __construct(
        private EmpleadoService $empleadoService
    ) {
    }

    /** Código error 403: solo supervisores */
    public const ERROR_FORBIDDEN = 3101;

    /**
     * GET /api/v1/empleados
     *
     * Listado paginado de empleados con búsqueda y filtros. Solo supervisores.
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

        $params = [
            'page' => $request->query('page', 1),
            'page_size' => $request->query('page_size', 20),
            'search' => $request->query('search'),
            'supervisor' => $request->query('supervisor'),
            'activo' => $request->query('activo'),
            'inhabilitado' => $request->query('inhabilitado'),
            'sort' => $request->query('sort', 'nombre'),
            'sort_dir' => $request->query('sort_dir', 'asc'),
        ];

        $result = $this->empleadoService->list($params);

        return response()->json([
            'error' => 0,
            'respuesta' => 'Empleados obtenidos correctamente',
            'resultado' => $result,
        ], 200);
    }

    /**
     * POST /api/v1/empleados
     *
     * Crear un nuevo empleado. Solo supervisores.
     * Siempre crea primero User en USERS y luego empleado en PQ_PARTES_USUARIOS.
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
                'email' => 'nullable|string|email|max:255',
                'password' => 'required|string|min:8',
                'supervisor' => 'nullable|boolean',
                'activo' => 'nullable|boolean',
                'inhabilitado' => 'nullable|boolean',
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
            $empleado = $this->empleadoService->create($data);
            return response()->json([
                'error' => 0,
                'respuesta' => 'Empleado creado correctamente',
                'resultado' => $empleado,
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
            if ($code === EmpleadoService::ERROR_CODE_DUPLICATE || $code === EmpleadoService::ERROR_EMAIL_DUPLICATE) {
                return response()->json([
                    'error' => $code,
                    'respuesta' => $e->getMessage(),
                    'resultado' => (object) [],
                ], 409);
            }
            return response()->json([
                'error' => 500,
                'respuesta' => 'Error al crear empleado: ' . $e->getMessage(),
                'resultado' => (object) [],
            ], 500);
        }
    }

    /**
     * GET /api/v1/empleados/{id}
     *
     * Obtener datos de un empleado para edición o detalle. Solo supervisores.
     * Query param opcional: include_stats=true para incluir total_tareas (TR-022).
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

        $includeStats = filter_var($request->query('include_stats', false), FILTER_VALIDATE_BOOLEAN);

        try {
            $empleado = $this->empleadoService->getById($id, $includeStats);
            return response()->json([
                'error' => 0,
                'respuesta' => 'Empleado obtenido correctamente',
                'resultado' => $empleado,
            ], 200);
        } catch (\Exception $e) {
            $code = (int) $e->getCode();
            if ($code === EmpleadoService::ERROR_NOT_FOUND) {
                return response()->json([
                    'error' => $code,
                    'respuesta' => $e->getMessage(),
                    'resultado' => (object) [],
                ], 404);
            }
            return response()->json([
                'error' => 500,
                'respuesta' => 'Error al obtener empleado: ' . $e->getMessage(),
                'resultado' => (object) [],
            ], 500);
        }
    }

    /**
     * PUT /api/v1/empleados/{id}
     *
     * Actualizar un empleado existente. Solo supervisores.
     * El código no es modificable.
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
                'email' => 'nullable|string|email|max:255',
                'password' => 'nullable|string|min:8',
                'supervisor' => 'nullable|boolean',
                'activo' => 'nullable|boolean',
                'inhabilitado' => 'nullable|boolean',
            ]);
            // Ignorar code si se envía (no es modificable)
            unset($data['code']);
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
            $empleado = $this->empleadoService->update($id, $data);
            return response()->json([
                'error' => 0,
                'respuesta' => 'Empleado actualizado correctamente',
                'resultado' => $empleado,
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
            if ($code === EmpleadoService::ERROR_NOT_FOUND) {
                return response()->json([
                    'error' => $code,
                    'respuesta' => $e->getMessage(),
                    'resultado' => (object) [],
                ], 404);
            }
            if ($code === EmpleadoService::ERROR_EMAIL_DUPLICATE) {
                return response()->json([
                    'error' => $code,
                    'respuesta' => $e->getMessage(),
                    'resultado' => (object) [],
                ], 409);
            }
            return response()->json([
                'error' => 500,
                'respuesta' => 'Error al actualizar empleado: ' . $e->getMessage(),
                'resultado' => (object) [],
            ], 500);
        }
    }

    /**
     * DELETE /api/v1/empleados/{id}
     *
     * Eliminar un empleado. Solo supervisores.
     * No se puede eliminar si tiene tareas asociadas (error 2113).
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
            $this->empleadoService->delete($id);
            return response()->json([
                'error' => 0,
                'respuesta' => 'Empleado eliminado correctamente',
                'resultado' => (object) [],
            ], 200);
        } catch (\Exception $e) {
            $code = (int) $e->getCode();
            if ($code === EmpleadoService::ERROR_NOT_FOUND) {
                return response()->json([
                    'error' => $code,
                    'respuesta' => $e->getMessage(),
                    'resultado' => (object) [],
                ], 404);
            }
            if ($code === EmpleadoService::ERROR_TIENE_TAREAS) {
                return response()->json([
                    'error' => $code,
                    'respuesta' => $e->getMessage(),
                    'resultado' => (object) [],
                ], 422);
            }
            return response()->json([
                'error' => 500,
                'respuesta' => 'Error al eliminar empleado: ' . $e->getMessage(),
                'resultado' => (object) [],
            ], 500);
        }
    }
}
