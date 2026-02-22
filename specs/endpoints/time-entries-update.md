# Endpoint: Actualizar Registro de Tarea

## Información General

- **Método:** `PUT`
- **Ruta:** `/api/v1/tareas/{id}`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Actualiza un registro de tarea existente. Todos los campos son opcionales; solo se actualizan los campos proporcionados.

**Permisos:**
- **Usuario normal:** Solo puede actualizar sus propias tareas
- **Supervisor:** Puede actualizar cualquier tarea de cualquier usuario

---

## Request

### Headers

```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Path Parameters

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| `id` | integer | Sí | ID del registro de tarea a actualizar |

### Body

```json
{
  "fecha": "2025-01-20",
  "cliente_id": 2,
  "tipo_tarea_id": 2,
  "duracion_minutos": 180,
  "sin_cargo": true,
  "presencial": true,
  "observacion": "Actualización de observación"
}
```

**Nota:** Todos los campos son opcionales. Solo se actualizan los campos proporcionados.

### Parámetros

| Campo | Tipo | Requerido | Descripción | Validaciones |
|-------|------|-----------|-------------|--------------|
| `fecha` | string (date) | No | Nueva fecha de la tarea | Formato YYYY-MM-DD (1202), no futura (1203) |
| `cliente_id` | integer | No | Nuevo ID del cliente | Debe existir, estar activo y no estar inhabilitado (4003, 4201) |
| `tipo_tarea_id` | integer | No | Nuevo ID del tipo de tarea | Debe existir, estar activo y no estar inhabilitado (4004, 4202) |
| `duracion_minutos` | integer | No | Nueva duración en minutos | > 0 (1207), <= 1440 (1208) |
| `sin_cargo` | boolean | No | Indica si la tarea es sin cargo | Default: false |
| `presencial` | boolean | No | Indica si la tarea es presencial | Default: false |
| `observacion` | string | No | Nueva observación | Máximo 1000 caracteres (1209) |

---

## Response

### Success (200 OK)

```json
{
  "error": 0,
  "respuesta": "Tarea actualizada correctamente",
  "resultado": {
    "id": 1,
    "usuario_id": 1,
    "cliente_id": 2,
    "tipo_tarea_id": 2,
    "fecha": "2025-01-20",
    "duracion_minutos": 180,
    "sin_cargo": true,
    "presencial": true,
    "observacion": "Actualización de observación",
    "created_at": "2025-01-20T10:30:00Z",
    "updated_at": "2025-01-20T15:45:00Z"
  }
}
```

### Campos de Respuesta

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `error` | integer | Código de error (0 = éxito) |
| `respuesta` | string | Mensaje legible para el usuario |
| `resultado` | object | Registro actualizado (misma estructura que creación) |

---

## Errores

### 401 Unauthorized - No Autenticado

```json
{
  "error": 3001,
  "respuesta": "Usuario no autenticado",
  "resultado": {}
}
```

### 404 Not Found - Tarea No Encontrada

```json
{
  "error": 4005,
  "respuesta": "Tarea no encontrada",
  "resultado": {}
}
```

### 422 Unprocessable Entity - No Autorizado

```json
{
  "error": 2105,
  "respuesta": "No se puede editar tarea de otro usuario",
  "resultado": {}
}
```

### 422 Unprocessable Entity - Validación

```json
{
  "error": 1000,
  "respuesta": "Error de validación",
  "resultado": {
    "errors": {
      "fecha": ["La fecha no puede ser futura"],
      "duracion_minutos": ["La duración debe ser mayor a cero"]
    }
  }
}
```

**Códigos de error posibles (mismos que creación):**
- `1202`: Fecha con formato inválido
- `1203`: Fecha futura no permitida
- `1207`: Duración debe ser mayor a cero
- `1208`: Duración excede el máximo permitido
- `1209`: Observación excede longitud máxima
- `4003`: Cliente no encontrado
- `4004`: Tipo de tarea no encontrado
- `4201`: Cliente inactivo
- `4202`: Tipo de tarea inactivo

---

## Validaciones

### A Nivel de Request

1. **Tarea debe existir:**
   - Buscar registro por ID
   - Si no existe: Error 4005

2. **Tarea debe pertenecer al usuario (solo usuarios normales):**
   - **Usuario normal:** Verificar `usuario_id` del registro = `usuario_id` del token. Si no coincide: Error 2105
   - **Supervisor:** Puede actualizar cualquier tarea (no se valida pertenencia)

3. **Validaciones de campos (si se proporcionan):**
   - Mismas validaciones que en creación
   - Solo se validan los campos que se envían

### A Nivel de Negocio

1. **Permisos de edición:**
   - **Usuario normal:** Solo puede editar sus propias tareas. Validación obligatoria antes de actualizar. Error 2105 si intenta editar tarea de otro usuario
   - **Supervisor:** Puede editar cualquier tarea de cualquier usuario (sin validación de pertenencia)

2. **Validaciones de referencias:**
   - Si se actualiza `cliente_id`: debe existir y estar activo
   - Si se actualiza `tipo_tarea_id`: debe existir y estar activo

3. **Reglas de negocio:**
   - Fecha no futura (si se actualiza)
   - Duración > 0 (si se actualiza)

---

## Operaciones de Base de Datos

### Tablas Involucradas

- `PQ_PARTES_registro_tarea` (UPDATE)
- `PQ_PARTES_cliente` (SELECT para validación, si se actualiza)
- `PQ_PARTES_tipo_tarea` (SELECT para validación, si se actualiza)

### Consultas

```php
// 1. Verificar tarea existe
$tarea = RegistroTarea::where('id', $id)->first();

if (!$tarea) {
    // Error 4005: Tarea no encontrada
}

// 2. Verificar permisos según tipo de usuario
$usuario = auth()->user();
if (!$usuario->supervisor) {
    // Usuario normal: solo puede editar sus propias tareas
    if ($tarea->usuario_id !== auth()->id()) {
        // Error 2105: No se puede editar tarea de otro usuario
    }
}
// Supervisor: puede editar cualquier tarea (no se valida pertenencia)

if (!$tarea) {
    // Verificar si existe pero no pertenece al usuario
    $exists = RegistroTarea::where('id', $id)->exists();
    if ($exists) {
        throw new AuthorizationException('No se puede editar tarea de otro usuario', 2105);
    }
    throw new NotFoundException('Tarea no encontrada', 4005);
}

// 2. Validar cliente (si se actualiza)
if ($request->has('cliente_id')) {
    $cliente = Cliente::where('id', $request->cliente_id)
        ->where('activo', true)
        ->where('inhabilitado', false)
        ->first();
    
    if (!$cliente) {
        throw new NotFoundException('Cliente no encontrado, inactivo o inhabilitado');
    }
}

// 3. Validar tipo de tarea (si se actualiza)
if ($request->has('tipo_tarea_id')) {
    $tipoTarea = TipoTarea::where('id', $request->tipo_tarea_id)
        ->where('activo', true)
        ->where('inhabilitado', false)
        ->first();
    
    if (!$tipoTarea) {
        throw new NotFoundException('Tipo de tarea no encontrado, inactivo o inhabilitado');
    }
}

// 4. Actualizar solo campos proporcionados
$updateData = [];
if ($request->has('cliente_id')) $updateData['cliente_id'] = $request->cliente_id;
if ($request->has('tipo_tarea_id')) $updateData['tipo_tarea_id'] = $request->tipo_tarea_id;
if ($request->has('fecha')) $updateData['fecha'] = $request->fecha;
if ($request->has('duracion_minutos')) $updateData['duracion_minutos'] = $request->duracion_minutos;
if ($request->has('sin_cargo')) $updateData['sin_cargo'] = $request->sin_cargo;
if ($request->has('presencial')) $updateData['presencial'] = $request->presencial;
if ($request->has('observacion')) $updateData['observacion'] = $request->observacion;
$updateData['updated_at'] = now();

$tarea->update($updateData);
```

### Índices Utilizados

- PRIMARY KEY (`id`) - Búsqueda del registro
- `idx_registro_usuario_fecha` - Verificación de pertenencia

---

## Ejemplos de Uso

### cURL

```bash
# Actualizar todos los campos
curl -X PUT "https://api.ejemplo.com/api/v1/tareas/1" \
  -H "Authorization: Bearer 1|abcdef1234567890" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "fecha": "2025-01-20",
    "cliente_id": 2,
    "tipo_tarea_id": 2,
    "duracion_minutos": 180,
    "observacion": "Actualización de observación"
  }'

# Actualizar solo duración
curl -X PUT "https://api.ejemplo.com/api/v1/tareas/1" \
  -H "Authorization: Bearer 1|abcdef1234567890" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "duracion_minutos": 240
  }'
```

### JavaScript (Fetch)

```javascript
// Actualizar tarea
const response = await fetch('/api/v1/tareas/1', {
  method: 'PUT',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    duracion_minutos: 180,
    observacion: 'Actualización de observación'
  })
});

const data = await response.json();

if (data.error === 0) {
  console.log('Tarea actualizada:', data.resultado);
} else if (data.error === 2105) {
  console.error('No puedes editar esta tarea');
} else {
  console.error('Error:', data.respuesta);
}
```

---

## Notas

- Solo el usuario propietario puede actualizar su tarea
- Los campos no proporcionados mantienen su valor actual
- El campo `usuario_id` no se puede modificar
- El campo `id` no se puede modificar
- Los timestamps `created_at` no se modifican, solo `updated_at`
- La operación es idempotente (múltiples llamadas con los mismos datos producen el mismo resultado)

---

**Última actualización:** 2025-01-20

