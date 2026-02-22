<?php

namespace App\Services;

use App\Models\User;
use App\Models\Usuario;
use App\Models\Cliente;
use App\Models\TipoTarea;
use App\Models\RegistroTarea;
use App\Models\ClienteTipoTarea;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service: TaskService
 * 
 * Servicio para gestionar la creación y listado de registros de tarea.
 * Implementa la lógica de negocio para crear tareas diarias y listar tareas propias.
 * 
 * Flujo de listado (TR-033):
 * - Solo tareas del usuario autenticado (usuario_id = empleado del user).
 * - Filtros: fecha, cliente, tipo, búsqueda en observación.
 * - Paginación y totales (cantidad, horas).
 * 
 * Flujo de creación de tarea:
 * 1. Validar que el cliente existe y está activo/no inhabilitado
 * 2. Validar que el tipo de tarea existe, está activo/no inhabilitado y es válido para el cliente
 * 3. Validar permisos: si usuario_id es diferente al autenticado, el usuario debe ser supervisor
 * 4. Determinar usuario_id final (del autenticado o del seleccionado si es supervisor)
 * 5. Crear el registro en PQ_PARTES_REGISTRO_TAREA
 * 6. Retornar datos del registro creado
 * 
 * Códigos de error:
 * - 4003: No tiene permisos para asignar tareas a otros empleados
 * - 4201: Cliente inactivo o inhabilitado
 * - 4202: Tipo de tarea inactivo o inhabilitado
 * - 4203: Empleado inactivo o inhabilitado
 * - 4204: Tipo de tarea no disponible para el cliente
 * 
 * @see TR-028(MH)-carga-de-tarea-diaria.md
 * @see TR-029(MH)-edición-de-tarea-propia.md
 * @see TR-030(MH)-eliminación-de-tarea-propia.md
 * @see TR-033(MH)-visualización-de-lista-de-tareas-propias.md
 * @see TR-044(MH)-consulta-detallada-de-tareas.md
 * @see TR-046(MH)-consulta-agrupada-por-cliente.md
 * @see TR-051(MH)-dashboard-principal.md
 */
class TaskService
{
    /**
     * Códigos de error
     */
    public const ERROR_FORBIDDEN = 4003;
    public const ERROR_CLOSED = 2110;       // No se puede modificar una tarea cerrada (TR-029)
    public const ERROR_FORBIDDEN_EDIT = 4030; // No tiene permisos para editar esta tarea (TR-029)
    public const ERROR_CLOSED_DELETE = 2111;  // No se puede eliminar una tarea cerrada (TR-030)
    public const ERROR_FORBIDDEN_DELETE = 4030; // No tiene permisos para eliminar esta tarea (TR-030)
    public const ERROR_CLIENTE_INACTIVO = 4201;
    public const ERROR_TIPO_TAREA_INACTIVO = 4202;
    public const ERROR_EMPLEADO_INACTIVO = 4203;
    public const ERROR_TIPO_TAREA_NO_DISPONIBLE = 4204;
    /** Período inválido: fecha_desde debe ser <= fecha_hasta (regla 8.1, TR-044) */
    public const ERROR_PERIODO_INVALIDO = 1305;

    /**
     * Listar tareas del usuario autenticado con filtros y paginación
     *
     * @param User $user Usuario autenticado
     * @param array $filters page, per_page, fecha_desde, fecha_hasta, cliente_id, tipo_tarea_id, busqueda, ordenar_por, orden
     * @return array ['data' => [...], 'pagination' => [...], 'totales' => [...]]
     */
    public function listTasks(User $user, array $filters): array
    {
        $empleado = Usuario::where('user_id', $user->id)->first();
        if (!$empleado) {
            return [
                'data' => [],
                'pagination' => [
                    'current_page' => 1,
                    'per_page' => (int) ($filters['per_page'] ?? 15),
                    'total' => 0,
                    'last_page' => 1,
                ],
                'totales' => [
                    'cantidad_tareas' => 0,
                    'total_horas' => 0,
                ],
            ];
        }

        $perPage = max(10, min(20, (int) ($filters['per_page'] ?? 15)));
        $ordenarPor = $filters['ordenar_por'] ?? 'fecha';
        $orden = strtolower($filters['orden'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $esSupervisor = (bool) $empleado->supervisor;
        $filtrarPorEmpleado = isset($filters['usuario_id']) && $filters['usuario_id'] !== '' && $filters['usuario_id'] !== null;
        $empleadoIdFiltro = $filtrarPorEmpleado && $esSupervisor ? (int) $filters['usuario_id'] : $empleado->id;

        $query = RegistroTarea::with(['cliente', 'tipoTarea', 'usuario']);
        if ($esSupervisor && !$filtrarPorEmpleado) {
            // Supervisor sin filtro empleado: todas las tareas
        } else {
            $query->where('usuario_id', $empleadoIdFiltro);
        }

        if (!empty($filters['fecha_desde'])) {
            $query->where('fecha', '>=', $filters['fecha_desde']);
        }
        if (!empty($filters['fecha_hasta'])) {
            $query->where('fecha', '<=', $filters['fecha_hasta']);
        }
        if (isset($filters['cliente_id']) && $filters['cliente_id'] !== '' && $filters['cliente_id'] !== null) {
            $query->where('cliente_id', (int) $filters['cliente_id']);
        }
        if (isset($filters['tipo_tarea_id']) && $filters['tipo_tarea_id'] !== '' && $filters['tipo_tarea_id'] !== null) {
            $query->where('tipo_tarea_id', (int) $filters['tipo_tarea_id']);
        }
        if (!empty($filters['busqueda'])) {
            $busqueda = trim($filters['busqueda']);
            $query->where('observacion', 'like', '%' . $busqueda . '%');
        }
        // TR-040: filtro por estado cerrado (true/false; si no se envía = todos)
        if (isset($filters['cerrado']) && $filters['cerrado'] !== '' && $filters['cerrado'] !== null) {
            $query->where('cerrado', (bool) $filters['cerrado']);
        }

        $totalesQuery = RegistroTarea::query();
        if ($esSupervisor && !$filtrarPorEmpleado) {
            // Todas las tareas para totales
        } else {
            $totalesQuery->where('usuario_id', $empleadoIdFiltro);
        }
        if (!empty($filters['fecha_desde'])) {
            $totalesQuery->where('fecha', '>=', $filters['fecha_desde']);
        }
        if (!empty($filters['fecha_hasta'])) {
            $totalesQuery->where('fecha', '<=', $filters['fecha_hasta']);
        }
        if (isset($filters['cliente_id']) && $filters['cliente_id'] !== '' && $filters['cliente_id'] !== null) {
            $totalesQuery->where('cliente_id', (int) $filters['cliente_id']);
        }
        if (isset($filters['tipo_tarea_id']) && $filters['tipo_tarea_id'] !== '' && $filters['tipo_tarea_id'] !== null) {
            $totalesQuery->where('tipo_tarea_id', (int) $filters['tipo_tarea_id']);
        }
        if (!empty($filters['busqueda'])) {
            $totalesQuery->where('observacion', 'like', '%' . trim($filters['busqueda']) . '%');
        }
        if (isset($filters['cerrado']) && $filters['cerrado'] !== '' && $filters['cerrado'] !== null) {
            $totalesQuery->where('cerrado', (bool) $filters['cerrado']);
        }
        $cantidadTareas = $totalesQuery->count();
        $totalHoras = $totalesQuery->sum('duracion_minutos') / 60;

        $allowedOrder = ['fecha', 'created_at', 'empleado', 'cliente'];
        if (!in_array($ordenarPor, $allowedOrder)) {
            $ordenarPor = 'fecha';
        }
        // TR-034: ordenar por empleado (nombre) o cliente (nombre) requiere join
        if ($ordenarPor === 'empleado') {
            $query->join('PQ_PARTES_USUARIOS', 'PQ_PARTES_USUARIOS.id', '=', 'PQ_PARTES_REGISTRO_TAREA.usuario_id')
                ->select('PQ_PARTES_REGISTRO_TAREA.*')
                ->orderBy('PQ_PARTES_USUARIOS.nombre', $orden);
        } elseif ($ordenarPor === 'cliente') {
            $query->join('PQ_PARTES_CLIENTES', 'PQ_PARTES_CLIENTES.id', '=', 'PQ_PARTES_REGISTRO_TAREA.cliente_id')
                ->select('PQ_PARTES_REGISTRO_TAREA.*')
                ->orderBy('PQ_PARTES_CLIENTES.nombre', $orden);
        } else {
            $query->orderBy($ordenarPor, $orden);
        }

        $paginator = $query->paginate($perPage);

        $data = $paginator->getCollection()->map(function (RegistroTarea $t) {
            $observacionTruncada = mb_strlen($t->observacion) > 50
                ? mb_substr($t->observacion, 0, 50) . '...'
                : $t->observacion;
            $horas = intdiv($t->duracion_minutos, 60);
            $minutos = $t->duracion_minutos % 60;
            $duracionHoras = sprintf('%d:%02d', $horas, $minutos);

            $item = [
                'id' => $t->id,
                'fecha' => $t->fecha->format('Y-m-d'),
                'cliente' => [
                    'id' => $t->cliente->id,
                    'nombre' => $t->cliente->nombre,
                ],
                'tipo_tarea' => [
                    'id' => $t->tipoTarea->id,
                    'nombre' => $t->tipoTarea->descripcion,
                ],
                'duracion_minutos' => $t->duracion_minutos,
                'duracion_horas' => $duracionHoras,
                'sin_cargo' => $t->sin_cargo,
                'presencial' => $t->presencial,
                'observacion' => $observacionTruncada,
                'cerrado' => $t->cerrado,
                'created_at' => $t->created_at->toIso8601String(),
            ];
            // TR-032: incluir empleado propietario para modal de eliminación (supervisor)
            if ($t->usuario) {
                $item['empleado'] = [
                    'id' => $t->usuario->id,
                    'code' => $t->usuario->code,
                    'nombre' => $t->usuario->nombre,
                ];
            }
            return $item;
        })->values()->all();

        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
            'totales' => [
                'cantidad_tareas' => $cantidadTareas,
                'total_horas' => round($totalHoras, 2),
            ],
        ];
    }

    /**
     * Procesamiento masivo: invertir estado cerrado de las tareas indicadas (TR-042).
     * Solo supervisores. Atómico (transacción).
     *
     * @param array<int> $taskIds IDs de tareas
     * @return array{processed: int, task_ids: array<int>}
     */
    public function bulkToggleClose(array $taskIds): array
    {
        $taskIds = array_values(array_unique(array_map('intval', $taskIds)));
        $taskIds = array_filter($taskIds, fn ($id) => $id > 0);
        if (count($taskIds) === 0) {
            throw new \InvalidArgumentException('Debe seleccionar al menos una tarea', 1212);
        }

        return DB::transaction(function () use ($taskIds) {
            $registros = RegistroTarea::whereIn('id', $taskIds)->get();
            $processed = 0;
            foreach ($registros as $t) {
                $t->cerrado = !$t->cerrado;
                $t->save();
                $processed++;
            }
            return [
                'processed' => $processed,
                'task_ids' => $taskIds,
            ];
        });
    }

    /**
     * Listado para consulta detallada (TR-044): filtros por rol, período, total_horas decimal.
     * Empleado: solo sus tareas. Supervisor: todas, con filtros opcionales. Cliente: solo donde es el cliente.
     *
     * @param User $user Usuario autenticado
     * @param array $filters page, per_page, fecha_desde, fecha_hasta, tipo_cliente_id, cliente_id, usuario_id, ordenar_por, orden
     * @return array ['data' => [...], 'pagination' => [...], 'total_horas' => float]
     * @throws \Exception Si período inválido (código ERROR_PERIODO_INVALIDO 1305)
     */
    public function listDetailReport(User $user, array $filters): array
    {
        $empleado = Usuario::where('user_id', $user->id)->first();
        $cliente = Cliente::where('user_id', $user->id)->first();

        $esCliente = $cliente !== null && $empleado === null;
        $esSupervisor = $empleado && (bool) $empleado->supervisor;

        if ($esCliente) {
            $clienteIdFijo = $cliente->id;
        } else {
            $clienteIdFijo = null;
            if (!$empleado) {
                return [
                    'data' => [],
                    'pagination' => [
                        'current_page' => 1,
                        'per_page' => (int) ($filters['per_page'] ?? 15),
                        'total' => 0,
                        'last_page' => 1,
                    ],
                    'total_horas' => 0,
                ];
            }
        }

        $fechaDesde = isset($filters['fecha_desde']) && $filters['fecha_desde'] !== '' ? $filters['fecha_desde'] : null;
        $fechaHasta = isset($filters['fecha_hasta']) && $filters['fecha_hasta'] !== '' ? $filters['fecha_hasta'] : null;
        if ($fechaDesde !== null && $fechaHasta !== null && $fechaDesde > $fechaHasta) {
            throw new \Exception('El período es inválido: fecha_desde debe ser menor o igual a fecha_hasta', self::ERROR_PERIODO_INVALIDO);
        }

        $perPage = max(10, min(20, (int) ($filters['per_page'] ?? 15)));
        $ordenarPor = $filters['ordenar_por'] ?? 'fecha';
        $orden = strtolower($filters['orden'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $query = RegistroTarea::with(['cliente.tipoCliente', 'tipoTarea', 'usuario']);

        if ($esCliente) {
            $query->where('cliente_id', $clienteIdFijo);
        } else {
            if ($esSupervisor) {
                if (isset($filters['usuario_id']) && $filters['usuario_id'] !== '' && $filters['usuario_id'] !== null) {
                    $query->where('usuario_id', (int) $filters['usuario_id']);
                }
            } else {
                $query->where('usuario_id', $empleado->id);
            }
        }

        if ($esCliente) {
            // Cliente: no filtro por cliente_id (ya fijado)
        } else {
            if (isset($filters['cliente_id']) && $filters['cliente_id'] !== '' && $filters['cliente_id'] !== null) {
                $query->where('cliente_id', (int) $filters['cliente_id']);
            }
            if (isset($filters['tipo_cliente_id']) && $filters['tipo_cliente_id'] !== '' && $filters['tipo_cliente_id'] !== null) {
                $query->whereHas('cliente', function ($q) use ($filters) {
                    $q->where('tipo_cliente_id', (int) $filters['tipo_cliente_id']);
                });
            }
        }

        if ($fechaDesde !== null) {
            $query->where('fecha', '>=', $fechaDesde);
        }
        if ($fechaHasta !== null) {
            $query->where('fecha', '<=', $fechaHasta);
        }

        $totalesQuery = clone $query;
        $totalHoras = $totalesQuery->sum('duracion_minutos') / 60;

        $allowedOrder = ['fecha', 'created_at', 'empleado', 'cliente', 'tipo_tarea', 'horas'];
        if (!in_array($ordenarPor, $allowedOrder)) {
            $ordenarPor = 'fecha';
        }
        if ($ordenarPor === 'empleado') {
            $query->join('PQ_PARTES_USUARIOS', 'PQ_PARTES_USUARIOS.id', '=', 'PQ_PARTES_REGISTRO_TAREA.usuario_id')
                ->select('PQ_PARTES_REGISTRO_TAREA.*')
                ->orderBy('PQ_PARTES_USUARIOS.nombre', $orden);
        } elseif ($ordenarPor === 'cliente') {
            $query->join('PQ_PARTES_CLIENTES', 'PQ_PARTES_CLIENTES.id', '=', 'PQ_PARTES_REGISTRO_TAREA.cliente_id')
                ->select('PQ_PARTES_REGISTRO_TAREA.*')
                ->orderBy('PQ_PARTES_CLIENTES.nombre', $orden);
        } elseif ($ordenarPor === 'tipo_tarea') {
            $query->join('PQ_PARTES_TIPOS_TAREA', 'PQ_PARTES_TIPOS_TAREA.id', '=', 'PQ_PARTES_REGISTRO_TAREA.tipo_tarea_id')
                ->select('PQ_PARTES_REGISTRO_TAREA.*')
                ->orderBy('PQ_PARTES_TIPOS_TAREA.descripcion', $orden);
        } elseif ($ordenarPor === 'horas') {
            $query->orderBy('duracion_minutos', $orden);
        } else {
            $query->orderBy($ordenarPor, $orden);
        }

        $paginator = $query->paginate($perPage);

        $incluirEmpleado = $esSupervisor && !$esCliente;

        $data = $paginator->getCollection()->map(function (RegistroTarea $t) use ($incluirEmpleado) {
            $horasDecimal = round($t->duracion_minutos / 60, 2);
            $item = [
                'id' => $t->id,
                'cliente' => [
                    'id' => $t->cliente->id,
                    'nombre' => $t->cliente->nombre,
                    'tipo_cliente' => $t->cliente->tipoCliente ? $t->cliente->tipoCliente->descripcion : null,
                ],
                'fecha' => $t->fecha->format('Y-m-d'),
                'tipo_tarea' => [
                    'id' => $t->tipoTarea->id,
                    'descripcion' => $t->tipoTarea->descripcion,
                ],
                'horas' => $horasDecimal,
                'sin_cargo' => $t->sin_cargo,
                'presencial' => $t->presencial,
                'descripcion' => $t->observacion ?? '',
            ];
            if ($incluirEmpleado && $t->usuario) {
                $item['empleado'] = [
                    'id' => $t->usuario->id,
                    'nombre' => $t->usuario->nombre,
                    'code' => $t->usuario->code,
                ];
            }
            return $item;
        })->values()->all();

        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
            'total_horas' => round($totalHoras, 2),
        ];
    }

    /**
     * Reporte agrupado por cliente (TR-046): grupos por cliente con total horas, cantidad y detalle de tareas.
     * Empleado: solo sus tareas. Supervisor: todas. Cliente: solo donde es el cliente.
     *
     * @param User $user Usuario autenticado
     * @param array $filters fecha_desde, fecha_hasta
     * @return array ['grupos' => [...], 'total_general_horas' => float, 'total_general_tareas' => int]
     * @throws \Exception Si período inválido (código ERROR_PERIODO_INVALIDO 1305)
     */
    public function listByClientReport(User $user, array $filters): array
    {
        $empleado = Usuario::where('user_id', $user->id)->first();
        $cliente = Cliente::where('user_id', $user->id)->first();

        $esCliente = $cliente !== null && $empleado === null;
        $esSupervisor = $empleado && (bool) $empleado->supervisor;

        if ($esCliente) {
            $clienteIdFijo = $cliente->id;
        } else {
            if (!$empleado) {
                return [
                    'grupos' => [],
                    'total_general_horas' => 0,
                    'total_general_tareas' => 0,
                ];
            }
        }

        $fechaDesde = isset($filters['fecha_desde']) && $filters['fecha_desde'] !== '' ? $filters['fecha_desde'] : null;
        $fechaHasta = isset($filters['fecha_hasta']) && $filters['fecha_hasta'] !== '' ? $filters['fecha_hasta'] : null;
        if ($fechaDesde !== null && $fechaHasta !== null && $fechaDesde > $fechaHasta) {
            throw new \Exception('El período es inválido: fecha_desde debe ser menor o igual a fecha_hasta', self::ERROR_PERIODO_INVALIDO);
        }

        $query = RegistroTarea::with(['cliente.tipoCliente', 'tipoTarea', 'usuario']);

        if ($esCliente) {
            $query->where('cliente_id', $clienteIdFijo);
        } else {
            if ($esSupervisor) {
                // Supervisor: todas las tareas
            } else {
                $query->where('usuario_id', $empleado->id);
            }
        }

        if ($fechaDesde !== null) {
            $query->where('fecha', '>=', $fechaDesde);
        }
        if ($fechaHasta !== null) {
            $query->where('fecha', '<=', $fechaHasta);
        }

        $registros = $query->orderBy('fecha', 'desc')->get();

        $agrupado = [];
        foreach ($registros as $t) {
            $cid = $t->cliente_id;
            if (!isset($agrupado[$cid])) {
                $agrupado[$cid] = [
                    'cliente_id' => $cid,
                    'nombre' => $t->cliente->nombre,
                    'tipo_cliente' => $t->cliente->tipoCliente ? [
                        'id' => $t->cliente->tipoCliente->id,
                        'descripcion' => $t->cliente->tipoCliente->descripcion,
                    ] : null,
                    'total_horas' => 0,
                    'cantidad_tareas' => 0,
                    'tareas' => [],
                ];
            }
            $horasDecimal = round($t->duracion_minutos / 60, 2);
            $agrupado[$cid]['total_horas'] += $horasDecimal;
            $agrupado[$cid]['cantidad_tareas']++;
            $item = [
                'id' => $t->id,
                'fecha' => $t->fecha->format('Y-m-d'),
                'tipo_tarea' => [
                    'id' => $t->tipoTarea->id,
                    'descripcion' => $t->tipoTarea->descripcion,
                ],
                'horas' => $horasDecimal,
                'descripcion' => $t->observacion ?? '',
            ];
            if ($esSupervisor && !$esCliente && $t->usuario) {
                $item['empleado'] = [
                    'id' => $t->usuario->id,
                    'nombre' => $t->usuario->nombre,
                    'code' => $t->usuario->code,
                ];
            }
            $agrupado[$cid]['tareas'][] = $item;
        }

        $grupos = array_values($agrupado);
        usort($grupos, function ($a, $b) {
            return $b['total_horas'] <=> $a['total_horas'];
        });

        foreach ($grupos as &$g) {
            $g['total_horas'] = round($g['total_horas'], 2);
        }
        unset($g);

        $totalGeneralHoras = array_sum(array_column($grupos, 'total_horas'));
        $totalGeneralTareas = array_sum(array_column($grupos, 'cantidad_tareas'));

        return [
            'grupos' => $grupos,
            'total_general_horas' => round($totalGeneralHoras, 2),
            'total_general_tareas' => (int) $totalGeneralTareas,
        ];
    }

    /**
     * Reporte agrupado por empleado (TR-045): grupos por usuario_id con total horas, cantidad y detalle de tareas.
     * Solo supervisores (el controlador verifica y devuelve 403 si no).
     *
     * @param User $user Usuario autenticado (debe ser supervisor)
     * @param array $filters fecha_desde, fecha_hasta, tipo_cliente_id, cliente_id, usuario_id (opcional)
     * @return array ['grupos' => [...], 'total_general_horas' => float, 'total_general_tareas' => int]
     * @throws \Exception Si período inválido (código ERROR_PERIODO_INVALIDO 1305)
     */
    public function listByEmployeeReport(User $user, array $filters): array
    {
        $fechaDesde = isset($filters['fecha_desde']) && $filters['fecha_desde'] !== '' ? $filters['fecha_desde'] : null;
        $fechaHasta = isset($filters['fecha_hasta']) && $filters['fecha_hasta'] !== '' ? $filters['fecha_hasta'] : null;
        if ($fechaDesde !== null && $fechaHasta !== null && $fechaDesde > $fechaHasta) {
            throw new \Exception('El período es inválido: fecha_desde debe ser menor o igual a fecha_hasta', self::ERROR_PERIODO_INVALIDO);
        }

        $query = RegistroTarea::with(['cliente', 'tipoTarea', 'usuario']);

        if ($fechaDesde !== null) {
            $query->where('fecha', '>=', $fechaDesde);
        }
        if ($fechaHasta !== null) {
            $query->where('fecha', '<=', $fechaHasta);
        }
        if (isset($filters['cliente_id']) && $filters['cliente_id'] !== '' && $filters['cliente_id'] !== null) {
            $query->where('cliente_id', (int) $filters['cliente_id']);
        }
        if (isset($filters['tipo_cliente_id']) && $filters['tipo_cliente_id'] !== '' && $filters['tipo_cliente_id'] !== null) {
            $query->whereHas('cliente', function ($q) use ($filters) {
                $q->where('tipo_cliente_id', (int) $filters['tipo_cliente_id']);
            });
        }
        if (isset($filters['usuario_id']) && $filters['usuario_id'] !== '' && $filters['usuario_id'] !== null) {
            $query->where('usuario_id', (int) $filters['usuario_id']);
        }

        $registros = $query->orderBy('fecha', 'desc')->get();

        $agrupado = [];
        foreach ($registros as $t) {
            $uid = $t->usuario_id;
            if (!isset($agrupado[$uid])) {
                $nombre = $t->usuario ? $t->usuario->nombre : 'Empleado #' . $uid;
                $code = $t->usuario ? $t->usuario->code : '';
                $agrupado[$uid] = [
                    'usuario_id' => $uid,
                    'nombre' => $nombre,
                    'code' => $code,
                    'total_horas' => 0,
                    'cantidad_tareas' => 0,
                    'tareas' => [],
                ];
            }
            $horasDecimal = round($t->duracion_minutos / 60, 2);
            $agrupado[$uid]['total_horas'] += $horasDecimal;
            $agrupado[$uid]['cantidad_tareas']++;
            $agrupado[$uid]['tareas'][] = [
                'id' => $t->id,
                'fecha' => $t->fecha->format('Y-m-d'),
                'cliente' => [
                    'id' => $t->cliente->id,
                    'nombre' => $t->cliente->nombre,
                ],
                'tipo_tarea' => [
                    'id' => $t->tipoTarea->id,
                    'descripcion' => $t->tipoTarea->descripcion,
                ],
                'horas' => $horasDecimal,
                'sin_cargo' => $t->sin_cargo,
                'presencial' => $t->presencial,
                'descripcion' => $t->observacion ?? '',
            ];
        }

        $grupos = array_values($agrupado);
        usort($grupos, function ($a, $b) {
            return $b['total_horas'] <=> $a['total_horas'];
        });

        foreach ($grupos as &$g) {
            $g['total_horas'] = round($g['total_horas'], 2);
        }
        unset($g);

        $totalGeneralHoras = array_sum(array_column($grupos, 'total_horas'));
        $totalGeneralTareas = array_sum(array_column($grupos, 'cantidad_tareas'));

        return [
            'grupos' => $grupos,
            'total_general_horas' => round($totalGeneralHoras, 2),
            'total_general_tareas' => (int) $totalGeneralTareas,
        ];
    }

    /**
     * Reporte agrupado por tipo de tarea (TR-047): grupos por tipo_tarea_id con total horas, cantidad y detalle.
     * Solo supervisores (el controlador verifica y devuelve 403 si no).
     *
     * @param User $user Usuario autenticado (debe ser supervisor)
     * @param array $filters fecha_desde, fecha_hasta, tipo_cliente_id, cliente_id, usuario_id (opcional)
     * @return array ['grupos' => [...], 'total_general_horas' => float, 'total_general_tareas' => int]
     * @throws \Exception Si período inválido (código ERROR_PERIODO_INVALIDO 1305)
     */
    public function listByTaskTypeReport(User $user, array $filters): array
    {
        $fechaDesde = isset($filters['fecha_desde']) && $filters['fecha_desde'] !== '' ? $filters['fecha_desde'] : null;
        $fechaHasta = isset($filters['fecha_hasta']) && $filters['fecha_hasta'] !== '' ? $filters['fecha_hasta'] : null;
        if ($fechaDesde !== null && $fechaHasta !== null && $fechaDesde > $fechaHasta) {
            throw new \Exception('El período es inválido: fecha_desde debe ser menor o igual a fecha_hasta', self::ERROR_PERIODO_INVALIDO);
        }

        $query = RegistroTarea::with(['cliente', 'tipoTarea', 'usuario']);

        if ($fechaDesde !== null) {
            $query->where('fecha', '>=', $fechaDesde);
        }
        if ($fechaHasta !== null) {
            $query->where('fecha', '<=', $fechaHasta);
        }
        if (isset($filters['cliente_id']) && $filters['cliente_id'] !== '' && $filters['cliente_id'] !== null) {
            $query->where('cliente_id', (int) $filters['cliente_id']);
        }
        if (isset($filters['tipo_cliente_id']) && $filters['tipo_cliente_id'] !== '' && $filters['tipo_cliente_id'] !== null) {
            $query->whereHas('cliente', function ($q) use ($filters) {
                $q->where('tipo_cliente_id', (int) $filters['tipo_cliente_id']);
            });
        }
        if (isset($filters['usuario_id']) && $filters['usuario_id'] !== '' && $filters['usuario_id'] !== null) {
            $query->where('usuario_id', (int) $filters['usuario_id']);
        }

        $registros = $query->orderBy('fecha', 'desc')->get();

        $agrupado = [];
        foreach ($registros as $t) {
            $tid = $t->tipo_tarea_id;
            if (!isset($agrupado[$tid])) {
                $descripcion = $t->tipoTarea ? $t->tipoTarea->descripcion : 'Tipo #' . $tid;
                $agrupado[$tid] = [
                    'tipo_tarea_id' => $tid,
                    'descripcion' => $descripcion,
                    'total_horas' => 0,
                    'cantidad_tareas' => 0,
                    'tareas' => [],
                ];
            }
            $horasDecimal = round($t->duracion_minutos / 60, 2);
            $agrupado[$tid]['total_horas'] += $horasDecimal;
            $agrupado[$tid]['cantidad_tareas']++;
            $agrupado[$tid]['tareas'][] = [
                'id' => $t->id,
                'fecha' => $t->fecha->format('Y-m-d'),
                'cliente' => [
                    'id' => $t->cliente->id,
                    'nombre' => $t->cliente->nombre,
                ],
                'tipo_tarea' => [
                    'id' => $t->tipoTarea->id,
                    'descripcion' => $t->tipoTarea->descripcion,
                ],
                'horas' => $horasDecimal,
                'sin_cargo' => $t->sin_cargo,
                'presencial' => $t->presencial,
                'descripcion' => $t->observacion ?? '',
            ];
        }

        $grupos = array_values($agrupado);
        usort($grupos, function ($a, $b) {
            return $b['total_horas'] <=> $a['total_horas'];
        });

        foreach ($grupos as &$g) {
            $g['total_horas'] = round($g['total_horas'], 2);
        }
        unset($g);

        $totalGeneralHoras = array_sum(array_column($grupos, 'total_horas'));
        $totalGeneralTareas = array_sum(array_column($grupos, 'cantidad_tareas'));

        return [
            'grupos' => $grupos,
            'total_general_horas' => round($totalGeneralHoras, 2),
            'total_general_tareas' => (int) $totalGeneralTareas,
        ];
    }

    /**
     * Reporte agrupado por fecha (TR-048): grupos por fecha con total horas, cantidad y detalle.
     * Filtros por rol: empleado solo sus tareas, supervisor todas, cliente solo donde es el cliente.
     *
     * @param User $user Usuario autenticado
     * @param array $filters fecha_desde, fecha_hasta
     * @return array ['grupos' => [...], 'total_general_horas' => float, 'total_general_tareas' => int]
     * @throws \Exception Si período inválido (ERROR_PERIODO_INVALIDO 1305)
     */
    public function listByDateReport(User $user, array $filters): array
    {
        $empleado = Usuario::where('user_id', $user->id)->first();
        $cliente = Cliente::where('user_id', $user->id)->first();

        $esCliente = $cliente !== null && $empleado === null;
        $esSupervisor = $empleado && (bool) $empleado->supervisor;

        if ($esCliente) {
            $clienteIdFijo = $cliente->id;
        } else {
            if (!$empleado) {
                return [
                    'grupos' => [],
                    'total_general_horas' => 0,
                    'total_general_tareas' => 0,
                ];
            }
        }

        $fechaDesde = isset($filters['fecha_desde']) && $filters['fecha_desde'] !== '' ? $filters['fecha_desde'] : null;
        $fechaHasta = isset($filters['fecha_hasta']) && $filters['fecha_hasta'] !== '' ? $filters['fecha_hasta'] : null;
        if ($fechaDesde !== null && $fechaHasta !== null && $fechaDesde > $fechaHasta) {
            throw new \Exception('El período es inválido: fecha_desde debe ser menor o igual a fecha_hasta', self::ERROR_PERIODO_INVALIDO);
        }

        $query = RegistroTarea::with(['cliente', 'tipoTarea', 'usuario']);

        if ($esCliente) {
            $query->where('cliente_id', $clienteIdFijo);
        } else {
            if (!$esSupervisor) {
                $query->where('usuario_id', $empleado->id);
            }
        }

        if ($fechaDesde !== null) {
            $query->where('fecha', '>=', $fechaDesde);
        }
        if ($fechaHasta !== null) {
            $query->where('fecha', '<=', $fechaHasta);
        }

        $registros = $query->orderBy('fecha', 'desc')->get();

        $agrupado = [];
        foreach ($registros as $t) {
            $fechaStr = $t->fecha->format('Y-m-d');
            if (!isset($agrupado[$fechaStr])) {
                $agrupado[$fechaStr] = [
                    'fecha' => $fechaStr,
                    'total_horas' => 0,
                    'cantidad_tareas' => 0,
                    'tareas' => [],
                ];
            }
            $horasDecimal = round($t->duracion_minutos / 60, 2);
            $agrupado[$fechaStr]['total_horas'] += $horasDecimal;
            $agrupado[$fechaStr]['cantidad_tareas']++;
            $agrupado[$fechaStr]['tareas'][] = [
                'id' => $t->id,
                'fecha' => $t->fecha->format('Y-m-d'),
                'cliente' => [
                    'id' => $t->cliente->id,
                    'nombre' => $t->cliente->nombre,
                ],
                'tipo_tarea' => [
                    'id' => $t->tipoTarea->id,
                    'descripcion' => $t->tipoTarea->descripcion,
                ],
                'horas' => $horasDecimal,
                'sin_cargo' => $t->sin_cargo,
                'presencial' => $t->presencial,
                'descripcion' => $t->observacion ?? '',
            ];
        }

        $grupos = array_values($agrupado);
        usort($grupos, function ($a, $b) {
            return $b['fecha'] <=> $a['fecha'];
        });

        foreach ($grupos as &$g) {
            $g['total_horas'] = round($g['total_horas'], 2);
        }
        unset($g);

        $totalGeneralHoras = array_sum(array_column($grupos, 'total_horas'));
        $totalGeneralTareas = array_sum(array_column($grupos, 'cantidad_tareas'));

        return [
            'grupos' => $grupos,
            'total_general_horas' => round($totalGeneralHoras, 2),
            'total_general_tareas' => (int) $totalGeneralTareas,
        ];
    }

    /** Top N para dashboard (TR-051) */
    private const DASHBOARD_TOP_N = 10;

    /**
     * Datos del dashboard (TR-051): KPIs, top clientes, top empleados (supervisor), distribución por tipo (cliente).
     *
     * @param User $user Usuario autenticado
     * @param array $filters fecha_desde, fecha_hasta
     * @return array total_horas, cantidad_tareas, promedio_horas_por_dia, top_clientes, top_empleados?, distribucion_por_tipo?
     * @throws \Exception Si período inválido (ERROR_PERIODO_INVALIDO 1305)
     */
    public function getDashboardData(User $user, array $filters): array
    {
        $empleado = Usuario::where('user_id', $user->id)->first();
        $cliente = Cliente::where('user_id', $user->id)->first();

        $esCliente = $cliente !== null && $empleado === null;
        $esSupervisor = $empleado && (bool) $empleado->supervisor;

        if ($esCliente) {
            $clienteIdFijo = $cliente->id;
        } else {
            if (!$empleado) {
                return $this->emptyDashboardResponse();
            }
        }

        $fechaDesde = isset($filters['fecha_desde']) && $filters['fecha_desde'] !== '' ? $filters['fecha_desde'] : null;
        $fechaHasta = isset($filters['fecha_hasta']) && $filters['fecha_hasta'] !== '' ? $filters['fecha_hasta'] : null;
        if ($fechaDesde !== null && $fechaHasta !== null && $fechaDesde > $fechaHasta) {
            throw new \Exception('El período es inválido: fecha_desde debe ser menor o igual a fecha_hasta', self::ERROR_PERIODO_INVALIDO);
        }

        $query = RegistroTarea::with(['cliente', 'tipoTarea', 'usuario']);

        if ($esCliente) {
            $query->where('cliente_id', $clienteIdFijo);
        } else {
            if (!$esSupervisor) {
                $query->where('usuario_id', $empleado->id);
            }
        }

        if ($fechaDesde !== null) {
            $query->where('fecha', '>=', $fechaDesde);
        }
        if ($fechaHasta !== null) {
            $query->where('fecha', '<=', $fechaHasta);
        }

        $registros = $query->get();

        $totalMinutos = $registros->sum('duracion_minutos');
        $totalHoras = round($totalMinutos / 60, 2);
        $cantidadTareas = $registros->count();

        $diasRango = 1;
        if ($fechaDesde && $fechaHasta) {
            $from = \Carbon\Carbon::parse($fechaDesde);
            $to = \Carbon\Carbon::parse($fechaHasta);
            $diasRango = max(1, $from->diffInDays($to) + 1);
        }
        $promedioHorasPorDia = $cantidadTareas > 0 ? round($totalHoras / $diasRango, 2) : 0;

        $topClientes = [];
        $porCliente = [];
        foreach ($registros as $t) {
            $cid = $t->cliente_id;
            if (!isset($porCliente[$cid])) {
                $porCliente[$cid] = ['cliente_id' => $cid, 'nombre' => $t->cliente->nombre, 'total_horas' => 0, 'cantidad_tareas' => 0];
            }
            $porCliente[$cid]['total_horas'] += $t->duracion_minutos / 60;
            $porCliente[$cid]['cantidad_tareas']++;
        }
        $topClientesList = array_values($porCliente);
        usort($topClientesList, fn ($a, $b) => $b['total_horas'] <=> $a['total_horas']);
        foreach (array_slice($topClientesList, 0, self::DASHBOARD_TOP_N) as $c) {
            $topClientes[] = [
                'cliente_id' => $c['cliente_id'],
                'nombre' => $c['nombre'],
                'total_horas' => round($c['total_horas'], 2),
                'cantidad_tareas' => $c['cantidad_tareas'],
                'porcentaje' => $totalHoras > 0 ? round($c['total_horas'] / $totalHoras * 100, 1) : 0,
            ];
        }

        $topEmpleados = [];
        if ($esSupervisor && !$esCliente) {
            $porEmpleado = [];
            foreach ($registros as $t) {
                $uid = $t->usuario_id;
                if (!isset($porEmpleado[$uid])) {
                    $porEmpleado[$uid] = [
                        'usuario_id' => $uid,
                        'nombre' => $t->usuario ? $t->usuario->nombre : '',
                        'code' => $t->usuario ? $t->usuario->code : '',
                        'total_horas' => 0,
                        'cantidad_tareas' => 0,
                    ];
                }
                $porEmpleado[$uid]['total_horas'] += $t->duracion_minutos / 60;
                $porEmpleado[$uid]['cantidad_tareas']++;
            }
            $topEmpleadosList = array_values($porEmpleado);
            usort($topEmpleadosList, fn ($a, $b) => $b['total_horas'] <=> $a['total_horas']);
            foreach (array_slice($topEmpleadosList, 0, self::DASHBOARD_TOP_N) as $e) {
                $topEmpleados[] = [
                    'usuario_id' => $e['usuario_id'],
                    'nombre' => $e['nombre'],
                    'code' => $e['code'],
                    'total_horas' => round($e['total_horas'], 2),
                    'cantidad_tareas' => $e['cantidad_tareas'],
                    'porcentaje' => $totalHoras > 0 ? round($e['total_horas'] / $totalHoras * 100, 1) : 0,
                ];
            }
        }

        $distribucionPorTipo = [];
        if ($esCliente) {
            $porTipo = [];
            foreach ($registros as $t) {
                $tid = $t->tipo_tarea_id;
                if (!isset($porTipo[$tid])) {
                    $porTipo[$tid] = [
                        'tipo_tarea_id' => $tid,
                        'descripcion' => $t->tipoTarea ? $t->tipoTarea->descripcion : '',
                        'total_horas' => 0,
                        'cantidad_tareas' => 0,
                    ];
                }
                $porTipo[$tid]['total_horas'] += $t->duracion_minutos / 60;
                $porTipo[$tid]['cantidad_tareas']++;
            }
            foreach (array_values($porTipo) as $d) {
                $distribucionPorTipo[] = [
                    'tipo_tarea_id' => $d['tipo_tarea_id'],
                    'descripcion' => $d['descripcion'],
                    'total_horas' => round($d['total_horas'], 2),
                    'cantidad_tareas' => $d['cantidad_tareas'],
                ];
            }
        }

        return [
            'total_horas' => $totalHoras,
            'cantidad_tareas' => $cantidadTareas,
            'promedio_horas_por_dia' => $promedioHorasPorDia,
            'top_clientes' => $topClientes,
            'top_empleados' => $topEmpleados,
            'distribucion_por_tipo' => $distribucionPorTipo,
        ];
    }

    private function emptyDashboardResponse(): array
    {
        return [
            'total_horas' => 0,
            'cantidad_tareas' => 0,
            'promedio_horas_por_dia' => 0,
            'top_clientes' => [],
            'top_empleados' => [],
            'distribucion_por_tipo' => [],
        ];
    }

    /**
     * Crear un nuevo registro de tarea
     *
     * @param array $datos Datos validados del request
     * @param User $user Usuario autenticado
     * @return array Datos del registro creado
     * @throws \Exception Si hay error en la creación
     */
    public function createTask(array $datos, User $user): array
    {
        // Obtener empleado del usuario autenticado
        $empleadoAutenticado = Usuario::where('user_id', $user->id)->first();
        
        if (!$empleadoAutenticado) {
            throw new \Exception('El usuario autenticado no es un empleado', self::ERROR_FORBIDDEN);
        }

        // Validar cliente activo y no inhabilitado
        $cliente = Cliente::find($datos['cliente_id']);
        if (!$cliente) {
            throw new \Exception('Cliente no encontrado', 404);
        }
        if (!$cliente->activo || $cliente->inhabilitado) {
            throw new \Exception('El cliente seleccionado está inactivo o inhabilitado', self::ERROR_CLIENTE_INACTIVO);
        }

        // Validar tipo de tarea activo y no inhabilitado
        $tipoTarea = TipoTarea::find($datos['tipo_tarea_id']);
        if (!$tipoTarea) {
            throw new \Exception('Tipo de tarea no encontrado', 404);
        }
        if (!$tipoTarea->activo || $tipoTarea->inhabilitado) {
            throw new \Exception('El tipo de tarea seleccionado está inactivo o inhabilitado', self::ERROR_TIPO_TAREA_INACTIVO);
        }

        // Validar que el tipo de tarea sea genérico o esté asignado al cliente
        if (!$tipoTarea->is_generico) {
            $asignado = ClienteTipoTarea::where('cliente_id', $cliente->id)
                ->where('tipo_tarea_id', $tipoTarea->id)
                ->exists();
            
            if (!$asignado) {
                throw new \Exception('El tipo de tarea no está disponible para el cliente seleccionado', self::ERROR_TIPO_TAREA_NO_DISPONIBLE);
            }
        }

        // Determinar usuario_id final
        $usuarioIdFinal = $empleadoAutenticado->id;

        // Si se proporciona usuario_id, validar permisos y empleado
        if (isset($datos['usuario_id']) && $datos['usuario_id'] !== null) {
            // Validar que el usuario autenticado sea supervisor
            if (!$empleadoAutenticado->supervisor) {
                throw new \Exception('No tiene permisos para asignar tareas a otros empleados', self::ERROR_FORBIDDEN);
            }

            // Validar que el empleado seleccionado existe y está activo
            $empleadoSeleccionado = Usuario::find($datos['usuario_id']);
            if (!$empleadoSeleccionado) {
                throw new \Exception('Empleado no encontrado', 404);
            }
            if (!$empleadoSeleccionado->activo || $empleadoSeleccionado->inhabilitado) {
                throw new \Exception('El empleado seleccionado está inactivo o inhabilitado', self::ERROR_EMPLEADO_INACTIVO);
            }

            $usuarioIdFinal = $empleadoSeleccionado->id;
        }

        // Crear el registro de tarea
        try {
            $registroTarea = RegistroTarea::create([
                'usuario_id' => $usuarioIdFinal,
                'cliente_id' => $datos['cliente_id'],
                'tipo_tarea_id' => $datos['tipo_tarea_id'],
                'fecha' => $datos['fecha'],
                'duracion_minutos' => $datos['duracion_minutos'],
                'sin_cargo' => $datos['sin_cargo'] ?? false,
                'presencial' => $datos['presencial'] ?? false,
                'observacion' => trim($datos['observacion']),
                'cerrado' => false,
            ]);

            // Retornar datos del registro creado
            return [
                'id' => $registroTarea->id,
                'usuario_id' => $registroTarea->usuario_id,
                'cliente_id' => $registroTarea->cliente_id,
                'tipo_tarea_id' => $registroTarea->tipo_tarea_id,
                'fecha' => $registroTarea->fecha->format('Y-m-d'),
                'duracion_minutos' => $registroTarea->duracion_minutos,
                'sin_cargo' => $registroTarea->sin_cargo,
                'presencial' => $registroTarea->presencial,
                'observacion' => $registroTarea->observacion,
                'cerrado' => $registroTarea->cerrado,
                'created_at' => $registroTarea->created_at->toIso8601String(),
                'updated_at' => $registroTarea->updated_at->toIso8601String(),
            ];
        } catch (\Exception $e) {
            Log::error('Error al crear registro de tarea', [
                'user_id' => $user->id,
                'datos' => $datos,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception('Error inesperado al crear el registro de tarea', 500);
        }
    }

    /**
     * Obtener una tarea por ID para edición (solo si pertenece al usuario o es supervisor)
     *
     * @param int $id ID de la tarea
     * @param User $user Usuario autenticado
     * @return array Datos de la tarea para el formulario de edición
     * @throws \Exception Si no existe (404), está cerrada (2110) o sin permisos (4030)
     */
    public function getTask(int $id, User $user): array
    {
        $registro = RegistroTarea::with(['cliente', 'tipoTarea', 'usuario'])->find($id);

        if (!$registro) {
            throw new \Exception('Tarea no encontrada', 404);
        }

        if ($registro->cerrado) {
            throw new \Exception('No se puede modificar una tarea cerrada', self::ERROR_CLOSED);
        }

        $empleado = Usuario::where('user_id', $user->id)->first();
        if (!$empleado) {
            throw new \Exception('No tiene permisos para acceder a esta tarea', self::ERROR_FORBIDDEN_EDIT);
        }

        $esSupervisor = (bool) $empleado->supervisor;
        if (!$esSupervisor && (int) $registro->usuario_id !== (int) $empleado->id) {
            throw new \Exception('No tiene permisos para acceder a esta tarea', self::ERROR_FORBIDDEN_EDIT);
        }

        return [
            'id' => $registro->id,
            'usuario_id' => $registro->usuario_id,
            'usuario_nombre' => $registro->usuario ? $registro->usuario->nombre : '',
            'cliente_id' => $registro->cliente_id,
            'tipo_tarea_id' => $registro->tipo_tarea_id,
            'fecha' => $registro->fecha->format('Y-m-d'),
            'duracion_minutos' => $registro->duracion_minutos,
            'sin_cargo' => $registro->sin_cargo,
            'presencial' => $registro->presencial,
            'observacion' => $registro->observacion,
            'cerrado' => $registro->cerrado,
        ];
    }

    /**
     * Actualizar una tarea existente (solo propias no cerradas, o cualquiera no cerrada si es supervisor).
     * TR-031: Si es supervisor, puede enviar usuario_id para cambiar el propietario.
     *
     * @param int $id ID de la tarea
     * @param array $datos Datos validados (fecha, cliente_id, tipo_tarea_id, duracion_minutos, sin_cargo, presencial, observacion [, usuario_id solo supervisor])
     * @param User $user Usuario autenticado
     * @return array Datos del registro actualizado
     * @throws \Exception Si no existe, está cerrada, sin permisos o validación de negocio
     */
    public function updateTask(int $id, array $datos, User $user): array
    {
        $registro = RegistroTarea::with(['cliente', 'tipoTarea'])->find($id);

        if (!$registro) {
            throw new \Exception('Tarea no encontrada', 404);
        }

        if ($registro->cerrado) {
            throw new \Exception('No se puede modificar una tarea cerrada', self::ERROR_CLOSED);
        }

        $empleado = Usuario::where('user_id', $user->id)->first();
        if (!$empleado) {
            throw new \Exception('No tiene permisos para editar esta tarea', self::ERROR_FORBIDDEN_EDIT);
        }

        $esSupervisor = (bool) $empleado->supervisor;
        if (!$esSupervisor && (int) $registro->usuario_id !== (int) $empleado->id) {
            throw new \Exception('No tiene permisos para editar esta tarea', self::ERROR_FORBIDDEN_EDIT);
        }

        // TR-031: Si se envía usuario_id, solo el supervisor puede cambiar el propietario
        if (array_key_exists('usuario_id', $datos) && $datos['usuario_id'] !== null) {
            if (!$esSupervisor) {
                throw new \Exception('Solo los supervisores pueden cambiar el propietario de una tarea', self::ERROR_FORBIDDEN_EDIT);
            }
            $empleadoSeleccionado = Usuario::find($datos['usuario_id']);
            if (!$empleadoSeleccionado) {
                throw new \Exception('El empleado seleccionado no existe o no está activo', self::ERROR_EMPLEADO_INACTIVO);
            }
            if (!$empleadoSeleccionado->activo || $empleadoSeleccionado->inhabilitado) {
                throw new \Exception('El empleado seleccionado no existe o no está activo', self::ERROR_EMPLEADO_INACTIVO);
            }
            $registro->usuario_id = $empleadoSeleccionado->id;
        }

        // Validar cliente activo
        $cliente = Cliente::find($datos['cliente_id']);
        if (!$cliente) {
            throw new \Exception('Cliente no encontrado', 404);
        }
        if (!$cliente->activo || $cliente->inhabilitado) {
            throw new \Exception('El cliente seleccionado está inactivo o inhabilitado', self::ERROR_CLIENTE_INACTIVO);
        }

        // Validar tipo de tarea
        $tipoTarea = TipoTarea::find($datos['tipo_tarea_id']);
        if (!$tipoTarea) {
            throw new \Exception('Tipo de tarea no encontrado', 404);
        }
        if (!$tipoTarea->activo || $tipoTarea->inhabilitado) {
            throw new \Exception('El tipo de tarea seleccionado está inactivo o inhabilitado', self::ERROR_TIPO_TAREA_INACTIVO);
        }
        if (!$tipoTarea->is_generico) {
            $asignado = ClienteTipoTarea::where('cliente_id', $cliente->id)
                ->where('tipo_tarea_id', $tipoTarea->id)
                ->exists();
            if (!$asignado) {
                throw new \Exception('El tipo de tarea no está disponible para el cliente seleccionado', self::ERROR_TIPO_TAREA_NO_DISPONIBLE);
            }
        }

        $registro->fecha = $datos['fecha'];
        $registro->cliente_id = $datos['cliente_id'];
        $registro->tipo_tarea_id = $datos['tipo_tarea_id'];
        $registro->duracion_minutos = $datos['duracion_minutos'];
        $registro->sin_cargo = $datos['sin_cargo'] ?? false;
        $registro->presencial = $datos['presencial'] ?? false;
        $registro->observacion = trim($datos['observacion']);
        $registro->save();

        return [
            'id' => $registro->id,
            'usuario_id' => $registro->usuario_id,
            'cliente_id' => $registro->cliente_id,
            'tipo_tarea_id' => $registro->tipo_tarea_id,
            'fecha' => $registro->fecha->format('Y-m-d'),
            'duracion_minutos' => $registro->duracion_minutos,
            'sin_cargo' => $registro->sin_cargo,
            'presencial' => $registro->presencial,
            'observacion' => $registro->observacion,
            'cerrado' => $registro->cerrado,
            'updated_at' => $registro->updated_at->toIso8601String(),
        ];
    }

    /**
     * Eliminar una tarea (solo propias no cerradas, o cualquiera no cerrada si es supervisor) – TR-030
     *
     * @param int $id ID de la tarea
     * @param User $user Usuario autenticado
     * @return void
     * @throws \Exception Si no existe (404), está cerrada (2111) o sin permisos (4030)
     */
    public function deleteTask(int $id, User $user): void
    {
        $registro = RegistroTarea::find($id);

        if (!$registro) {
            throw new \Exception('Tarea no encontrada', 404);
        }

        if ($registro->cerrado) {
            throw new \Exception('No se puede eliminar una tarea cerrada', self::ERROR_CLOSED_DELETE);
        }

        $empleado = Usuario::where('user_id', $user->id)->first();
        if (!$empleado) {
            throw new \Exception('No tiene permisos para eliminar esta tarea', self::ERROR_FORBIDDEN_DELETE);
        }

        $esSupervisor = (bool) $empleado->supervisor;
        if (!$esSupervisor && (int) $registro->usuario_id !== (int) $empleado->id) {
            throw new \Exception('No tiene permisos para eliminar esta tarea', self::ERROR_FORBIDDEN_DELETE);
        }

        $registro->delete();
    }
}
