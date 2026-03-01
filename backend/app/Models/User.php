<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Modelo: User (Tabla de Autenticación)
 *
 * Tabla física: USERS
 * Ubicación: Base de datos DICCIONARIO (PQ_DICCIONARIO), no en bases de empresas.
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $table = 'USERS';

    protected $fillable = [
        'codigo',
        'name_user',
        'email',
        'password_hash',
        'activo',
        'inhabilitado',
        'locale',
        'menu_abrir_nueva_pestana',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'inhabilitado' => 'boolean',
        'menu_abrir_nueva_pestana' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $dateFormat = 'Y-m-d H:i:s';

    protected $attributes = [
        'activo' => true,
        'inhabilitado' => false,
    ];

    public function isHabilitado(): bool
    {
        return $this->activo && !$this->inhabilitado;
    }

    public function scopeHabilitados($query)
    {
        return $query->where('activo', true)->where('inhabilitado', false);
    }

    public function getAuthPassword()
    {
        return $this->password_hash;
    }
}
