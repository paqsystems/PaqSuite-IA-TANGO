<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller: DashboardController
 *
 * Endpoint GET /api/v1/dashboard (TR-051).
 * Datos del dashboard: KPIs, top clientes, top empleados (supervisor), distribución por tipo (cliente).
 *
 * @see TR-051(MH)-dashboard-principal.md
 */
class DashboardController extends Controller
{
    public function __construct(
        private TaskService $taskService
    ) {
    }

    /**
     * GET /api/v1/dashboard?fecha_desde=...&fecha_hasta=...
     * Respuesta según rol; error 1305 si período inválido.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $filters = [
                'fecha_desde' => $request->query('fecha_desde'),
                'fecha_hasta' => $request->query('fecha_hasta'),
            ];

            $result = $this->taskService->getDashboardData($user, $filters);

            return response()->json([
                'error' => 0,
                'respuesta' => 'Dashboard obtenido correctamente',
                'resultado' => $result,
            ], 200);
        } catch (\Exception $e) {
            if ($e->getCode() === TaskService::ERROR_PERIODO_INVALIDO) {
                return response()->json([
                    'error' => (int) $e->getCode(),
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
}
