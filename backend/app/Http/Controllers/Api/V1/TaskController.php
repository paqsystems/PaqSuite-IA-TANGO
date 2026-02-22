<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\BulkToggleCloseRequest;
use App\Http\Requests\Api\V1\CreateTaskRequest;
use App\Http\Requests\Api\V1\UpdateTaskRequest;
use App\Services\TaskService;
use App\Models\Cliente;
use App\Models\TipoTarea;
use App\Models\Usuario;
use App\Models\ClienteTipoTarea;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller: TaskController
 * 
 * Controlador para endpoints de gestión de tareas.
 * 
 * Endpoints:
 * - GET /api/v1/tasks - Listar tareas propias (paginado, filtros)
 * - POST /api/v1/tasks - Crear nuevo registro de tarea
 * - GET /api/v1/tasks/clients - Obtener lista de clientes activos
 * - GET /api/v1/tasks/task-types - Obtener tipos de tarea disponibles
 * - GET /api/v1/tasks/employees - Obtener lista de empleados (solo supervisores)
 * - GET /api/v1/tasks/{id} - Obtener tarea para edición (TR-029)
 * - PUT /api/v1/tasks/{id} - Actualizar tarea (TR-029)
 * - DELETE /api/v1/tasks/{id} - Eliminar tarea (TR-030)
 * - GET /api/v1/tasks/all - Listar todas las tareas (solo supervisores, TR-034)
 *
 * @see TR-028(MH)-carga-de-tarea-diaria.md
 * @see TR-029(MH)-edición-de-tarea-propia.md
 * @see TR-033(MH)-visualización-de-lista-de-tareas-propias.md
 * @see TR-034(MH)-visualización-de-lista-de-todas-las-tareas-supervisor.md
 */
class TaskController extends Controller
{
    /**
     * Servicio de tareas
     */
    protected TaskService $taskService;

    /**
     * Constructor
     *
     * @param TaskService $taskService Servicio inyectado
     */
    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Listar tareas del usuario autenticado
     *
     * GET /api/v1/tasks?page=1&per_page=15&fecha_desde=...&fecha_hasta=...&cliente_id=...&tipo_tarea_id=...&busqueda=...&ordenar_por=fecha&orden=desc
     *
     * @param Request $request Request con query params y usuario autenticado
     * @return JsonResponse Respuesta con data, pagination y totales
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $filters = [
                'page' => $request->query('page', 1),
                'per_page' => $request->query('per_page', 15),
                'fecha_desde' => $request->query('fecha_desde'),
                'fecha_hasta' => $request->query('fecha_hasta'),
                'cliente_id' => $request->query('cliente_id'),
                'tipo_tarea_id' => $request->query('tipo_tarea_id'),
                'usuario_id' => $request->query('usuario_id'),
                'busqueda' => $request->query('busqueda'),
                'ordenar_por' => $request->query('ordenar_por', 'fecha'),
                'orden' => $request->query('orden', 'desc'),
            ];

            $result = $this->taskService->listTasks($user, $filters);

            return response()->json([
                'error' => 0,
                'respuesta' => 'Tareas obtenidas correctamente',
                'resultado' => $result,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 9999,
                'respuesta' => 'Error inesperado del servidor',
                'resultado' => (object) [],
            ], 500);
        }
    }

    /**
     * Listar todas las tareas (solo supervisores) – TR-034
     *
     * GET /api/v1/tasks/all?page=1&per_page=15&fecha_desde=...&fecha_hasta=...&usuario_id=...&cliente_id=...&tipo_tarea_id=...&busqueda=...&ordenar_por=fecha|empleado|cliente&orden=desc
     * Retorna 403 si el usuario no es supervisor.
     */
    public function indexAll(Request $request): JsonResponse
    {
        $user = $request->user();
        $empleado = Usuario::where('user_id', $user->id)->first();

        if (!$empleado || !$empleado->supervisor) {
            return response()->json([
                'error' => 4030,
                'respuesta' => 'Solo los supervisores pueden acceder a todas las tareas',
                'resultado' => (object) [],
            ], 403);
        }

        $fechaDesde = $request->query('fecha_desde');
        $fechaHasta = $request->query('fecha_hasta');
        if ($fechaDesde !== null && $fechaDesde !== '' && $fechaHasta !== null && $fechaHasta !== '' && $fechaDesde > $fechaHasta) {
            return response()->json([
                'error' => 1305,
                'respuesta' => 'La fecha desde no puede ser posterior a fecha hasta',
                'resultado' => (object) [],
            ], 422);
        }

        $cerradoParam = $request->query('cerrado');
        $cerrado = null;
        if ($cerradoParam === 'true' || $cerradoParam === '1') {
            $cerrado = true;
        } elseif ($cerradoParam === 'false' || $cerradoParam === '0') {
            $cerrado = false;
        }

        try {
            $filters = [
                'page' => $request->query('page', 1),
                'per_page' => $request->query('per_page', 15),
                'fecha_desde' => $fechaDesde,
                'fecha_hasta' => $fechaHasta,
                'cliente_id' => $request->query('cliente_id'),
                'tipo_tarea_id' => $request->query('tipo_tarea_id'),
                'usuario_id' => $request->query('usuario_id'),
                'busqueda' => $request->query('busqueda'),
                'ordenar_por' => $request->query('ordenar_por', 'fecha'),
                'orden' => $request->query('orden', 'desc'),
                'cerrado' => $cerrado,
            ];

            $result = $this->taskService->listTasks($user, $filters);

            return response()->json([
                'error' => 0,
                'respuesta' => 'Tareas obtenidas correctamente',
                'resultado' => $result,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 9999,
                'respuesta' => 'Error inesperado del servidor',
                'resultado' => (object) [],
            ], 500);
        }
    }

    /**
     * Procesamiento masivo: invertir estado cerrado de tareas seleccionadas (TR-042, TR-043).
     * POST /api/v1/tasks/bulk-toggle-close
     * Solo supervisores. Body: { "task_ids": [1, 2, 3] }
     */
    public function bulkToggleClose(BulkToggleCloseRequest $request): JsonResponse
    {
        $user = $request->user();
        $empleado = Usuario::where('user_id', $user->id)->first();
        if (!$empleado || !$empleado->supervisor) {
            return response()->json([
                'error' => 4030,
                'respuesta' => 'Solo los supervisores pueden ejecutar el proceso masivo',
                'resultado' => (object) [],
            ], 403);
        }

        $taskIds = $request->validated()['task_ids'];
        $result = $this->taskService->bulkToggleClose($taskIds);
        $count = $result['processed'];
        return response()->json([
            'error' => 0,
            'respuesta' => 'Se procesaron ' . $count . ' registro' . ($count !== 1 ? 's' : ''),
            'resultado' => $result,
        ], 200);
    }

    /**
     * Crear un nuevo registro de tarea
     * 
     * Crea un registro de tarea para el usuario autenticado o para otro empleado si es supervisor.
     * Requiere autenticación (middleware auth:sanctum).
     * 
     * @param CreateTaskRequest $request Request validado
     * @return JsonResponse Respuesta con datos del registro creado
     * 
     * @response 201 {
     *   "error": 0,
     *   "respuesta": "Tarea registrada correctamente",
     *   "resultado": {
     *     "id": 1,
     *     "usuario_id": 1,
     *     "cliente_id": 1,
     *     "tipo_tarea_id": 2,
     *     "fecha": "2026-01-28",
     *     "duracion_minutos": 120,
     *     "sin_cargo": false,
     *     "presencial": true,
     *     "observacion": "Desarrollo de feature X",
     *     "cerrado": false,
     *     "created_at": "2026-01-28T10:30:00+00:00",
     *     "updated_at": "2026-01-28T10:30:00+00:00"
     *   }
     * }
     * 
     * @response 403 {
     *   "error": 4003,
     *   "respuesta": "No tiene permisos para asignar tareas a otros empleados",
     *   "resultado": {}
     * }
     * 
     * @response 422 {
     *   "error": 4220,
     *   "respuesta": "Errores de validación",
     *   "resultado": {
     *     "errors": {
     *       "fecha": ["La fecha es obligatoria"],
     *       "duracion_minutos": ["La duración debe ser múltiplo de 15 minutos"]
     *     }
     *   }
     * }
     */
    public function store(CreateTaskRequest $request): JsonResponse
    {
        try {
            // Obtener usuario autenticado
            $user = $request->user();
            
            // Crear tarea usando el servicio
            $datosTarea = $this->taskService->createTask($request->validated(), $user);

            return response()->json([
                'error' => 0,
                'respuesta' => 'Tarea registrada correctamente',
                'resultado' => $datosTarea
            ], 201);

        } catch (\Exception $e) {
            return $this->handleTaskException($e);
        }
    }

    /**
     * Obtener una tarea por ID para edición (TR-029)
     *
     * GET /api/v1/tasks/{id}
     * Empleado: solo sus tareas. Supervisor: cualquier tarea.
     * Retorna 404 si no existe, 400 con error 2110 si está cerrada, 403 con 4030 si sin permisos.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            $task = $this->taskService->getTask($id, $user);
            return response()->json([
                'error' => 0,
                'respuesta' => 'Tarea obtenida correctamente',
                'resultado' => $task,
            ], 200);
        } catch (\Exception $e) {
            return $this->handleTaskException($e);
        }
    }

    /**
     * Actualizar una tarea existente (TR-029)
     *
     * PUT /api/v1/tasks/{id}
     * Empleado: solo sus tareas no cerradas. Supervisor: cualquier tarea no cerrada.
     */
    public function update(UpdateTaskRequest $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            $task = $this->taskService->updateTask($id, $request->validated(), $user);
            return response()->json([
                'error' => 0,
                'respuesta' => 'Tarea actualizada correctamente',
                'resultado' => $task,
            ], 200);
        } catch (\Exception $e) {
            return $this->handleTaskException($e);
        }
    }

    /**
     * Eliminar una tarea existente (TR-030)
     *
     * DELETE /api/v1/tasks/{id}
     * Empleado: solo sus tareas no cerradas. Supervisor: cualquier tarea no cerrada.
     * Retorna 404 si no existe, 400 con error 2111 si está cerrada, 403 con 4030 si sin permisos.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            $this->taskService->deleteTask($id, $user);
            return response()->json([
                'error' => 0,
                'respuesta' => 'Tarea eliminada correctamente',
                'resultado' => (object) [],
            ], 200);
        } catch (\Exception $e) {
            return $this->handleTaskException($e);
        }
    }

    /**
     * Mapear excepciones del TaskService a respuestas JSON
     */
    private function handleTaskException(\Exception $e): JsonResponse
    {
        $httpCode = 500;
        $errorCode = 9999;

        if ($e->getCode() === TaskService::ERROR_FORBIDDEN) {
            $httpCode = 403;
            $errorCode = TaskService::ERROR_FORBIDDEN;
        } elseif ($e->getCode() === TaskService::ERROR_CLOSED) {
            $httpCode = 400;
            $errorCode = TaskService::ERROR_CLOSED;
        } elseif ($e->getCode() === TaskService::ERROR_FORBIDDEN_EDIT ||
                  $e->getCode() === TaskService::ERROR_FORBIDDEN_DELETE) {
            $httpCode = 403;
            $errorCode = $e->getCode();
        } elseif ($e->getCode() === TaskService::ERROR_CLOSED_DELETE) {
            $httpCode = 400;
            $errorCode = TaskService::ERROR_CLOSED_DELETE;
        } elseif ($e->getCode() === TaskService::ERROR_CLIENTE_INACTIVO ||
                  $e->getCode() === TaskService::ERROR_TIPO_TAREA_INACTIVO ||
                  $e->getCode() === TaskService::ERROR_EMPLEADO_INACTIVO ||
                  $e->getCode() === TaskService::ERROR_TIPO_TAREA_NO_DISPONIBLE) {
            $httpCode = 422;
            $errorCode = $e->getCode();
            // TR-031: para ERROR_EMPLEADO_INACTIVO devolver resultado con errors para usuario_id
            if ($e->getCode() === TaskService::ERROR_EMPLEADO_INACTIVO) {
                return response()->json([
                    'error' => $errorCode,
                    'respuesta' => $e->getMessage(),
                    'resultado' => ['errors' => ['usuario_id' => [$e->getMessage()]]],
                ], 422);
            }
        } elseif ($e->getCode() === 404) {
            $httpCode = 404;
            $errorCode = 4040;
        }

        return response()->json([
            'error' => $errorCode,
            'respuesta' => $e->getMessage(),
            'resultado' => (object) []
        ], $httpCode);
    }

    /**
     * Obtener lista de clientes activos
     * 
     * Retorna lista de clientes activos y no inhabilitados para el selector.
     * Requiere autenticación (middleware auth:sanctum).
     * 
     * @param Request $request Request con usuario autenticado
     * @return JsonResponse Respuesta con lista de clientes
     * 
     * @response 200 {
     *   "error": 0,
     *   "respuesta": "Clientes obtenidos correctamente",
     *   "resultado": [
     *     {
     *       "id": 1,
     *       "code": "CLI001",
     *       "nombre": "Empresa ABC S.A."
     *     }
     *   ]
     * }
     */
    public function getClients(Request $request): JsonResponse
    {
        try {
            $clientes = Cliente::where('activo', true)
                ->where('inhabilitado', false)
                ->orderBy('nombre')
                ->get()
                ->map(function ($cliente) {
                    return [
                        'id' => $cliente->id,
                        'code' => $cliente->code,
                        'nombre' => $cliente->nombre,
                    ];
                });

            return response()->json([
                'error' => 0,
                'respuesta' => 'Clientes obtenidos correctamente',
                'resultado' => $clientes->values()->toArray()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 9999,
                'respuesta' => 'Error inesperado del servidor',
                'resultado' => (object) []
            ], 500);
        }
    }

    /**
     * Obtener tipos de tarea disponibles
     * 
     * Retorna tipos de tarea genéricos o tipos asignados al cliente si se proporciona cliente_id.
     * Requiere autenticación (middleware auth:sanctum).
     * 
     * @param Request $request Request con usuario autenticado
     * @return JsonResponse Respuesta con lista de tipos de tarea
     * 
     * @response 200 {
     *   "error": 0,
     *   "respuesta": "Tipos de tarea obtenidos correctamente",
     *   "resultado": [
     *     {
     *       "id": 1,
     *       "code": "DESARROLLO",
     *       "descripcion": "Desarrollo de software",
     *       "is_generico": true
     *     }
     *   ]
     * }
     */
    public function getTaskTypes(Request $request): JsonResponse
    {
        try {
            $clienteId = $request->query('cliente_id');

            // Sin cliente_id (ej. filtro "Todos"): retornar todos los tipos activos (genéricos y no genéricos)
            if (!$clienteId || $clienteId === '' || $clienteId === 'all') {
                $tiposDisponibles = TipoTarea::where('activo', true)
                    ->where('inhabilitado', false)
                    ->orderBy('is_generico', 'desc')
                    ->orderBy('descripcion')
                    ->get();
            } else {
                // Con cliente_id: genéricos + tipos asignados al cliente
                $tiposGenericos = TipoTarea::where('is_generico', true)
                    ->where('activo', true)
                    ->where('inhabilitado', false)
                    ->get();

                $tiposAsignadosIds = ClienteTipoTarea::where('cliente_id', (int) $clienteId)
                    ->pluck('tipo_tarea_id')
                    ->toArray();

                $tiposAsignados = TipoTarea::whereIn('id', $tiposAsignadosIds)
                    ->where('activo', true)
                    ->where('inhabilitado', false)
                    ->get();

                $tiposDisponibles = $tiposGenericos->merge($tiposAsignados)->unique('id');
            }

            $resultado = $tiposDisponibles->map(function ($tipo) {
                return [
                    'id' => $tipo->id,
                    'code' => $tipo->code,
                    'descripcion' => $tipo->descripcion,
                    'is_generico' => $tipo->is_generico,
                ];
            })->values()->toArray();

            return response()->json([
                'error' => 0,
                'respuesta' => 'Tipos de tarea obtenidos correctamente',
                'resultado' => $resultado
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 9999,
                'respuesta' => 'Error inesperado del servidor',
                'resultado' => (object) []
            ], 500);
        }
    }

    /**
     * Obtener lista de empleados activos
     * 
     * Retorna lista de empleados activos y no inhabilitados.
     * Solo accesible para supervisores.
     * Requiere autenticación (middleware auth:sanctum).
     * 
     * @param Request $request Request con usuario autenticado
     * @return JsonResponse Respuesta con lista de empleados o error 403
     * 
     * @response 200 {
     *   "error": 0,
     *   "respuesta": "Empleados obtenidos correctamente",
     *   "resultado": [
     *     {
     *       "id": 1,
     *       "code": "JPEREZ",
     *       "nombre": "Juan Pérez"
     *     }
     *   ]
     * }
     * 
     * @response 403 {
     *   "error": 4003,
     *   "respuesta": "Solo los supervisores pueden acceder a esta información",
     *   "resultado": {}
     * }
     */
    public function getEmployees(Request $request): JsonResponse
    {
        try {
            // Obtener usuario autenticado
            $user = $request->user();
            
            // Verificar que sea empleado supervisor
            $empleado = Usuario::where('user_id', $user->id)->first();
            
            if (!$empleado || !$empleado->supervisor) {
                return response()->json([
                    'error' => 4003,
                    'respuesta' => 'Solo los supervisores pueden acceder a esta información',
                    'resultado' => (object) []
                ], 403);
            }

            $empleados = Usuario::where('activo', true)
                ->where('inhabilitado', false)
                ->orderBy('nombre')
                ->get()
                ->map(function ($empleado) {
                    return [
                        'id' => $empleado->id,
                        'code' => $empleado->code,
                        'nombre' => $empleado->nombre,
                    ];
                });

            return response()->json([
                'error' => 0,
                'respuesta' => 'Empleados obtenidos correctamente',
                'resultado' => $empleados->values()->toArray()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 9999,
                'respuesta' => 'Error inesperado del servidor',
                'resultado' => (object) []
            ], 500);
        }
    }
}
