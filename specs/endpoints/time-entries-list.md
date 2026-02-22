# Endpoint: Listar Registros de Tarea

## Información General

- **Método:** `GET`
- **Ruta:** `/api/v1/tareas`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Obtiene una lista paginada de registros de tarea. Soporta filtros por fecha, ordenamiento y paginación.

**Permisos:**
- **Usuario normal:** Solo ve sus propias tareas (filtrado automático por `usuario_id` del token)
- **Supervisor:** Ve todas las tareas de todos los usuarios. Puede filtrar por `usuario_id` opcional

---

## Request

### Headers

```
Authorization: Bearer {token}
Accept: application/json
```

### Query Parameters

| Parámetro | Tipo | Requerido | Descripción | Validaciones |
|-----------|------|-----------|-------------|--------------|
| `usuario_id` | integer | No | ID del usuario para filtrar tareas | Solo para supervisores. Si no se proporciona, se muestran todas las tareas (supervisores) o solo las propias (usuarios normales). Debe existir, estar activo y no estar inhabilitado (2107, 4006, 4204) |
| `page` | integer | No | Número de página | >= 1 (1301), default: 1 |
| `page_size` | integer | No | Tamaño de página | 1-100 (1302), default: 20 |
| `sort` | string | No | Campo de ordenamiento | Whitelist (1303), default: "fecha" |
| `sort_dir` | string | No | Dirección de ordenamiento | "asc" o "desc" (1304), default: "desc" |
| `fecha_desde` | string (date) | No | Fecha inicial del rango | Formato YYYY-MM-DD |
| `fecha_hasta` | string (date) | No | Fecha final del rango | Formato YYYY-MM-DD, >= fecha_desde (1305) |

### Campos Permitidos para Ordenamiento (Whitelist)

- `fecha`
- `duracion_minutos`
- `created_at`

---

## Response

### Success (200 OK)

```json
{
  "error": 0,
  "respuesta": "Tareas obtenidas correctamente",
  "resultado": {
    "items": [
      {
        "id": 1,
        "fecha": "2025-01-20",
        "cliente": {
          "id": 1,
          "nombre": "Cliente A",
          "tipo_cliente": {
            "id": 1,
            "descripcion": "Corporativo"
          }
        },
        "tipo_tarea": {
          "id": 1,
          "descripcion": "Desarrollo"
        },
        "duracion_minutos": 120,
        "duracion_horas": 2.0,
        "sin_cargo": false,
        "presencial": false,
        "observacion": "Desarrollo de funcionalidad X",
        "created_at": "2025-01-20T10:30:00Z"
      }
    ],
    "page": 1,
    "page_size": 20,
    "total": 1,
    "total_pages": 1
  }
}
```

### Campos de Respuesta

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `error` | integer | Código de error (0 = éxito) |
| `respuesta` | string | Mensaje legible para el usuario |
| `resultado.items` | array | Lista de registros de tarea |
| `resultado.items[].id` | integer | ID del registro |
| `resultado.items[].fecha` | string | Fecha de la tarea (YYYY-MM-DD) |
| `resultado.items[].cliente.id` | integer | ID del cliente |
| `resultado.items[].cliente.nombre` | string | Nombre del cliente |
| `resultado.items[].cliente.tipo_cliente` | object | Tipo de cliente (obligatorio) |
| `resultado.items[].cliente.tipo_cliente.id` | integer | ID del tipo de cliente |
| `resultado.items[].cliente.tipo_cliente.descripcion` | string | Descripción del tipo de cliente |
| `resultado.items[].tipo_tarea.id` | integer | ID del tipo de tarea |
| `resultado.items[].tipo_tarea.descripcion` | string | Descripción del tipo |
| `resultado.items[].duracion_minutos` | integer | Duración en minutos |
| `resultado.items[].duracion_horas` | number | Duración en horas (calculado) |
| `resultado.items[].sin_cargo` | boolean | Indica si la tarea es sin cargo |
| `resultado.items[].presencial` | boolean | Indica si la tarea es presencial |
| `resultado.items[].observacion` | string\|null | Observación |
| `resultado.items[].created_at` | string | Fecha de creación (ISO-8601) |
| `resultado.page` | integer | Página actual |
| `resultado.page_size` | integer | Tamaño de página |
| `resultado.total` | integer | Total de registros |
| `resultado.total_pages` | integer | Total de páginas |

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

### 422 Unprocessable Entity - Validación

```json
{
  "error": 1301,
  "respuesta": "La página debe ser mayor a 0",
  "resultado": {
    "errors": {
      "page": ["La página debe ser mayor a 0"]
    }
  }
}
```

**Códigos de error posibles:**
- `1301`: Página inválida
- `1302`: Tamaño de página inválido
- `1303`: Campo de ordenamiento inválido
- `1304`: Dirección de ordenamiento inválida
- `1305`: Rango de fechas inválido

---

## Validaciones

### A Nivel de Request

1. **Paginación:**
   - `page`: >= 1 (1301)
   - `page_size`: 1-100 (1302)

2. **Ordenamiento:**
   - `sort`: Solo campos permitidos (whitelist) (1303)
   - `sort_dir`: "asc" o "desc" (1304)

3. **Filtros de Fecha:**
   - `fecha_desde`: Formato YYYY-MM-DD
   - `fecha_hasta`: Formato YYYY-MM-DD
   - `fecha_desde` <= `fecha_hasta` (1305)

### A Nivel de Negocio

1. **Solo tareas del usuario:**
   - Filtro automático por `usuario_id` del token
   - No se pueden ver tareas de otros usuarios

2. **Filtros aplicados:**
   - Si se proporciona `fecha_desde`: `fecha >= fecha_desde`
   - Si se proporciona `fecha_hasta`: `fecha <= fecha_hasta`

---

## Operaciones de Base de Datos

### Tablas Involucradas

- `PQ_PARTES_registro_tarea` (SELECT principal)
- `PQ_PARTES_cliente` (LEFT JOIN)
- `PQ_PARTES_tipo_tarea` (LEFT JOIN)

### Consultas

```php
// Query Builder con JOIN (evitar N+1)
$query = RegistroTarea::query()
    ->where('usuario_id', auth()->id())
    ->leftJoin('PQ_PARTES_cliente', 'PQ_PARTES_registro_tarea.cliente_id', '=', 'PQ_PARTES_cliente.id')
    ->leftJoin('PQ_PARTES_tipo_cliente', 'PQ_PARTES_cliente.tipo_cliente_id', '=', 'PQ_PARTES_tipo_cliente.id')
    ->leftJoin('PQ_PARTES_tipo_tarea', 'PQ_PARTES_registro_tarea.tipo_tarea_id', '=', 'PQ_PARTES_tipo_tarea.id');

// Filtros opcionales
if ($request->has('fecha_desde')) {
    $query->where('PQ_PARTES_registro_tarea.fecha', '>=', $request->fecha_desde);
}

if ($request->has('fecha_hasta')) {
    $query->where('PQ_PARTES_registro_tarea.fecha', '<=', $request->fecha_hasta);
}

// Ordenamiento
$sortField = $request->get('sort', 'fecha');
$sortDir = $request->get('sort_dir', 'desc');
$allowedSortFields = ['fecha', 'duracion_minutos', 'created_at'];
if (!in_array($sortField, $allowedSortFields)) {
    throw new ValidationException('Campo de ordenamiento inválido');
}
$query->orderBy("PQ_PARTES_registro_tarea.{$sortField}", $sortDir);

// Paginación
$page = $request->get('page', 1);
$pageSize = min($request->get('page_size', 20), 100);
$tareas = $query->paginate($pageSize, [
    'PQ_PARTES_registro_tarea.*',
    'PQ_PARTES_cliente.nombre as cliente_nombre',
    'PQ_PARTES_tipo_cliente.id as tipo_cliente_id',
    'PQ_PARTES_tipo_cliente.descripcion as tipo_cliente_descripcion',
    'PQ_PARTES_tipo_tarea.descripcion as tipo_tarea_descripcion'
], 'page', $page);

// Mapear resultados incluyendo campos booleanos
$tareas->getCollection()->transform(function ($tarea) {
    return [
        'id' => $tarea->id,
        'fecha' => $tarea->fecha,
        'cliente' => [
            'id' => $tarea->cliente_id,
            'nombre' => $tarea->cliente_nombre,
            'tipo_cliente' => [
                'id' => $tarea->tipo_cliente_id,
                'descripcion' => $tarea->tipo_cliente_descripcion
            ] // tipo_cliente_id es obligatorio, siempre existe
        ],
        'tipo_tarea' => [
            'id' => $tarea->tipo_tarea_id,
            'descripcion' => $tarea->tipo_tarea_descripcion
        ],
        'duracion_minutos' => $tarea->duracion_minutos,
        'duracion_horas' => round($tarea->duracion_minutos / 60, 2),
        'sin_cargo' => (bool) $tarea->sin_cargo,
        'presencial' => (bool) $tarea->presencial,
        'observacion' => $tarea->observacion,
        'created_at' => $tarea->created_at->toISOString()
    ];
});
```

### Índices Utilizados

- `idx_registro_usuario_fecha` - Consulta principal
- `idx_registro_fecha` - Filtros por fecha

---

## Ejemplos de Uso

### cURL

```bash
# Listar todas las tareas (primera página)
curl -X GET "https://api.ejemplo.com/api/v1/tareas" \
  -H "Authorization: Bearer 1|abcdef1234567890" \
  -H "Accept: application/json"

# Con filtros y paginación
curl -X GET "https://api.ejemplo.com/api/v1/tareas?fecha_desde=2025-01-01&fecha_hasta=2025-01-31&page=1&page_size=20&sort=fecha&sort_dir=desc" \
  -H "Authorization: Bearer 1|abcdef1234567890" \
  -H "Accept: application/json"
```

### JavaScript (Fetch)

```javascript
// Listar tareas con filtros
const params = new URLSearchParams({
  fecha_desde: '2025-01-01',
  fecha_hasta: '2025-01-31',
  page: 1,
  page_size: 20,
  sort: 'fecha',
  sort_dir: 'desc'
});

const response = await fetch(`/api/v1/tareas?${params}`, {
  method: 'GET',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }
});

const data = await response.json();

if (data.error === 0) {
  console.log('Tareas:', data.resultado.items);
  console.log('Total:', data.resultado.total);
  console.log('Páginas:', data.resultado.total_pages);
}
```

---

## Notas

- Solo se devuelven tareas del usuario autenticado
- Se usa LEFT JOIN para evitar N+1 queries
- La paginación es obligatoria (máximo 100 registros por página)
- Los campos de ordenamiento están restringidos a una whitelist por seguridad
- `duracion_horas` se calcula automáticamente (duracion_minutos / 60)

---

**Última actualización:** 2025-01-20

