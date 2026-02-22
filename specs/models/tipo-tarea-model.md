# Especificación del Modelo: TipoTarea

## Información General

- **Tabla:** `PQ_PARTES_tipo_tarea`
- **Modelo:** `App\Models\TipoTarea`
- **Descripción:** Catálogo de tipos de tarea que se pueden registrar

---

## Campos

| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| `id` | integer (PK) | AUTO_INCREMENT, NOT NULL, UNIQUE | Identificador único |
| `code` | string | NOT NULL, UNIQUE | Código único del tipo |
| `descripcion` | string | NOT NULL | Descripción del tipo |
| `is_generico` | boolean | NOT NULL, DEFAULT false | Indica si es genérico (disponible para todos) |
| `is_default` | boolean | NOT NULL, DEFAULT false | Indica si es el tipo por defecto |
| `activo` | boolean | NOT NULL, DEFAULT true | Estado activo |
| `inhabilitado` | boolean | NOT NULL, DEFAULT false | Estado inhabilitado |
| `created_at` | timestamp | NOT NULL | Fecha de creación |
| `updated_at` | timestamp | NOT NULL | Fecha de última actualización |

---

## Índices

- PRIMARY KEY (`id`)
- UNIQUE INDEX (`code`)
- INDEX (`is_generico`)
- INDEX (`is_default`)
- INDEX (`activo`)

---

## Relaciones

### HasMany: RegistroTarea

```php
public function registrosTarea(): HasMany
{
    return $this->hasMany(RegistroTarea::class, 'tipo_tarea_id');
}
```

### BelongsToMany: Cliente

```php
public function clientes(): BelongsToMany
{
    return $this->belongsToMany(
        Cliente::class,
        'PQ_PARTES_cliente_tipo_tarea',
        'tipo_tarea_id',
        'cliente_id'
    )->withTimestamps();
}
```

---

## Validaciones

- `code`: Requerido, único, no vacío
- `descripcion`: Requerido, no vacío, máximo 200 caracteres

---

## Reglas de Negocio

- Solo puede haber un tipo con `is_default = true` en todo el sistema
- Si `is_default = true`, entonces `is_generico = true` (forzado)
- Los tipos genéricos están disponibles para todos los clientes
- Los tipos NO genéricos solo están disponibles para clientes asignados
- No se puede eliminar si tiene tareas asociadas o clientes asignados

---

**Última actualización:** 2025-01-20

