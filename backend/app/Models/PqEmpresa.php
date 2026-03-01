<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/**
 * Modelo: PqEmpresa (Dictionary DB)
 *
 * Catálogo de empresas del sistema (multiempresa).
 * Soporta PQ_Empresa (IDEmpresa, NombreEmpresa) y pq_empresa (id, nombre_empresa).
 *
 * @see docs/modelo-datos/md-diccionario/md-diccionario.md
 */
class PqEmpresa extends Model
{
    protected $table = 'pq_empresa';

    protected $fillable = [
        'nombre_empresa',
        'nombre_bd',
        'habilita',
        'imagen',
        'theme',
    ];

    protected $dateFormat = 'Y-m-d H:i:s';

    public $timestamps = true;

    public function __construct(array $attributes = [])
    {
        if (Schema::hasTable('pq_empresa') && Schema::hasColumn('pq_empresa', 'IDEmpresa')) {
            $this->primaryKey = 'IDEmpresa';
        }
        parent::__construct($attributes);
    }
}
