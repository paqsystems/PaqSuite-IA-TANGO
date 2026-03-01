<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Modelo: PqGrupoEmpresario (Dictionary DB)
 *
 * @see docs/04-tareas/002-GruposEmpresarios/TR-001-listado-grupos-empresarios.md
 */
class PqGrupoEmpresario extends Model
{
    protected $table = 'pq_grupo_empresario';

    protected $fillable = ['descripcion'];

    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * Relación N:M con empresas vía tabla pivote pq_grupo_empresario_empresas.
     * Usa modelo dinámico para pq_empresa (sin clase dedicada).
     */
    public function empresas(): BelongsToMany
    {
        $empresaKey = \Illuminate\Support\Facades\Schema::hasColumn('pq_empresa', 'IDEmpresa') ? 'IDEmpresa' : 'id';
        return $this->belongsToMany(
            PqEmpresa::class,
            'pq_grupo_empresario_empresas',
            'id_grupo',
            'id_empresa',
            'id',
            $empresaKey
        );
    }
}
