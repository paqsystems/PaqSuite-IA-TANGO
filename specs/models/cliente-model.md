# Especificación del Modelo: Cliente

## Información General

- **Tabla:** `PQ_PARTES_cliente`
- **Modelo:** `App\Models\Cliente`
- **Descripción:** Representa los clientes para los cuales se registran tareas

---

## Campos

| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| `id` | integer (PK) | AUTO_INCREMENT, NOT NULL, UNIQUE | Identificador único |
| `code` | string | NOT NULL, UNIQUE | Código único del cliente |
| `nombre` | string | NOT NULL | Nombre/descripción del cliente |
| `tipo_cliente_id` | integer (FK) | NOT NULL | Referencia a TipoCliente |
| `email` | string | NULL, UNIQUE | Email del cliente (opcional) |
| `password_hash` | string | NULL | Hash de contraseña (si tiene acceso) |
| `activo` | boolean | NOT NULL, DEFAULT true | Estado activo |
| `inhabilitado` | boolean | NOT NULL, DEFAULT false | Estado inhabilitado |
| `created_at` | timestamp | NOT NULL | Fecha de creación |
| `updated_at` | timestamp | NOT NULL | Fecha de última actualización |

---

## Índices

- PRIMARY KEY (`id`)
- UNIQUE INDEX (`code`)
- UNIQUE INDEX (`email`) - Solo si email no es NULL
- FOREIGN KEY (`tipo_cliente_id`) REFERENCES `PQ_PARTES_tipo_cliente`(`id`)
- INDEX (`activo`)
- INDEX (`tipo_cliente_id`)

---

## Relaciones

### HasMany: RegistroTarea

```php
public function registrosTarea(): HasMany
{
    return $this->hasMany(RegistroTarea::class, 'cliente_id');
}
```

### BelongsTo: TipoCliente

```php
public function tipoCliente(): BelongsTo
{
    return $this->belongsTo(TipoCliente::class, 'tipo_cliente_id');
}
```

### BelongsToMany: TipoTarea

```php
public function tiposTarea(): BelongsToMany
{
    return $this->belongsToMany(
        TipoTarea::class,
        'PQ_PARTES_cliente_tipo_tarea',
        'cliente_id',
        'tipo_tarea_id'
    )->withTimestamps();
}
```

---

## Validaciones

- `code`: Requerido, único, no vacío
- `nombre`: Requerido, no vacío, máximo 200 caracteres
- `tipo_cliente_id`: Requerido, debe existir y estar activo/no inhabilitado
- `email`: Opcional, único si se proporciona, formato válido
- Si se proporciona `email`, también debe proporcionarse `password_hash` (y viceversa)

---

## Reglas de Negocio

- El cliente debe tener al menos un tipo de tarea genérico disponible O un tipo asignado
- No se puede eliminar si tiene tareas asociadas

---

**Última actualización:** 2025-01-20

