# Especificación del Modelo: ClienteTipoTarea (Pivot)

## Información General

- **Tabla:** `PQ_PARTES_cliente_tipo_tarea`
- **Modelo:** `App\Models\ClienteTipoTarea`
- **Descripción:** Tabla de asociación muchos-a-muchos entre Cliente y TipoTarea (para tipos NO genéricos)

---

## Campos

| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| `id` | integer (PK) | AUTO_INCREMENT, NOT NULL, UNIQUE | Identificador único |
| `cliente_id` | integer (FK) | NOT NULL | Referencia a Cliente |
| `tipo_tarea_id` | integer (FK) | NOT NULL | Referencia a TipoTarea |
| `created_at` | timestamp | NOT NULL | Fecha de creación |
| `updated_at` | timestamp | NOT NULL | Fecha de última actualización |

---

## Índices

- PRIMARY KEY (`id`)
- FOREIGN KEY (`cliente_id`) REFERENCES `PQ_PARTES_cliente`(`id`)
- FOREIGN KEY (`tipo_tarea_id`) REFERENCES `PQ_PARTES_tipo_tarea`(`id`)
- UNIQUE INDEX (`cliente_id`, `tipo_tarea_id`) - Evita duplicados

---

## Relaciones

Este modelo actúa como tabla pivot. Las relaciones se definen en Cliente y TipoTarea:

### Desde Cliente

```php
public function tiposTarea(): BelongsToMany
{
    return $this->belongsToMany(TipoTarea::class, 'PQ_PARTES_cliente_tipo_tarea', ...);
}
```

### Desde TipoTarea

```php
public function clientes(): BelongsToMany
{
    return $this->belongsToMany(Cliente::class, 'PQ_PARTES_cliente_tipo_tarea', ...);
}
```

---

## Validaciones

- `cliente_id`: Requerido, debe existir
- `tipo_tarea_id`: Requerido, debe existir, NO debe ser genérico (`is_generico = false`)
- La combinación `cliente_id` + `tipo_tarea_id` debe ser única

---

## Reglas de Negocio

- Solo se pueden asignar tipos NO genéricos (`is_generico = false`)
- Los tipos genéricos están disponibles para todos los clientes automáticamente
- Al desasignar, verificar que el cliente tenga al menos un tipo genérico disponible o otros tipos asignados

---

**Última actualización:** 2025-01-20

