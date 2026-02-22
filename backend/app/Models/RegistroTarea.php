<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo: RegistroTarea
 * 
 * Tabla física: PQ_PARTES_REGISTRO_TAREA
 * 
 * Tabla principal del sistema. Almacena los registros de tareas diarias.
 * 
 * Restricciones:
 * - duracion_minutos debe ser múltiplo de 15 (15, 30, 45, ..., 1440)
 * - duracion_minutos <= 1440 (máximo 24 horas)
 * - observacion es obligatorio
 * - cerrado = true impide modificación y eliminación
 * 
 * @property int $id
 * @property int $usuario_id FK → PQ_PARTES_USUARIOS
 * @property int $cliente_id FK → PQ_PARTES_CLIENTES
 * @property int $tipo_tarea_id FK → PQ_PARTES_TIPOS_TAREA
 * @property \Carbon\Carbon $fecha Fecha de la tarea
 * @property int $duracion_minutos Duración en minutos (múltiplo de 15)
 * @property bool $sin_cargo Indica si es sin cargo
 * @property bool $presencial Indica si es presencial
 * @property string $observacion Descripción de la tarea
 * @property bool $cerrado Indica si está cerrada
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @see docs/modelo-datos.md
 * @see TR-00(MH)-Generacion-base-datos-inicial.md
 */
class RegistroTarea extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla
     */
    protected $table = 'PQ_PARTES_REGISTRO_TAREA';

    /**
     * Campos que pueden ser asignados masivamente
     */
    protected $fillable = [
        'usuario_id',
        'cliente_id',
        'tipo_tarea_id',
        'fecha',
        'duracion_minutos',
        'sin_cargo',
        'presencial',
        'observacion',
        'cerrado',
    ];

    /**
     * Casts de tipos de datos
     */
    protected $casts = [
        'fecha' => 'date',
        'duracion_minutos' => 'integer',
        'sin_cargo' => 'boolean',
        'presencial' => 'boolean',
        'cerrado' => 'boolean',
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
        'sin_cargo' => false,
        'presencial' => false,
        'cerrado' => false,
    ];

    /**
     * Relación: Un RegistroTarea pertenece a un Usuario
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Relación: Un RegistroTarea pertenece a un Cliente
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /**
     * Relación: Un RegistroTarea pertenece a un TipoTarea
     */
    public function tipoTarea(): BelongsTo
    {
        return $this->belongsTo(TipoTarea::class, 'tipo_tarea_id');
    }

    /**
     * Verificar si la tarea está cerrada (no modificable)
     */
    public function isCerrado(): bool
    {
        return $this->cerrado === true;
    }

    /**
     * Verificar si la tarea puede ser modificada
     */
    public function puedeSerModificada(): bool
    {
        return !$this->cerrado;
    }

    /**
     * Verificar si la tarea puede ser eliminada
     */
    public function puedeSerEliminada(): bool
    {
        return !$this->cerrado;
    }

    /**
     * Obtener la duración formateada en horas y minutos
     * 
     * @return string Ejemplo: "2h 30m"
     */
    public function getDuracionFormateada(): string
    {
        $horas = intdiv($this->duracion_minutos, 60);
        $minutos = $this->duracion_minutos % 60;
        
        if ($horas > 0 && $minutos > 0) {
            return "{$horas}h {$minutos}m";
        } elseif ($horas > 0) {
            return "{$horas}h";
        } else {
            return "{$minutos}m";
        }
    }

    /**
     * Scope para tareas abiertas (no cerradas)
     */
    public function scopeAbiertas($query)
    {
        return $query->where('cerrado', false);
    }

    /**
     * Scope para tareas cerradas
     */
    public function scopeCerradas($query)
    {
        return $query->where('cerrado', true);
    }

    /**
     * Scope para tareas de un usuario específico
     */
    public function scopeDeUsuario($query, int $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    /**
     * Scope para tareas de un cliente específico
     */
    public function scopeDeCliente($query, int $clienteId)
    {
        return $query->where('cliente_id', $clienteId);
    }

    /**
     * Scope para tareas en un rango de fechas
     */
    public function scopeEntreFechas($query, $fechaDesde, $fechaHasta)
    {
        return $query->whereBetween('fecha', [$fechaDesde, $fechaHasta]);
    }
}
