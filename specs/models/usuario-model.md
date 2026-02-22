# Especificación del Modelo: Usuario

## Información General

- **Tabla:** `PQ_PARTES_usuario`
- **Modelo:** `App\Models\Usuario`
- **Descripción:** Representa a los empleados que cargan las tareas al sistema

---

## Campos

| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| `id` | integer (PK) | AUTO_INCREMENT, NOT NULL, UNIQUE | Identificador único |
| `code` | string | NOT NULL, UNIQUE | Código de usuario para autenticación |
| `nombre` | string | NOT NULL | Nombre completo del usuario |
| `email` | string | NULL, UNIQUE | Email del usuario (opcional) |
| `password_hash` | string | NOT NULL | Hash de la contraseña (bcrypt) |
| `supervisor` | boolean | NOT NULL, DEFAULT false | Indica si el usuario es supervisor |
| `activo` | boolean | NOT NULL, DEFAULT true | Indica si el usuario está activo |
| `inhabilitado` | boolean | NOT NULL, DEFAULT false | Indica si el usuario está inhabilitado (soft delete) |
| `created_at` | timestamp | NOT NULL | Fecha de creación |
| `updated_at` | timestamp | NOT NULL | Fecha de última actualización |

---

## Índices

- PRIMARY KEY (`id`)
- UNIQUE INDEX (`code`)
- UNIQUE INDEX (`email`) - Solo si email no es NULL
- INDEX (`activo`)
- INDEX (`supervisor`)
- INDEX (`inhabilitado`)

---

## Relaciones

### HasMany: RegistroTarea

```php
public function registrosTarea(): HasMany
{
    return $this->hasMany(RegistroTarea::class, 'usuario_id');
}
```

---

## Validaciones

### A Nivel de Modelo

- `code`: Requerido, único, no vacío
- `nombre`: Requerido, no vacío, máximo 200 caracteres
- `email`: Opcional, único si se proporciona, formato válido
- `password_hash`: Requerido, mínimo 8 caracteres (antes de hashear)
- `supervisor`: Boolean, default: false
- `activo`: Boolean, default: true
- `inhabilitado`: Boolean, default: false

### A Nivel de Negocio

- Un usuario habilitado debe tener `activo = true` y `inhabilitado = false`
- Para autenticación, el usuario debe estar habilitado

---

## Scopes

### Habilitados

```php
public function scopeHabilitados($query)
{
    return $query->where('activo', true)
        ->where('inhabilitado', false);
}
```

### Supervisores

```php
public function scopeSupervisores($query)
{
    return $query->where('supervisor', true);
}
```

---

## Métodos Helper

### isHabilitado()

```php
public function isHabilitado(): bool
{
    return $this->activo && !$this->inhabilitado;
}
```

### isSupervisor()

```php
public function isSupervisor(): bool
{
    return $this->supervisor === true;
}
```

---

## Autenticación

El modelo extiende `Authenticatable` y usa `HasApiTokens` (Sanctum) para autenticación.

---

**Última actualización:** 2025-01-20

