<?php

namespace App\Http\Middleware;

use App\Services\AdminAuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exige que el usuario autenticado tenga rol de administrador (acceso_total).
 *
 * @see docs/04-tareas/001-Seguridad/TR-010-administracion-usuarios.md
 */
class RequireAdmin
{
    public function __construct(
        private AdminAuthService $adminAuthService
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'error' => 401,
                'respuesta' => 'No autenticado',
                'resultado' => (object) [],
            ], 401);
        }

        if (!$this->adminAuthService->isAdmin($user)) {
            return response()->json([
                'error' => 403,
                'respuesta' => 'No autorizado. Se requiere rol de administrador.',
                'resultado' => (object) [],
            ], 403);
        }

        return $next($request);
    }
}
