# Reglas de Validación

## Descripción General

Este documento define todas las reglas de validación aplicadas a los endpoints de la API, organizadas por entidad y campo. Estas validaciones se aplican tanto a nivel de request como a nivel de base de datos.

---

## Validaciones de Autenticación

### Código de Usuario

| Regla | Código Error | Mensaje | Validación |
|-------|--------------|---------|------------|
| Requerido | 1101 | El código de usuario es obligatorio | Campo presente y no vacío |
| No vacío | 1102 | El código de usuario no puede estar vacío | `strlen($code) > 0` |

### Contraseña

| Regla | Código Error | Mensaje | Validación |
|-------|--------------|---------|------------|
| Requerido | 1103 | El campo contraseña es obligatorio | Campo presente y no vacío |
| Longitud mínima | 1104 | La contraseña debe tener al menos 8 caracteres | `length >= 8` |

---

## Validaciones de Tareas (Time Entries)

### Fecha

| Regla | Código Error | Mensaje | Validación |
|-------|--------------|---------|------------|
| Requerido | 1201 | El campo fecha es obligatorio | Campo presente |
| Formato válido | 1202 | La fecha debe tener formato YYYY-MM-DD | Regex: `^\d{4}-\d{2}-\d{2}$` |
| No futura | 1203 | La fecha no puede ser futura | `fecha <= CURRENT_DATE` |

**Implementación:**
```php
// Validación de formato
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    throw new ValidationException('Formato inválido', 1202);
}

// Validación de no futura
if (Carbon::parse($fecha)->isFuture()) {
    throw new ValidationException('Fecha futura no permitida', 1203);
}
```

### Cliente ID

| Regla | Código Error | Mensaje | Validación |
|-------|--------------|---------|------------|
| Requerido | 1204 | El campo cliente es obligatorio | Campo presente |
| Tipo entero | 1003 | El cliente_id debe ser un número entero | `is_integer($cliente_id)` |
| Debe existir | 4003 | Cliente no encontrado | Existe en `PQ_PARTES_cliente` |
| Debe estar activo | 4201 | Cliente inactivo | `activo = true` |

**Implementación:**
```php
$cliente = Cliente::where('id', $cliente_id)
    ->where('activo', true)
    ->first();

if (!$cliente) {
    // Verificar si existe pero está inactivo
    $exists = Cliente::where('id', $cliente_id)->exists();
    throw $exists 
        ? new ValidationException('Cliente inactivo', 4201)
        : new NotFoundException('Cliente no encontrado', 4003);
}
```

### Tipo de Tarea ID

| Regla | Código Error | Mensaje | Validación |
|-------|--------------|---------|------------|
| Requerido | 1205 | El campo tipo de tarea es obligatorio | Campo presente |
| Tipo entero | 1003 | El tipo_tarea_id debe ser un número entero | `is_integer($tipo_tarea_id)` |
| Debe existir | 4004 | Tipo de tarea no encontrado | Existe en `PQ_PARTES_tipo_tarea` |
| Debe estar activo | 4202 | Tipo de tarea inactivo | `activo = true` |

**Implementación:**
```php
$tipoTarea = TipoTarea::where('id', $tipo_tarea_id)
    ->where('activo', true)
    ->first();

if (!$tipoTarea) {
    $exists = TipoTarea::where('id', $tipo_tarea_id)->exists();
    throw $exists 
        ? new ValidationException('Tipo de tarea inactivo', 4202)
        : new NotFoundException('Tipo de tarea no encontrado', 4004);
}
```

### Duración (Minutos)

| Regla | Código Error | Mensaje | Validación |
|-------|--------------|---------|------------|
| Requerido | 1206 | El campo duración es obligatorio | Campo presente |
| Tipo entero | 1003 | La duración debe ser un número entero | `is_integer($duracion_minutos)` |
| Mayor a cero | 1207 | La duración debe ser mayor a cero | `duracion_minutos > 0` |
| Máximo permitido | 1208 | La duración no puede exceder 1440 minutos (24 horas) | `duracion_minutos <= 1440` |
| Tramos de 15 minutos | 1210 | La duración debe estar en tramos de 15 minutos | `duracion_minutos % 15 === 0` |

**Implementación:**
```php
if ($duracion_minutos <= 0) {
    throw new ValidationException('Duración debe ser mayor a cero', 1207);
}

if ($duracion_minutos > 1440) {
    throw new ValidationException('Duración excede el máximo permitido', 1208);
}

if ($duracion_minutos % 15 !== 0) {
    throw new ValidationException('La duración debe estar en tramos de 15 minutos', 1210);
}
```

### Observación

| Regla | Código Error | Mensaje | Validación |
|-------|--------------|---------|------------|
| Requerido | 1211 | El campo observación es obligatorio | Campo presente y no vacío |
| Longitud máxima | 1209 | La observación no puede exceder 1000 caracteres | `strlen($observacion) <= 1000` |

**Implementación:**
```php
if (empty($observacion) || trim($observacion) === '') {
    throw new ValidationException('El campo observación es obligatorio', 1211);
}

if (strlen($observacion) > 1000) {
    throw new ValidationException('Observación excede longitud máxima', 1209);
}
```

---

## Validaciones de Filtros y Paginación

### Página

| Regla | Código Error | Mensaje | Validación |
|-------|--------------|---------|------------|
| Tipo entero | 1003 | La página debe ser un número entero | `is_integer($page)` |
| Mayor a cero | 1301 | La página debe ser mayor a 0 | `page >= 1` |
| Default | - | - | Si no se proporciona: `page = 1` |

### Tamaño de Página

| Regla | Código Error | Mensaje | Validación |
|-------|--------------|---------|------------|
| Tipo entero | 1003 | El tamaño de página debe ser un número entero | `is_integer($page_size)` |
| Rango válido | 1302 | El tamaño de página debe estar entre 1 y 100 | `1 <= page_size <= 100` |
| Default | - | - | Si no se proporciona: `page_size = 20` |

**Implementación:**
```php
$page = max(1, (int) $request->get('page', 1));
$pageSize = min(100, max(1, (int) $request->get('page_size', 20)));
```

### Ordenamiento

| Regla | Código Error | Mensaje | Validación |
|-------|--------------|---------|------------|
| Campo permitido | 1303 | El campo de ordenamiento no está permitido | Whitelist de campos |
| Dirección válida | 1304 | La dirección debe ser 'asc' o 'desc' | `sort_dir IN ('asc', 'desc')` |
| Default | - | - | `sort = 'fecha'`, `sort_dir = 'desc'` |

**Campos Permitidos (Whitelist):**
- `fecha`
- `duracion_minutos`
- `created_at`

**Implementación:**
```php
$allowedSortFields = ['fecha', 'duracion_minutos', 'created_at'];
$sortField = $request->get('sort', 'fecha');

if (!in_array($sortField, $allowedSortFields)) {
    throw new ValidationException('Campo de ordenamiento inválido', 1303);
}

$sortDir = strtolower($request->get('sort_dir', 'desc'));
if (!in_array($sortDir, ['asc', 'desc'])) {
    throw new ValidationException('Dirección de ordenamiento inválida', 1304);
}
```

### Rango de Fechas

| Regla | Código Error | Mensaje | Validación |
|-------|--------------|---------|------------|
| Formato válido | 1202 | La fecha debe tener formato YYYY-MM-DD | Aplica a ambas fechas |
| Rango válido | 1305 | La fecha desde no puede ser posterior a fecha hasta | `fecha_desde <= fecha_hasta` |
| Opcional | - | - | Ambas fechas son opcionales |

**Implementación:**
```php
if ($request->has('fecha_desde') && $request->has('fecha_hasta')) {
    $fechaDesde = Carbon::parse($request->fecha_desde);
    $fechaHasta = Carbon::parse($request->fecha_hasta);
    
    if ($fechaDesde->gt($fechaHasta)) {
        throw new ValidationException('Rango de fechas inválido', 1305);
    }
}
```

---

## Validaciones de Autorización

### Token de Autenticación

| Regla | Código Error | Mensaje | Validación |
|-------|--------------|---------|------------|
| Requerido | 9001 | Token no provisto | Header `Authorization` presente |
| Válido | 3002 | Token inválido | Token válido según Sanctum |
| No expirado | 3003 | Token expirado | Token dentro del período de validez |
| No revocado | 3004 | Token revocado | Token no ha sido revocado |

### Propiedad de Recurso

| Regla | Código Error | Mensaje | Validación |
|-------|--------------|---------|------------|
| Tarea pertenece al usuario | 2105 | No se puede editar tarea de otro usuario | `tarea.usuario_id = token.usuario_id` |
| Tarea pertenece al usuario | 2104 | No se puede eliminar tarea de otro usuario | `tarea.usuario_id = token.usuario_id` |

**Implementación:**
```php
$tarea = RegistroTarea::where('id', $id)
    ->where('usuario_id', auth()->id())
    ->first();

if (!$tarea) {
    $exists = RegistroTarea::where('id', $id)->exists();
    throw $exists 
        ? new AuthorizationException('No se puede editar tarea de otro usuario', 2105)
        : new NotFoundException('Tarea no encontrada', 4005);
}
```

---

## Validaciones de Reglas de Negocio

### Fecha No Futura (Advertencia)

| Regla | Código Error | Mensaje | Validación |
|-------|--------------|---------|------------|
| Advertencia si futura | - | Advertencia: La fecha es futura | `fecha > CURRENT_DATE` (muestra advertencia, no bloquea) |

**Implementación:**
```php
// A nivel de API - Advertencia (no bloquea)
if (Carbon::parse($fecha)->isFuture()) {
    // Retornar advertencia en respuesta, pero permitir continuar
    $warnings[] = 'La fecha seleccionada es futura. ¿Desea continuar?';
    // En frontend, mostrar modal de confirmación
}

// A nivel de BD (CHECK constraint)
// CHECK (fecha <= CURRENT_DATE) - Opcional, según requerimiento
```

### Duración Válida

| Regla | Código Error | Mensaje | Validación |
|-------|--------------|---------|------------|
| Mayor a cero | 2102 | La duración mínima no puede ser cero | `duracion_minutos > 0` |

**Implementación:**
```php
// A nivel de API
if ($duracion_minutos <= 0) {
    throw new BusinessRuleException('Duración debe ser mayor a cero', 2102);
}

// A nivel de BD (CHECK constraint)
// CHECK (duracion_minutos > 0)
```

---

## Orden de Validación

Las validaciones se aplican en el siguiente orden:

1. **Autenticación** (si aplica)
   - Token presente y válido

2. **Validación de Request**
   - Campos requeridos
   - Tipos de datos
   - Formatos básicos

3. **Validación de Referencias**
   - Recursos existen
   - Recursos están activos

4. **Validación de Reglas de Negocio**
   - Fecha no futura
   - Duración válida
   - Propiedad de recursos

5. **Validación de Base de Datos**
   - Constraints de BD
   - Integridad referencial

---

## Mensajes de Error

### Principios

1. **Claridad:** El mensaje debe ser claro y comprensible
2. **Especificidad:** Debe indicar qué campo tiene el problema
3. **Accionabilidad:** Debe guiar al usuario sobre cómo corregirlo
4. **No técnico:** No debe exponer detalles técnicos internos

### Ejemplos

✅ **Buenos mensajes:**
- "La fecha no puede ser futura"
- "El cliente seleccionado no está activo"
- "La duración debe ser mayor a cero"

❌ **Malos mensajes:**
- "Validation failed"
- "SQLSTATE[23000]: Integrity constraint violation"
- "Error 422"

---

## Referencias

- Códigos de error: `specs/errors/domain-error-codes.md`
- Contrato de API: `.cursor/rules/06-api-contract.md`
- Mapeo API-Datos: `architecture/api-to-data-mapping.md`

---

**Última actualización:** 2025-01-20

