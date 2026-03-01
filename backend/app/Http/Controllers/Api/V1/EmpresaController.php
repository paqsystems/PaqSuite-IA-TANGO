<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller: EmpresaController
 *
 * Endpoint de empresas del usuario autenticado.
 *
 * @see docs/04-tareas/001-Seguridad/TR-002-seleccion-empresa.md
 */
class EmpresaController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {
    }

    /**
     * GET /api/v1/empresas
     * Lista de empresas con permiso del usuario.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'error' => 401,
                'respuesta' => 'No autenticado',
                'resultado' => null,
            ], 401);
        }

        $empresas = $this->authService->getEmpresasDelUsuario($user->id);

        return response()->json([
            'error' => 0,
            'respuesta' => 'OK',
            'resultado' => $empresas->toArray(),
        ]);
    }
}
