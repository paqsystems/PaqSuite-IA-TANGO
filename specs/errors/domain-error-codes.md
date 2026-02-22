# Catálogo de Códigos de Error del Dominio

## Descripción General

Este documento define el catálogo completo de códigos de error del sistema de registro de tareas. Todos los errores siguen el formato estándar de respuesta de la API y están organizados por categorías según su naturaleza.

---

## Formato de Respuesta de Error

Todos los errores siguen el formato estándar:

```json
{
  "error": <código_numérico>,
  "respuesta": "Mensaje legible para el usuario",
  "resultado": {
    "errors": {
      "campo": ["Mensaje específico del campo"]
    }
  }
}
```

---

## Categorías de Códigos

| Rango | Categoría | Descripción |
|-------|-----------|-------------|
| 0 | Éxito | Operación exitosa |
| 1000-1999 | Validación | Errores de validación de entrada (request/DTO) |
| 2000-2999 | Reglas de Negocio | Violaciones de reglas de negocio |
| 3000-3999 | Autorización/Permisos | Errores de permisos y autorización |
| 4000-4999 | Not Found/Conflictos | Recursos no encontrados o conflictos |
| 9000-9999 | Seguridad/Infra | Errores de seguridad e infraestructura |

---

## 0 - Éxito

| Código | Descripción | HTTP | Contexto |
|--------|-------------|------|----------|
| 0 | Operación exitosa | 200/201 | Todas las operaciones exitosas |

---

## 1000-1999: Validación (Request/DTO)

### 1000-1099: Validación General

| Código | Descripción | HTTP | Campos Afectados | Ejemplo |
|--------|-------------|------|------------------|---------|
| 1000 | Error de validación general | 422 | Varios | Múltiples campos con errores |
| 1001 | Campo requerido faltante | 422 | Cualquier campo | `email` es requerido |
| 1002 | Formato de campo inválido | 422 | Cualquier campo | `email` debe tener formato válido |
| 1003 | Tipo de dato incorrecto | 422 | Cualquier campo | `duracion_minutos` debe ser numérico |
| 1004 | Valor fuera de rango permitido | 422 | Campos numéricos | `page_size` debe estar entre 1 y 100 |

### 1100-1199: Validación de Autenticación

| Código | Descripción | HTTP | Campos Afectados | Ejemplo |
|--------|-------------|------|------------------|---------|
| 1101 | Email requerido | 422 | `email` | El campo email es obligatorio |
| 1102 | Email con formato inválido | 422 | `email` | El email debe tener un formato válido |
| 1103 | Contraseña requerida | 422 | `password` | El campo contraseña es obligatorio |
| 1104 | Contraseña muy corta | 422 | `password` | La contraseña debe tener al menos 8 caracteres |

### 1200-1299: Validación de Tareas

| Código | Descripción | HTTP | Campos Afectados | Ejemplo |
|--------|-------------|------|------------------|---------|
| 1201 | Fecha requerida | 422 | `fecha` | El campo fecha es obligatorio |
| 1202 | Fecha con formato inválido | 422 | `fecha` | La fecha debe tener formato YYYY-MM-DD |
| 1203 | Fecha futura no permitida | 422 | `fecha` | La fecha no puede ser futura |
| 1204 | Cliente requerido | 422 | `cliente_id` | El campo cliente es obligatorio |
| 1205 | Tipo de tarea requerido | 422 | `tipo_tarea_id` | El campo tipo de tarea es obligatorio |
| 1206 | Duración requerida | 422 | `duracion_minutos` | El campo duración es obligatorio |
| 1207 | Duración debe ser mayor a cero | 422 | `duracion_minutos` | La duración debe ser mayor a cero |
| 1208 | Duración excede el máximo permitido | 422 | `duracion_minutos` | La duración no puede exceder 1440 minutos (24 horas) |
| 1209 | Observación excede longitud máxima | 422 | `observacion` | La observación no puede exceder 1000 caracteres |
| 1210 | Duración debe estar en tramos de 15 minutos | 422 | `duracion_minutos` | La duración debe ser múltiplo de 15 (15, 30, 45, 60, etc.) |
| 1211 | Observación requerida | 422 | `observacion` | El campo observación es obligatorio |
| 1212 | Debe seleccionar al menos una tarea | 422 | `task_ids` | Debe seleccionar al menos una tarea (proceso masivo) |

### 1300-1399: Validación de Filtros y Paginación

| Código | Descripción | HTTP | Campos Afectados | Ejemplo |
|--------|-------------|------|------------------|---------|
| 1301 | Página inválida | 422 | `page` | La página debe ser mayor a 0 |
| 1302 | Tamaño de página inválido | 422 | `page_size` | El tamaño de página debe estar entre 1 y 100 |
| 1303 | Campo de ordenamiento inválido | 422 | `sort` | El campo de ordenamiento no está permitido |
| 1304 | Dirección de ordenamiento inválida | 422 | `sort_dir` | La dirección debe ser 'asc' o 'desc' |
| 1305 | Rango de fechas inválido | 422 | `fecha_desde`, `fecha_hasta` | La fecha desde no puede ser posterior a fecha hasta |

---

## 2000-2999: Reglas de Negocio

### 2000-2099: Reglas Generales

| Código | Descripción | HTTP | Contexto | Ejemplo |
|--------|-------------|------|----------|---------|
| 2000 | Violación de regla de negocio | 422 | General | Regla de negocio no especificada |
| 2001 | Operación no permitida en el estado actual | 422 | Estados | No se puede editar una tarea eliminada |

### 2100-2199: Reglas de Tareas

| Código | Descripción | HTTP | Contexto | Ejemplo |
|--------|-------------|------|----------|---------|
| 2101 | No se pueden registrar tareas en fechas futuras | 422 | Registro de tarea | Intento de registrar tarea con fecha futura |
| 2102 | La duración mínima no puede ser cero | 422 | Registro de tarea | Intento de registrar tarea con duración 0 |
| 2103 | Límite diario de horas excedido | 422 | Registro de tarea | Intento de registrar más de 24 horas en un día |
| 2104 | No se puede eliminar tarea de otro usuario | 422 | Eliminación de tarea | Usuario normal intenta eliminar tarea que no le pertenece (solo supervisores pueden eliminar tareas de otros) |
| 2105 | No se puede editar tarea de otro usuario | 422 | Edición de tarea | Usuario normal intenta editar tarea que no le pertenece (solo supervisores pueden editar tareas de otros) |
| 2106 | No se puede ver tarea de otro usuario | 422 | Consulta de tarea | Usuario normal intenta ver tarea que no le pertenece (solo supervisores pueden ver tareas de otros) |
| 2107 | Solo supervisores pueden especificar usuario_id | 403 | Creación/Edición de tarea | Usuario normal intenta especificar usuario_id en creación/edición de tarea (solo supervisores pueden hacerlo) |
| 2110 | No se puede modificar una tarea cerrada | 403 | Edición de tarea | Intento de modificar una tarea con `cerrado = true` |
| 2111 | No se puede eliminar una tarea cerrada | 403 | Eliminación de tarea | Intento de eliminar una tarea con `cerrado = true` |
| 2112 | No se puede eliminar un cliente con tareas asociadas | 422 | Eliminación de cliente | Cliente tiene tareas registradas en `RegistroTarea` |
| 2113 | No se puede eliminar un asistente con tareas asociadas | 422 | Eliminación de asistente | Asistente tiene tareas registradas en `RegistroTarea` |
| 2114 | No se puede eliminar un tipo de tarea en uso | 422 | Eliminación de tipo de tarea | Tipo de tarea está referenciado en `RegistroTarea` o `ClienteTipoTarea` |
| 2115 | No se puede eliminar un tipo de cliente con clientes asociados | 422 | Eliminación de tipo de cliente | Tipo de cliente tiene clientes asociados |
| 2116 | El cliente debe tener al menos un tipo de tarea disponible | 422 | Creación/Actualización de cliente | Cliente no tiene tipos de tarea genéricos ni asignados |
| 2117 | Solo puede haber un tipo de tarea por defecto | 422 | Creación/Actualización de tipo de tarea | Intento de marcar un segundo tipo como por defecto |

---

## 3000-3999: Autorización/Permisos

### 3000-3099: Autenticación

| Código | Descripción | HTTP | Contexto | Ejemplo |
|--------|-------------|------|----------|---------|
| 3001 | Usuario no autenticado | 401 | Cualquier endpoint protegido | Token no proporcionado |
| 3002 | Token inválido | 401 | Cualquier endpoint protegido | Token malformado |
| 3003 | Token expirado | 401 | Cualquier endpoint protegido | Token expirado |
| 3004 | Token revocado | 401 | Cualquier endpoint protegido | Token revocado |

### 3100-3199: Autorización

| Código | Descripción | HTTP | Contexto | Ejemplo |
|--------|-------------|------|----------|---------|
| 3101 | Acceso denegado | 403 | Endpoint protegido | Usuario sin permisos |
| 3102 | Usuario inactivo | 403 | Cualquier operación | Usuario desactivado intenta acceder |
| 3103 | Operación no permitida para este usuario | 403 | Operación específica | Usuario intenta acceder a recurso de otro |

### 3200-3299: Credenciales

| Código | Descripción | HTTP | Contexto | Ejemplo |
|--------|-------------|------|----------|---------|
| 3201 | Credenciales inválidas | 401 | Login | Email o contraseña incorrectos |
| 3202 | Usuario no encontrado | 401 | Login | Email no existe en el sistema |
| 3203 | Contraseña incorrecta | 401 | Login | Contraseña no coincide con el hash |

---

## 4000-4999: Not Found / Conflictos

### 4000-4099: Recursos No Encontrados

| Código | Descripción | HTTP | Contexto | Ejemplo |
|--------|-------------|------|----------|---------|
| 4001 | Recurso no encontrado | 404 | General | Recurso solicitado no existe |
| 4002 | Usuario no encontrado | 404 | Operaciones con usuario | Usuario con ID no existe |
| 4003 | Cliente no encontrado | 404 | Operaciones con cliente | Cliente con ID no existe |
| 4004 | Tipo de tarea no encontrado | 404 | Operaciones con tipo | Tipo de tarea con ID no existe |
| 4005 | Tarea no encontrada | 404 | Operaciones con tarea | Tarea con ID no existe |
| 4006 | Usuario especificado no encontrado | 404 | Creación/Edición de tarea | Supervisor intenta crear/editar tarea con usuario_id que no existe |

### 4100-4199: Conflictos

| Código | Descripción | HTTP | Contexto | Ejemplo |
|--------|-------------|------|----------|---------|
| 4101 | Conflicto de recurso | 409 | General | Recurso en estado conflictivo |
| 4102 | Email ya registrado | 409 | Registro de usuario | Intento de crear usuario con email existente |
| 4103 | Recurso ya existe | 409 | Creación de recursos | Intento de crear recurso duplicado |

### 4200-4299: Estado Inválido

| Código | Descripción | HTTP | Contexto | Ejemplo |
|--------|-------------|------|----------|---------|
| 4201 | Cliente inactivo | 422 | Operaciones con cliente | Cliente existe pero está inactivo |
| 4202 | Tipo de tarea inactivo | 422 | Operaciones con tipo | Tipo de tarea existe pero está inactivo |
| 4203 | Usuario inactivo | 422 | Operaciones con usuario | Usuario existe pero está inactivo |
| 4204 | Usuario especificado inactivo | 422 | Creación/Edición de tarea | Supervisor intenta crear/editar tarea con usuario_id que está inactivo |

---

## 9000-9999: Seguridad / Infraestructura

### 9000-9099: Seguridad

| Código | Descripción | HTTP | Contexto | Ejemplo |
|--------|-------------|------|----------|---------|
| 9001 | Token no provisto | 401 | Endpoint protegido | Request sin header Authorization |
| 9002 | Token inválido/expirado/revocado | 401 | Endpoint protegido | Token no válido |
| 9003 | Intento de acceso no autorizado | 403 | Endpoint protegido | Usuario sin permisos |
| 9004 | Rate limit excedido | 429 | Cualquier endpoint | Demasiadas solicitudes |

### 9100-9199: Infraestructura

| Código | Descripción | HTTP | Contexto | Ejemplo |
|--------|-------------|------|----------|---------|
| 9101 | Error de conexión a base de datos | 500 | Operaciones de BD | Timeout de conexión |
| 9102 | Servicio no disponible | 503 | Servicios externos | Servicio temporalmente no disponible |
| 9103 | Timeout de operación | 504 | Operaciones largas | Operación excedió tiempo máximo |

### 9900-9999: Errores Inesperados

| Código | Descripción | HTTP | Contexto | Ejemplo |
|--------|-------------|------|----------|---------|
| 9999 | Error inesperado del servidor | 500 | Cualquier operación | Excepción no controlada |

---

## Ejemplos de Uso

### Ejemplo 1: Validación de Registro de Tarea

**Request:**
```json
{
  "fecha": "2025-12-31",
  "cliente_id": null,
  "tipo_tarea_id": 1,
  "duracion_minutos": -10
}
```

**Response (422):**
```json
{
  "error": 1000,
  "respuesta": "Error de validación",
  "resultado": {
    "errors": {
      "fecha": ["La fecha no puede ser futura"],
      "cliente_id": ["El campo cliente es obligatorio"],
      "duracion_minutos": ["La duración debe ser mayor a cero"]
    }
  }
}
```

### Ejemplo 2: Recurso No Encontrado

**Request:**
```
GET /api/v1/tareas/999
```

**Response (404):**
```json
{
  "error": 4005,
  "respuesta": "Tarea no encontrada",
  "resultado": {}
}
```

### Ejemplo 3: Violación de Regla de Negocio

**Request:**
```
DELETE /api/v1/tareas/5
```
(Usuario intenta eliminar tarea de otro usuario)

**Response (422):**
```json
{
  "error": 2104,
  "respuesta": "No se puede eliminar tarea de otro usuario",
  "resultado": {}
}
```

### Ejemplo 4: Error de Autenticación

**Request:**
```
POST /api/v1/auth/login
{
  "email": "usuario@ejemplo.com",
  "password": "contraseña_incorrecta"
}
```

**Response (401):**
```json
{
  "error": 3201,
  "respuesta": "Credenciales inválidas",
  "resultado": {}
}
```

---

## Mapeo de Códigos HTTP

| Código de Error | HTTP Status | Descripción |
|-----------------|-------------|-------------|
| 0 | 200/201 | Éxito |
| 1000-1999 | 422 | Validación |
| 2000-2999 | 422 | Reglas de negocio |
| 3000-3099 | 401 | Autenticación |
| 3100-3199 | 403 | Autorización |
| 3200-3299 | 401 | Credenciales |
| 4000-4099 | 404 | Not Found |
| 4100-4199 | 409 | Conflictos |
| 4200-4299 | 422 | Estado inválido |
| 9000-9099 | 401/403/429 | Seguridad |
| 9100-9199 | 500/503/504 | Infraestructura |
| 9999 | 500 | Error inesperado |

---

## Convenciones de Implementación

### Mensajes de Error

- Los mensajes deben ser claros y legibles para el usuario final
- No exponer detalles técnicos internos (stack traces, SQL, etc.)
- Usar lenguaje apropiado para el contexto del negocio
- Mensajes en español (según requerimiento del proyecto)

### Estructura de Errores de Validación

Cuando hay múltiples errores de validación, agrupar por campo:

```json
{
  "error": 1000,
  "respuesta": "Error de validación",
  "resultado": {
    "errors": {
      "campo1": ["Error 1", "Error 2"],
      "campo2": ["Error 3"]
    }
  }
}
```

### Logging

- Todos los errores deben registrarse en logs del servidor
- Errores 5xx deben incluir stack trace en logs (no en respuesta)
- Errores 4xx deben incluir contexto suficiente para debugging

---

## Extensibilidad

Este catálogo puede extenderse según necesidades futuras:

- **Nuevos códigos**: Agregar en el rango correspondiente
- **Nuevas categorías**: Crear nuevos rangos si es necesario
- **Códigos específicos**: Para funcionalidades nuevas (ej: reportes, exportación)

**Regla:** Nunca reutilizar códigos existentes para nuevos propósitos. Siempre agregar nuevos códigos.

---

## Notas

- Este catálogo está alineado con el contrato de API definido en `.cursor/rules/06-api-contract.md`
- Los códigos deben ser consistentes en toda la aplicación
- El frontend debe manejar todos los códigos definidos
- Documentar cualquier código nuevo que se agregue

---

**Última actualización:** 2025-01-20

