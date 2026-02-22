# Endpoint: Crear Registro de Tarea

## Información General

- **Método:** `POST`
- **Ruta:** `/api/v1/tareas`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Crea un nuevo registro de tarea (time entry) asociado al usuario autenticado. El registro incluye fecha, cliente, tipo de tarea, duración y observación opcional.

**Permisos:**
- **Usuario normal:** Solo puede crear tareas para sí mismo (el `usuario_id` se asigna automáticamente desde el token)
- **Supervisor:** Puede crear tareas para cualquier usuario. Si se proporciona `usuario_id`, se usa ese valor; si no se proporciona, se usa el ID del usuario autenticado (por defecto)

---

## Request

### Headers

```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Body

```json
{
  "fecha": "2025-01-20",
  "cliente_id": 1,
  "tipo_tarea_id": 1,
  "duracion_minutos": 120,
  "sin_cargo": false,
  "presencial": false,
  "observacion": "Desarrollo de funcionalidad X"
}
```

### Parámetros

| Campo | Tipo | Requerido | Descripción | Validaciones |
|-------|------|-----------|-------------|--------------|
| `fecha` | string (date) | Sí | Fecha en que se realizó la tarea | Formato YYYY-MM-DD (1202), no futura (1203) |
| `cliente_id` | integer | Sí | ID del cliente | Debe existir, estar activo y no estar inhabilitado (1204, 4003, 4201) |
| `tipo_tarea_id` | integer | Sí | ID del tipo de tarea | Debe existir, estar activo y no estar inhabilitado (1205, 4004, 4202) |
| `duracion_minutos` | integer | Sí | Duración en minutos | > 0 (1207), <= 1440 (1208) |
| `usuario_id` | integer | No | ID del usuario propietario de la tarea | Solo para supervisores. Si no se proporciona, se usa el ID del usuario autenticado. Debe existir, estar activo y no estar inhabilitado (2107, 4006, 4204) |
| `sin_cargo` | boolean | Sí | Indica si la tarea es sin cargo para el cliente | Default: false |
| `presencial` | boolean | Sí | Indica si la tarea es presencial (en el cliente) | Default: false |
| `observacion` | string | No | Observaciones sobre la tarea | Máximo 1000 caracteres (1209) |

---

## Response

### Success (201 Created)

```json
{
  "error": 0,
  "respuesta": "Tarea registrada correctamente",
  "resultado": {
    "id": 1,
    "usuario_id": 1,
    "cliente_id": 1,
    "tipo_tarea_id": 1,
    "fecha": "2025-01-20",
    "duracion_minutos": 120,
    "sin_cargo": false,
    "presencial": false,
    "observacion": "Desarrollo de funcionalidad X",
    "created_at": "2025-01-20T10:30:00Z",
    "updated_at": "2025-01-20T10:30:00Z"
  }
}
```

### Campos de Respuesta

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `error` | integer | Código de error (0 = éxito) |
| `respuesta` | string | Mensaje legible para el usuario |
| `resultado.id` | integer | ID del registro creado |
| `resultado.usuario_id` | integer | ID del usuario (automático del token) |
| `resultado.cliente_id` | integer | ID del cliente |
| `resultado.tipo_tarea_id` | integer | ID del tipo de tarea |
| `resultado.fecha` | string | Fecha de la tarea (YYYY-MM-DD) |
| `resultado.duracion_minutos` | integer | Duración en minutos |
| `resultado.sin_cargo` | boolean | Indica si la tarea es sin cargo |
| `resultado.presencial` | boolean | Indica si la tarea es presencial |
| `resultado.observacion` | string\|null | Observación (opcional) |
| `resultado.created_at` | string | Fecha de creación (ISO-8601) |
| `resultado.updated_at` | string | Fecha de actualización (ISO-8601) |

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

**Códigos de error posibles:**
- `3001`: Usuario no autenticado
- `3002`: Token inválido
- `3003`: Token expirado
- `3004`: Token revocado

### 422 Unprocessable Entity - Validación

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

**Códigos de error posibles:**
- `1201`: Fecha requerida
- `1202`: Fecha con formato inválido
- `1203`: Fecha futura no permitida
- `1204`: Cliente requerido
- `1205`: Tipo de tarea requerido
- `1206`: Duración requerida
- `1207`: Duración debe ser mayor a cero
- `1208`: Duración excede el máximo permitido
- `1209`: Observación excede longitud máxima

### 404 Not Found - Recurso No Encontrado

```json
{
  "error": 4003,
  "respuesta": "Cliente no encontrado",
  "resultado": {}
}
```

**Códigos de error posibles:**
- `4003`: Cliente no encontrado
- `4004`: Tipo de tarea no encontrado

### 422 Unprocessable Entity - Estado Inválido

```json
{
  "error": 4201,
  "respuesta": "Cliente inactivo",
  "resultado": {}
}
```

**Códigos de error posibles:**
- `4201`: Cliente inactivo
- `4202`: Tipo de tarea inactivo

---

## Validaciones

### A Nivel de Request

1. **Fecha:**
   - Debe estar presente (1201)
   - Formato YYYY-MM-DD (1202)
   - No puede ser futura (1203)

2. **Cliente:**
   - Debe estar presente (1204)
   - Debe ser un entero válido
   - Debe existir en `PQ_PARTES_cliente` (4003)
   - Debe estar activo y no inhabilitado (4201)

3. **Tipo de Tarea:**
   - Debe estar presente (1205)
   - Debe ser un entero válido
   - Debe existir en `PQ_PARTES_tipo_tarea` (4004)
   - Debe estar activo y no inhabilitado (4202)

4. **Duración:**
   - Debe estar presente (1206)
   - Debe ser un entero positivo (1207)
   - Máximo 1440 minutos (24 horas) (1208)

5. **Sin Cargo:**
   - Requerido
   - Tipo boolean
   - Default: false

6. **Presencial:**
   - Requerido
   - Tipo boolean
   - Default: false

7. **Observación:**
   - Opcional
   - Máximo 1000 caracteres (1209)

### A Nivel de Negocio

1. **Usuario autenticado:**
   - El `usuario_id` se obtiene automáticamente del token
   - No se puede especificar manualmente

2. **Fecha no futura: (a nivel advertencia)** 
   - La fecha no puede ser posterior a la fecha actual
   - Validación: `fecha <= CURRENT_DATE`

3. **Duración válida:**
   - Debe ser mayor a 0
   - Máximo 1440 minutos (24 horas por día)

---

## Operaciones de Base de Datos

### Tablas Involucradas

- `PQ_PARTES_registro_tarea` (INSERT)
- `PQ_PARTES_cliente` (SELECT para validación)
- `PQ_PARTES_tipo_tarea` (SELECT para validación)
- `PQ_PARTES_usuario` (obtener ID del token)

### Consultas

```php
// 1. Validar cliente existe, está activo y no está inhabilitado
$cliente = Cliente::where('id', $request->cliente_id)
    ->where('activo', true)
    ->where('inhabilitado', false)
    ->first();

if (!$cliente) {
    throw new NotFoundException('Cliente no encontrado, inactivo o inhabilitado');
}

// 2. Validar tipo de tarea existe, está activo y no está inhabilitado
$tipoTarea = TipoTarea::where('id', $request->tipo_tarea_id)
    ->where('activo', true)
    ->where('inhabilitado', false)
    ->first();

if (!$tipoTarea) {
    throw new NotFoundException('Tipo de tarea no encontrado, inactivo o inhabilitado');
}

// 3. Determinar usuario_id según permisos
$usuarioId = auth()->id(); // Por defecto, el usuario autenticado
if ($request->has('usuario_id')) {
    $usuario = auth()->user();
    if (!$usuario->supervisor) {
        // Usuario normal no puede especificar usuario_id
        throw new UnauthorizedException('Solo los supervisores pueden especificar usuario_id');
    }
    // Validar que el usuario_id especificado existe, está activo y no está inhabilitado
    $usuarioEspecificado = Usuario::where('id', $request->usuario_id)
        ->where('activo', true)
        ->where('inhabilitado', false)
        ->first();
    if (!$usuarioEspecificado) {
        throw new NotFoundException('Usuario no encontrado, inactivo o inhabilitado');
    }
    $usuarioId = $request->usuario_id;
}

// 4. Crear registro
$tarea = RegistroTarea::create([
    'usuario_id' => $usuarioId,
    'cliente_id' => $request->cliente_id,
    'tipo_tarea_id' => $request->tipo_tarea_id,
    'fecha' => $request->fecha,
    'duracion_minutos' => $request->duracion_minutos,
    'sin_cargo' => $request->sin_cargo ?? false,
    'presencial' => $request->presencial ?? false,
    'observacion' => $request->observacion ?? null,
    'created_at' => now(),
    'updated_at' => now()
]);
```

### Índices Utilizados

- `idx_cliente_activo` - Validación de cliente
- `idx_tipo_tarea_activo` - Validación de tipo
- `idx_registro_usuario_fecha` - Para consultas futuras

---

## Ejemplos de Uso

### cURL

```bash
curl -X POST https://api.ejemplo.com/api/v1/tareas \
  -H "Authorization: Bearer 1|abcdef1234567890" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "fecha": "2025-01-20",
    "cliente_id": 1,
    "tipo_tarea_id": 1,
    "duracion_minutos": 120,
    "sin_cargo": false,
    "presencial": false,
    "observacion": "Desarrollo de funcionalidad X"
  }'
```

### JavaScript (Fetch)

```javascript
const response = await fetch('/api/v1/tareas', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    fecha: '2025-01-20',
    cliente_id: 1,
    tipo_tarea_id: 1,
    duracion_minutos: 120,
    sin_cargo: false,
    presencial: false,
    observacion: 'Desarrollo de funcionalidad X'
  })
});

const data = await response.json();

if (data.error === 0) {
  console.log('Tarea creada:', data.resultado);
} else {
  console.error('Error:', data.respuesta);
}
```

---

## Notas

- El `usuario_id` se asigna automáticamente desde el token de autenticación
- La fecha se valida tanto a nivel de API como de base de datos
- Los timestamps (`created_at`, `updated_at`) se generan automáticamente
- El registro queda inmediatamente disponible para consultas

---

**Última actualización:** 2025-01-20

