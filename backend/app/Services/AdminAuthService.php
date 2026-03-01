<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Servicio para verificar si un usuario tiene rol de administrador.
 * Admin = tiene algún rol con acceso_total=true en pq_rol (vía pq_permiso).
 *
 * @see docs/04-tareas/001-Seguridad/TR-010-administracion-usuarios.md
 */
class AdminAuthService
{
    public function isAdmin(User $user): bool
    {
        if (!Schema::hasTable('pq_permiso') || !Schema::hasTable('pq_rol')) {
            return false;
        }

        return DB::table('pq_permiso as p')
            ->join('pq_rol as r', 'p.id_rol', '=', 'r.id')
            ->where('p.id_usuario', $user->id)
            ->where('r.acceso_total', true)
            ->exists();
    }
}
