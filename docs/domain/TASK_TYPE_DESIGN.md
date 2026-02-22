# Diseño de Dominio: TipoTarea (TaskType)

**Estado:** Diseño de modelo completado (sin reglas de negocio implementadas)  
**Fecha:** 2025-01-20

---

## Descripción General

Este documento describe los cambios de diseño aplicados a la entidad `TipoTarea` (TaskType) del MVP Sistema de Partes. Estos cambios extienden el modelo para soportar tipos de tarea genéricos y específicos por cliente, sin implementar aún las reglas de negocio asociadas.

---

## Campos Agregados a TipoTarea

### `is_generico` (boolean)

**Significado:** Indica si el tipo de tarea es genérico (disponible para todos los clientes del sistema).

**Valores:**
- `true`: El tipo de tarea es genérico y puede ser usado por cualquier cliente.
- `false`: El tipo de tarea es específico y solo está disponible para clientes que tengan una asociación explícita en la tabla `ClienteTipoTarea`.

**Default:** `false`

**Uso previsto:**
- Tipos genéricos: "Desarrollo", "Reunión", "Análisis" (disponibles para todos)
- Tipos específicos: "Soporte Cliente A", "Mantenimiento Cliente B" (solo para clientes específicos)

**Reglas de negocio pendientes:**
- TODO: Implementar regla de visibilidad al crear tarea (mostrar genéricos + tipos asociados al cliente seleccionado). SI

---

### `is_default` (boolean)

**Significado:** Indica si este tipo de tarea es el predeterminado del sistema.

**Valores:**
- `true`: Este tipo de tarea se considera el predeterminado.
- `false`: Este tipo de tarea no es el predeterminado.

**Default:** `false`

**Uso previsto:**
- Cuando un usuario crea una nueva tarea, el sistema puede preseleccionar automáticamente el tipo predeterminado.
- Facilita la experiencia de usuario al reducir pasos en el flujo de registro.

**Reglas de negocio pendientes:**
- TODO: Implementar regla que solo un `TipoTarea` puede tener `is_default = true` en todo el sistema. SI
- TODO: Validar que al marcar un tipo como predeterminado, se desmarque automáticamente el anterior. SI

---

## Nueva Entidad: ClienteTipoTarea

### Descripción

Tabla de asociación (pivot) que establece una relación muchos-a-muchos entre `Cliente` y `TipoTarea`. Permite asignar tipos de tarea específicos (no genéricos) a clientes particulares.

### Estructura

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | PK | Identificador único |
| `cliente_id` | FK → Cliente | Referencia al cliente |
| `tipo_tarea_id` | FK → TipoTarea | Referencia al tipo de tarea |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

### Constraints

- **Unique constraint:** `(cliente_id, tipo_tarea_id)` - Un cliente no puede tener el mismo tipo de tarea asignado dos veces.
- **Foreign keys:** 
  - `cliente_id` → `PQ_PARTES_cliente.id` (ON DELETE CASCADE)
  - `tipo_tarea_id` → `PQ_PARTES_tipo_tarea.id` (ON DELETE CASCADE)

### Índices

- `idx_cliente_tipo_tarea_cliente` - Para búsquedas por cliente
- `idx_cliente_tipo_tarea_tipo` - Para búsquedas por tipo de tarea

### Uso Previsto

Cuando un `TipoTarea` tiene `is_generico = false`, solo los clientes que tengan una entrada en `ClienteTipoTarea` podrán usar ese tipo de tarea.

**Ejemplo:**
- Tipo "Soporte Cliente A" (`is_generico = false`)
- Solo el Cliente A tiene una entrada en `ClienteTipoTarea` asociando este tipo
- Solo el Cliente A podrá seleccionar este tipo al crear una tarea

**Reglas de negocio pendientes:**
- TODO: Implementar regla de visibilidad al crear tarea (mostrar genéricos + tipos asociados al cliente seleccionado). SI
- TODO: Validar que no se pueda asignar un tipo genérico a un cliente (redundante). SI

---

## Relaciones Actualizadas

### TipoTarea

**Relaciones:**
- `TipoTarea` 1 → N `RegistroTarea` (sin cambios)
- `TipoTarea` N → M `Cliente` (a través de `ClienteTipoTarea`) - **NUEVA**

### Cliente

**Relaciones:**
- `Cliente` 1 → N `RegistroTarea` (sin cambios)
- `Cliente` N → M `TipoTarea` (a través de `ClienteTipoTarea`) - **NUEVA**

---

## Implementación Técnica

### Migraciones

1. **`2025_01_20_000001_add_fields_to_tipo_tarea_table.php`**
   - Agrega campos `is_generico` e `is_default` a `PQ_PARTES_tipo_tarea`
   - Crea índices para optimizar búsquedas

2. **`2025_01_20_000002_create_cliente_tipo_tarea_table.php`**
   - Crea tabla `PQ_PARTES_cliente_tipo_tarea`
   - Define foreign keys y unique constraint

### Modelos Eloquent

1. **`TipoTarea.php`**
   - Agrega campos `is_generico` e `is_default` al `$fillable`
   - Agrega casts para tipos booleanos
   - Define relación `clientes()` (BelongsToMany)

2. **`ClienteTipoTarea.php`**
   - Modelo para la tabla pivot
   - Define relaciones con `Cliente` y `TipoTarea`

3. **`Cliente.php`**
   - Define relación `tiposTarea()` (BelongsToMany)

---

## Notas de Implementación

### Decisiones de Diseño

1. **Campos booleanos con default `false`:** 
   - Permite migración gradual sin romper datos existentes
   - Los tipos existentes se comportan como no genéricos y no predeterminados

2. **Tabla pivot con timestamps:**
   - Permite auditoría de cuándo se asignó un tipo a un cliente
   - Facilita debugging y análisis

3. **Unique constraint en pivot:**
   - Previene duplicados accidentalmente
   - Simplifica consultas (no necesita DISTINCT)

### Consideraciones Futuras

1. **Regla de visibilidad:**
   - Al crear tarea, el frontend deberá consultar:
     - Tipos con `is_generico = true`
     - Tipos asociados al cliente seleccionado en `ClienteTipoTarea`
   - Esta lógica se implementará en el endpoint correspondiente

2. **Regla de único predeterminado:**
   - Se puede implementar mediante:
     - Trigger en base de datos
     - Validación en el modelo Eloquent
     - Middleware o observer
   - Pendiente de decisión sobre el enfoque preferido

3. **Performance:**
   - Los índices creados optimizan las consultas de filtrado
   - Considerar cache si el catálogo de tipos es grande

---

## Archivos Modificados/Creados

### Documentación
- ✅ `docs/modelo-datos.md` - Actualizado con nuevos campos y tabla
- ✅ `docs/domain/TASK_TYPE_DESIGN.md` - Este documento
- ✅ `architecture/api-to-data-mapping.md` - Menciones de nuevos campos

### Código (Ejemplos)
- ✅ `backend/database/migrations/2025_01_20_000001_add_fields_to_tipo_tarea_table.php`
- ✅ `backend/database/migrations/2025_01_20_000002_create_cliente_tipo_tarea_table.php`
- ✅ `backend/app/Models/TipoTarea.php`
- ✅ `backend/app/Models/ClienteTipoTarea.php`
- ✅ `backend/app/Models/Cliente.php`

---

## Pendientes (TODOs)

1. **Reglas de negocio:**
   - [ ] Implementar regla: solo un `TipoTarea` puede tener `is_default = true`
   - [ ] Implementar regla de visibilidad: mostrar genéricos + asociados al cliente. SI

2. **Endpoints:**
   - [ ] Actualizar endpoint de listado de tipos para considerar `is_generico` y `is_default`
   - [ ] Implementar endpoint para gestionar asociaciones Cliente-TipoTarea (si se requiere)

3. **Validaciones:**
   - [ ] Validar que no se asigne un tipo genérico a un cliente (redundante)
   - [ ] Validar que al marcar un tipo como predeterminado, se desmarque el anterior

4. **Testing:**
   - [ ] Tests unitarios para modelos
   - [ ] Tests de integración para migraciones
   - [ ] Tests de reglas de negocio (cuando se implementen)

---

**Última actualización:** 2025-01-20

