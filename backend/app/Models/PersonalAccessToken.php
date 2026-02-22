<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;
use DateTimeInterface;

/**
 * Modelo: PersonalAccessToken
 * 
 * Extiende el modelo de Sanctum para agregar compatibilidad con SQL Server.
 * 
 * @see .cursor/rules/20-sql-server-datetime-format.md
 */
class PersonalAccessToken extends SanctumPersonalAccessToken
{
    /**
     * Formato de fecha para SQL Server (sin milisegundos)
     * @see .cursor/rules/20-sql-server-datetime-format.md
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * Casts de tipos de datos para asegurar formato correcto
     */
    protected $casts = [
        'abilities' => 'json',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Prepara una fecha para serialización en SQL Server
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
