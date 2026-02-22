<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo: Cliente
 * 
 * Tabla física: PQ_PARTES_CLIENTES
 * 
 * Representa a los clientes para los cuales se registran tareas.
 * - Tiene relación 1:1 OPCIONAL con USERS (si el cliente tiene acceso al sistema).
 * - Tiene relación N:1 OBLIGATORIA con PQ_PARTES_TIPOS_CLIENTE.
 * 
 * Si un cliente tiene user_id, puede autenticarse y consultar (solo lectura) 
 * las tareas relacionadas con él.
 * 
 * @property int $id
 * @property int|null $user_id FK → USERS (opcional)
 * @property string $nombre Nombre/Descripción del cliente
 * @property int $tipo_cliente_id FK → PQ_PARTES_TIPOS_CLIENTE
 * @property string $code Código único del cliente
 * @property string|null $email Email del cliente
 * @property bool $activo Indica si está activo
 * @property bool $inhabilitado Indica si está inhabilitado
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @see docs/modelo-datos.md
 * @see TR-00(MH)-Generacion-base-datos-inicial.md
 */
class Cliente extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla
     */
    protected $table = 'PQ_PARTES_CLIENTES';

    /**
     * Campos que pueden ser asignados masivamente
     */
    protected $fillable = [
        'user_id',
        'nombre',
        'tipo_cliente_id',
        'code',
        'email',
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
     * Relación: Un Cliente puede pertenecer a un User
     * Relación 1:1 opcional
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación: Un Cliente pertenece a un TipoCliente
     * Relación N:1 obligatoria
     */
    public function tipoCliente(): BelongsTo
    {
        return $this->belongsTo(TipoCliente::class, 'tipo_cliente_id');
    }

    /**
     * Relación: Un Cliente tiene muchos RegistroTarea
     */
    public function registrosTarea(): HasMany
    {
        return $this->hasMany(RegistroTarea::class, 'cliente_id');
    }

    /**
     * Relación: Un Cliente puede tener muchos TiposTarea asignados
     * (a través de la tabla intermedia PQ_PARTES_CLIENTE_TIPO_TAREA)
     */
    public function tiposTarea(): BelongsToMany
    {
        return $this->belongsToMany(
            TipoTarea::class,
            'PQ_PARTES_CLIENTE_TIPO_TAREA',
            'cliente_id',
            'tipo_tarea_id'
        )->withTimestamps();
    }

    /**
     * Verificar si el cliente tiene acceso al sistema
     */
    public function tieneAccesoSistema(): bool
    {
        return $this->user_id !== null;
    }

    /**
     * Verificar si el cliente está habilitado
     */
    public function isHabilitado(): bool
    {
        return $this->activo && !$this->inhabilitado;
    }

    /**
     * Scope para clientes habilitados
     */
    public function scopeHabilitados($query)
    {
        return $query->where('activo', true)
                     ->where('inhabilitado', false);
    }

    /**
     * Scope para clientes con acceso al sistema
     */
    public function scopeConAcceso($query)
    {
        return $query->whereNotNull('user_id');
    }

    /**
     * Obtener tipos de tarea disponibles para este cliente
     * (genéricos + asignados específicamente)
     */
    public function getTiposTareaDisponibles()
    {
        $genericos = TipoTarea::habilitados()->genericos()->get();
        $asignados = $this->tiposTarea()->habilitados()->get();
        
        return $genericos->merge($asignados)->unique('id');
    }
}
