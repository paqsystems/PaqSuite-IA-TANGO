<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo: Usuario (Empleado/Asistente/Agente)
 * 
 * Tabla física: PQ_PARTES_USUARIOS
 * 
 * Representa a los empleados que cargan las tareas al sistema.
 * Tiene relación 1:1 obligatoria con la tabla USERS.
 * 
 * Permisos según tipo de usuario:
 * - Usuario normal (supervisor=false): Solo puede gestionar sus propias tareas
 * - Supervisor (supervisor=true): Puede gestionar tareas de cualquier usuario
 * 
 * @property int $id
 * @property int $user_id FK → USERS
 * @property string $code Código de usuario (debe coincidir con User.code)
 * @property string $nombre Nombre completo del empleado
 * @property string|null $email Email del empleado
 * @property bool $supervisor Indica si es supervisor
 * @property bool $activo Indica si está activo
 * @property bool $inhabilitado Indica si está inhabilitado
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @see docs/modelo-datos.md
 * @see TR-00(MH)-Generacion-base-datos-inicial.md
 */
class Usuario extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla
     */
    protected $table = 'PQ_PARTES_USUARIOS';

    /**
     * Campos que pueden ser asignados masivamente
     */
    protected $fillable = [
        'user_id',
        'code',
        'nombre',
        'email',
        'supervisor',
        'activo',
        'inhabilitado',
    ];

    /**
     * Casts de tipos de datos
     */
    protected $casts = [
        'supervisor' => 'boolean',
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
        'supervisor' => false,
        'activo' => true,
        'inhabilitado' => false,
    ];

    /**
     * Relación: Un Usuario pertenece a un User
     * Relación 1:1 obligatoria
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación: Un Usuario tiene muchos RegistroTarea
     */
    public function registrosTarea(): HasMany
    {
        return $this->hasMany(RegistroTarea::class, 'usuario_id');
    }

    /**
     * Verificar si el usuario está habilitado
     */
    public function isHabilitado(): bool
    {
        return $this->activo && !$this->inhabilitado;
    }

    /**
     * Verificar si el usuario es supervisor
     */
    public function isSupervisor(): bool
    {
        return $this->supervisor === true;
    }

    /**
     * Scope para usuarios habilitados
     */
    public function scopeHabilitados($query)
    {
        return $query->where('activo', true)
                     ->where('inhabilitado', false);
    }

    /**
     * Scope para supervisores
     */
    public function scopeSupervisores($query)
    {
        return $query->where('supervisor', true);
    }
}
