# Especificación del Modelo: TipoCliente

## Información General

- **Tabla:** `PQ_PARTES_tipo_cliente`
- **Modelo:** `App\Models\TipoCliente`
- **Descripción:** Catálogo de tipos de cliente (ej: "Corporativo", "PyME", "Startup")

---

## Campos

| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| `id` | integer (PK) | AUTO_INCREMENT, NOT NULL, UNIQUE | Identificador único |
| `code` | string | NOT NULL, UNIQUE | Código único del tipo |
| `descripcion` | string | NOT NULL | Descripción del tipo |
| `activo` | boolean | NOT NULL, DEFAULT true | Estado activo |
| `inhabilitado` | boolean | NOT NULL, DEFAULT false | Estado inhabilitado |
| `created_at` | timestamp | NOT NULL | Fecha de creación |
| `updated_at` | timestamp | NOT NULL | Fecha de última actualización |

---

## Índices

- PRIMARY KEY (`id`)
- UNIQUE INDEX (`code`)
- INDEX (`activo`)

---

## Relaciones

### HasMany: Cliente

```php
public function clientes(): HasMany
{
    return $this->hasMany(Cliente::class, 'tipo_cliente_id');
}
```

---

## Validaciones

- `code`: Requerido, único, no vacío
- `descripcion`: Requerido, no vacío, máximo 200 caracteres

---

## Reglas de Negocio

- No se puede eliminar si tiene clientes asociados

---

**Última actualización:** 2025-01-20

