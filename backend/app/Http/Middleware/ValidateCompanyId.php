<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

/**
 * Valida que X-Company-Id corresponda a una empresa autorizada para el usuario.
 * Si el header está presente y no está autorizado, responde 403.
 *
 * @see docs/04-tareas/001-Seguridad/TR-002-seleccion-empresa.md
 */
class ValidateCompanyId
{
    public function handle(Request $request, Closure $next): Response
    {
        $companyId = $request->header('X-Company-Id');

        if ($companyId === null || $companyId === '') {
            return $next($request);
        }

        $user = $request->user();
        if (!$user) {
            return $next($request);
        }

        if (!$this->userHasAccessToCompany($user->id, (int) $companyId)) {
            return response()->json([
                'error' => 403,
                'respuesta' => 'Empresa no autorizada para este usuario',
                'resultado' => null,
            ], 403);
        }

        return $next($request);
    }

    private function userHasAccessToCompany(int $userId, int $companyId): bool
    {
        if (!Schema::hasTable('pq_permiso') || !Schema::hasTable('pq_empresa')) {
            return false;
        }

        return DB::table('pq_permiso')
            ->where('id_usuario', $userId)
            ->where('id_empresa', $companyId)
            ->exists();
    }
}
