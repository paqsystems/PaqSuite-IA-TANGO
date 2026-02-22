<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo: ClienteTipoTarea
 * 
 * Tabla física: PQ_PARTES_CLIENTE_TIPO_TAREA
 * 
 * Tabla de asociación N:M entre Cliente y TipoTarea.
 * Permite asignar tipos de tarea específicos a clientes 
 * (cuando el tipo NO es genérico).
 * 
 * @property int $id
 * @property int $cliente_id FK → PQ_PARTES_CLIENTES
 * @property int $tipo_tarea_id FK → PQ_PARTES_TIPOS_TAREA
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @see docs/modelo-datos.md
 * @see TR-00(MH)-Generacion-base-datos-inicial.md
 */
class ClienteTipoTarea extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla
     */
    protected $table = 'PQ_PARTES_CLIENTE_TIPO_TAREA';

    /**
     * Campos que pueden ser asignados masivamente
     */
    protected $fillable = [
        'cliente_id',
        'tipo_tarea_id',
    ];

    /**
     * Casts de tipos de datos
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Formato de fecha para SQL Server
     * @see .cursor/rules/20-sql-server-datetime-format.md
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * Relación: Pertenece a un Cliente
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /**
     * Relación: Pertenece a un TipoTarea
     */
    public function tipoTarea(): BelongsTo
    {
        return $this->belongsTo(TipoTarea::class, 'tipo_tarea_id');
    }
}
