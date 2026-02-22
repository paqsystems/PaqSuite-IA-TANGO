# Especificación del Modelo: RegistroTarea

## Información General

- **Tabla:** `PQ_PARTES_registro_tarea`
- **Modelo:** `App\Models\RegistroTarea`
- **Descripción:** Representa el registro de una tarea realizada por un usuario para un cliente

---

## Campos

| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| `id` | integer (PK) | AUTO_INCREMENT, NOT NULL, UNIQUE | Identificador único |
| `usuario_id` | integer (FK) | NOT NULL | Referencia a Usuario |
| `cliente_id` | integer (FK) | NOT NULL | Referencia a Cliente |
| `tipo_tarea_id` | integer (FK) | NOT NULL | Referencia a TipoTarea |
| `fecha` | date | NOT NULL | Fecha en que se realizó la tarea |
| `duracion_minutos` | integer | NOT NULL | Duración en minutos |
| `sin_cargo` | boolean | NOT NULL, DEFAULT false | Indica si la tarea es sin cargo |
| `presencial` | boolean | NOT NULL, DEFAULT false | Indica si la tarea es presencial |
| `observacion` | text | NOT NULL | Descripción de la tarea (obligatorio) |
| `cerrado` | boolean | NOT NULL, DEFAULT false | Indica si la tarea está cerrada |
| `created_at` | timestamp | NOT NULL | Fecha de creación |
| `updated_at` | timestamp | NOT NULL | Fecha de última actualización |

---

## Índices

- PRIMARY KEY (`id`)
- FOREIGN KEY (`usuario_id`) REFERENCES `PQ_PARTES_usuario`(`id`)
- FOREIGN KEY (`cliente_id`) REFERENCES `PQ_PARTES_cliente`(`id`)
- FOREIGN KEY (`tipo_tarea_id`) REFERENCES `PQ_PARTES_tipo_tarea`(`id`)
- INDEX (`usuario_id`, `fecha`) - Para consultas por usuario y fecha
- INDEX (`cliente_id`, `fecha`) - Para consultas por cliente y fecha
- INDEX (`fecha`) - Para filtros de fecha
- INDEX (`cerrado`) - Para filtros de estado

---

## Relaciones

### BelongsTo: Usuario

```php
public function usuario(): BelongsTo
{
    return $this->belongsTo(Usuario::class, 'usuario_id');
}
```

### BelongsTo: Cliente

```php
public function cliente(): BelongsTo
{
    return $this->belongsTo(Cliente::class, 'cliente_id');
}
```

### BelongsTo: TipoTarea

```php
public function tipoTarea(): BelongsTo
{
    return $this->belongsTo(TipoTarea::class, 'tipo_tarea_id');
}
```

---

## Validaciones

### A Nivel de Modelo

- `usuario_id`: Requerido, debe existir en `PQ_PARTES_usuario`
- `cliente_id`: Requerido, debe existir en `PQ_PARTES_cliente`, debe estar activo y no inhabilitado
- `tipo_tarea_id`: Requerido, debe existir en `PQ_PARTES_tipo_tarea`, debe estar activo y no inhabilitado
- `fecha`: Requerido, formato YYYY-MM-DD, no futura (advertencia)
- `duracion_minutos`: Requerido, > 0, <= 1440, múltiplo de 15
- `sin_cargo`: Boolean, default: false
- `presencial`: Boolean, default: false
- `observacion`: Requerido, no vacío, máximo 1000 caracteres
- `cerrado`: Boolean, default: false

### A Nivel de Negocio

- Una tarea cerrada (`cerrado = true`) no se puede modificar ni eliminar
- El tipo de tarea debe ser genérico o estar asignado al cliente

---

## Scopes

### Abiertas

```php
public function scopeAbiertas($query)
{
    return $query->where('cerrado', false);
}
```

### Cerradas

```php
public function scopeCerradas($query)
{
    return $query->where('cerrado', true);
}
```

### Del Usuario

```php
public function scopeDelUsuario($query, int $usuarioId)
{
    return $query->where('usuario_id', $usuarioId);
}
```

### Del Cliente

```php
public function scopeDelCliente($query, int $clienteId)
{
    return $query->where('cliente_id', $clienteId);
}
```

### En Rango de Fechas

```php
public function scopeEnRangoFechas($query, ?string $fechaDesde, ?string $fechaHasta)
{
    if ($fechaDesde) {
        $query->where('fecha', '>=', $fechaDesde);
    }
    
    if ($fechaHasta) {
        $query->where('fecha', '<=', $fechaHasta);
    }
    
    return $query;
}
```

---

## Métodos Helper

### isCerrada()

```php
public function isCerrada(): bool
{
    return $this->cerrado === true;
}
```

### getDuracionHorasAttribute()

```php
public function getDuracionHorasAttribute(): float
{
    return round($this->duracion_minutos / 60, 2);
}
```

---

## Restricciones de Base de Datos

### CHECK Constraints (Recomendado)

```sql
ALTER TABLE PQ_PARTES_registro_tarea
ADD CONSTRAINT chk_duracion_positiva 
CHECK (duracion_minutos > 0);

ALTER TABLE PQ_PARTES_registro_tarea
ADD CONSTRAINT chk_duracion_maxima 
CHECK (duracion_minutos <= 1440);

ALTER TABLE PQ_PARTES_registro_tarea
ADD CONSTRAINT chk_duracion_tramos 
CHECK (duracion_minutos % 15 = 0);
```

---

**Última actualización:** 2025-01-20

