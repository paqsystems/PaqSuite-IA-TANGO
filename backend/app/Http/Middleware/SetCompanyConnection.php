<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

/**
 * Configura la conexión 'company' con la base de datos de la empresa activa.
 * Debe ejecutarse después de ValidateCompanyId (X-Company-Id validado).
 *
 * @see docs/04-tareas/000-Generalidades/TR-007-Parametros-generales.md
 */
class SetCompanyConnection
{
    public function handle(Request $request, Closure $next): Response
    {
        $companyId = $request->header('X-Company-Id');
        if ($companyId !== null && $companyId !== '') {
            $idCol = Schema::hasColumn('pq_empresa', 'IDEmpresa') ? 'IDEmpresa' : 'id';
            $empresa = DB::connection()->table('pq_empresa')->where($idCol, (int) $companyId)->first();
            $nombreBd = $empresa->NombreBD ?? $empresa->nombre_bd ?? null;
            if ($empresa && ! empty($nombreBd)) {
                Config::set('database.connections.company.database', $nombreBd);
                DB::purge('company');
            }
        }

        return $next($request);
    }
}
