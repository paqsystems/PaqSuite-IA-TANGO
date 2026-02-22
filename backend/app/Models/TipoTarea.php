<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo: TipoTarea
 * 
 * Tabla física: PQ_PARTES_TIPOS_TAREA
 * 
 * Catálogo de tipos de tarea.
 * - is_generico: Si es true, está disponible para todos los clientes.
 * - is_default: Si es true, es el tipo de tarea predeterminado del sistema.
 * 
 * Reglas de negocio:
 * - Solo puede existir un TipoTarea con is_default=true en todo el sistema.
 * - Si is_default=true, entonces is_generico=true (forzado).
 * 
 * @property int $id
 * @property string $code Código único del tipo de tarea
 * @property string $descripcion Descripción del tipo de tarea
 * @property bool $is_generico Indica si está disponible para todos los clientes
 * @property bool $is_default Indica si es el tipo predeterminado
 * @property bool $activo Indica si está activo
 * @property bool $inhabilitado Indica si está inhabilitado
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @see docs/modelo-datos.md
 * @see TR-00(MH)-Generacion-base-datos-inicial.md
 */
class TipoTarea extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla
     */
    protected $table = 'PQ_PARTES_TIPOS_TAREA';

    /**
     * Campos que pueden ser asignados masivamente
     */
    protected $fillable = [
        'code',
        'descripcion',
        'is_generico',
        'is_default',
        'activo',
        'inhabilitado',
    ];

    /**
     * Casts de tipos de datos
     */
    protected $casts = [
        'is_generico' => 'boolean',
        'is_default' => 'boolean',
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
        'is_generico' => false,
        'is_default' => false,
        'activo' => true,
        'inhabilitado' => false,
    ];

    /**
     * Relación: Un TipoTarea tiene muchos RegistroTarea
     */
    public function registrosTarea(): HasMany
    {
        return $this->hasMany(RegistroTarea::class, 'tipo_tarea_id');
    }

    /**
     * Relación: Un TipoTarea puede estar asignado a muchos Clientes
     * (a través de la tabla intermedia PQ_PARTES_CLIENTE_TIPO_TAREA)
     */
    public function clientes(): BelongsToMany
    {
        return $this->belongsToMany(
            Cliente::class,
            'PQ_PARTES_CLIENTE_TIPO_TAREA',
            'tipo_tarea_id',
            'cliente_id'
        )->withTimestamps();
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

    /**
     * Scope para tipos genéricos
     */
    public function scopeGenericos($query)
    {
        return $query->where('is_generico', true);
    }

    /**
     * Obtener el tipo de tarea por defecto
     */
    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->first();
    }
}
