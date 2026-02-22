<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo: TipoCliente
 * 
 * Tabla física: PQ_PARTES_TIPOS_CLIENTE
 * 
 * Catálogo de tipos de cliente (ej: Corporativo, PyME, Startup, Gobierno).
 * 
 * @property int $id
 * @property string $code Código único del tipo de cliente
 * @property string $descripcion Descripción del tipo de cliente
 * @property bool $activo Indica si está activo
 * @property bool $inhabilitado Indica si está inhabilitado
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @see docs/modelo-datos.md
 * @see TR-00(MH)-Generacion-base-datos-inicial.md
 */
class TipoCliente extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla
     */
    protected $table = 'PQ_PARTES_TIPOS_CLIENTE';

    /**
     * Campos que pueden ser asignados masivamente
     */
    protected $fillable = [
        'code',
        'descripcion',
        'activo',
        'inhabilitado',
    ];

    /**
     * Casts de tipos de datos
     */
    protected $casts = [
        'activo' => 'boolean',
        'inhabilitado' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Formato de fecha para SQL Server
     * @see .cursor/rules/20-sql-server-datetime-format.md
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * Valores por defecto
     */
    protected $attributes = [
        'activo' => true,
        'inhabilitado' => false,
    ];

    /**
     * Relación: Un TipoCliente tiene muchos Clientes
     */
    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class, 'tipo_cliente_id');
    }

    /**
     * Verificar si el tipo está habilitado
     */
    public function isHabilitado(): bool
    {
        return $this->activo && !$this->inhabilitado;
    }

    /**
     * Scope para tipos habilitados
     */
    public function scopeHabilitados($query)
    {
        return $query->where('activo', true)
                     ->where('inhabilitado', false);
    }
}
