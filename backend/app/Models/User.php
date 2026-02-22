<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Modelo: User (Tabla de Autenticación Centralizada)
 * 
 * Tabla física: USERS (ÚNICA tabla SIN prefijo PQ_PARTES_)
 * 
 * Esta es la tabla central de autenticación del sistema.
 * Después de un login exitoso, se determina si el User corresponde
 * a un Cliente (PQ_PARTES_CLIENTES) o a un Usuario (PQ_PARTES_USUARIOS).
 * 
 * Flujo de autenticación:
 * 1. Login se valida contra USERS usando code y password_hash
 * 2. Si exitoso, se busca User.code en PQ_PARTES_CLIENTES o PQ_PARTES_USUARIOS
 * 3. Se determina el tipo de usuario (cliente/empleado) y rol (supervisor)
 * 
 * @property int $id
 * @property string $code Código de usuario para autenticación
 * @property string $password_hash Hash de contraseña
 * @property bool $activo Indica si el usuario está activo
 * @property bool $inhabilitado Indica si el usuario está inhabilitado
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @see docs/modelo-datos.md
 * @see TR-00(MH)-Generacion-base-datos-inicial.md
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    /**
     * Nombre de la tabla (sin prefijo PQ_PARTES_)
     */
    protected $table = 'USERS';

    /**
     * Campos que pueden ser asignados masivamente
     */
    protected $fillable = [
        'code',
        'password_hash',
        'activo',
        'inhabilitado',
    ];

    /**
     * Campos ocultos en serialización
     */
    protected $hidden = [
        'password_hash',
        'remember_token',
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
     * Relación: Un User puede tener un Usuario (empleado)
     * Relación 1:1 opcional
     */
    public function usuario(): HasOne
    {
        return $this->hasOne(Usuario::class, 'user_id');
    }

    /**
     * Relación: Un User puede tener un Cliente
     * Relación 1:1 opcional
     */
    public function cliente(): HasOne
    {
        return $this->hasOne(Cliente::class, 'user_id');
    }

    /**
     * Verificar si el usuario está habilitado para autenticarse
     */
    public function isHabilitado(): bool
    {
        return $this->activo && !$this->inhabilitado;
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
     * Obtener el tipo de usuario después del login
     * 
     * @return string|null 'usuario', 'cliente', o null si no tiene perfil
     */
    public function getTipoUsuario(): ?string
    {
        if ($this->usuario()->exists()) {
            return 'usuario';
        }
        if ($this->cliente()->exists()) {
            return 'cliente';
        }
        return null;
    }

    /**
     * Verificar si es supervisor (solo para usuarios/empleados)
     */
    public function isSupervisor(): bool
    {
        $usuario = $this->usuario;
        return $usuario && $usuario->supervisor;
    }

    /**
     * Obtener la contraseña para autenticación (Laravel usa 'password' por defecto)
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }
}
