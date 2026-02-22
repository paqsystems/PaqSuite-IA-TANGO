# Endpoint: Resumen de Dedicación por Cliente

## Información General

- **Método:** `GET`
- **Ruta:** `/api/v1/tareas/resumen`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Obtiene un resumen agregado de la dedicación del usuario autenticado, agrupado por cliente. Incluye totales de minutos, horas y cantidad de tareas para cada cliente, así como totales generales.

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
| `fecha_desde` | string (date) | No | Fecha inicial del período | Formato YYYY-MM-DD |
| `fecha_hasta` | string (date) | No | Fecha final del período | Formato YYYY-MM-DD, >= fecha_desde (1305) |

---

## Response

### Success (200 OK)

```json
{
  "error": 0,
  "respuesta": "Resumen obtenido correctamente",
  "resultado": {
    "periodo": {
      "fecha_desde": "2025-01-01",
      "fecha_hasta": "2025-01-31"
    },
    "resumen_por_cliente": [
            {
              "cliente_id": 1,
              "cliente_nombre": "Cliente A",
              "tipo_cliente_id": 1,
              "tipo_cliente_descripcion": "Corporativo",
        "total_minutos": 480,
        "total_horas": 8.0,
        "cantidad_tareas": 4
      },
      {
        "cliente_id": 2,
        "cliente_nombre": "Cliente B",
        "tipo_cliente_id": 2,
        "tipo_cliente_descripcion": "PyME",
        "total_minutos": 240,
        "total_horas": 4.0,
        "cantidad_tareas": 2
      }
    ],
    "totales": {
      "total_minutos": 720,
      "total_horas": 12.0,
      "cantidad_tareas": 6
    }
  }
}
```

### Campos de Respuesta

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `error` | integer | Código de error (0 = éxito) |
| `respuesta` | string | Mensaje legible para el usuario |
| `resultado.periodo.fecha_desde` | string\|null | Fecha inicial del período (si se proporcionó) |
| `resultado.periodo.fecha_hasta` | string\|null | Fecha final del período (si se proporcionó) |
| `resultado.resumen_por_cliente` | array | Lista de resúmenes por cliente |
| `resultado.resumen_por_cliente[].cliente_id` | integer | ID del cliente |
| `resultado.resumen_por_cliente[].cliente_nombre` | string | Nombre del cliente |
| `resultado.resumen_por_cliente[].tipo_cliente_id` | integer | ID del tipo de cliente (obligatorio) |
| `resultado.resumen_por_cliente[].tipo_cliente_descripcion` | string | Descripción del tipo de cliente (obligatorio) |
| `resultado.resumen_por_cliente[].total_minutos` | integer | Total de minutos dedicados |
| `resultado.resumen_por_cliente[].total_horas` | number | Total de horas dedicadas (calculado) |
| `resultado.resumen_por_cliente[].cantidad_tareas` | integer | Cantidad de tareas registradas |
| `resultado.totales.total_minutos` | integer | Total general de minutos |
| `resultado.totales.total_horas` | number | Total general de horas (calculado) |
| `resultado.totales.cantidad_tareas` | integer | Total general de tareas |

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
  "error": 1305,
  "respuesta": "La fecha desde no puede ser posterior a fecha hasta",
  "resultado": {
    "errors": {
      "fecha_desde": ["La fecha desde no puede ser posterior a fecha hasta"]
    }
  }
}
```

**Códigos de error posibles:**
- `1305`: Rango de fechas inválido

---

## Validaciones

### A Nivel de Request

1. **Filtros de Fecha:**
   - `fecha_desde`: Formato YYYY-MM-DD (opcional)
   - `fecha_hasta`: Formato YYYY-MM-DD (opcional)
   - Si ambos se proporcionan: `fecha_desde` <= `fecha_hasta` (1305)

### A Nivel de Negocio

1. **Filtros por tipo de usuario:**
   - **Usuario Cliente:** Solo puede ver las tareas donde `cliente_id = usuario_autenticado.id` (filtro automático)
   - **Usuario No Supervisor:** Solo puede ver las tareas donde `usuario_id = usuario_autenticado.id` (filtro automático)
   - **Supervisor:** Puede ver todas las tareas (sin filtro automático)

2. **Agrupación:**
   - Agrupa por `cliente_id`
   - Suma `duracion_minutos` por cliente
   - Cuenta cantidad de tareas por cliente

3. **Ordenamiento:**
   - Ordena por `total_minutos` descendente (mayor dedicación primero)

4. **Resultado vacío:**
   - Si no hay tareas, retornar mensaje informativo
   - No presentar lista vacía ni habilitar exportación

---

## Operaciones de Base de Datos

### Tablas Involucradas

- `PQ_PARTES_registro_tarea` (SELECT con agregación)
- `PQ_PARTES_cliente` (LEFT JOIN)

### Consultas

```php
// Query Builder con agregación
$query = RegistroTarea::query()
    ->where('usuario_id', auth()->id())
    ->leftJoin('PQ_PARTES_cliente', 'PQ_PARTES_registro_tarea.cliente_id', '=', 'PQ_PARTES_cliente.id')
    ->leftJoin('PQ_PARTES_tipo_cliente', 'PQ_PARTES_cliente.tipo_cliente_id', '=', 'PQ_PARTES_tipo_cliente.id');

// Filtros opcionales
if ($request->has('fecha_desde')) {
    $query->where('PQ_PARTES_registro_tarea.fecha', '>=', $request->fecha_desde);
}

if ($request->has('fecha_hasta')) {
    $query->where('PQ_PARTES_registro_tarea.fecha', '<=', $request->fecha_hasta);
}

// Agrupación y agregación
$resumen = $query
    ->select([
        'PQ_PARTES_cliente.id as cliente_id',
        'PQ_PARTES_cliente.nombre as cliente_nombre',
        'PQ_PARTES_tipo_cliente.id as tipo_cliente_id',
        'PQ_PARTES_tipo_cliente.descripcion as tipo_cliente_descripcion',
        DB::raw('SUM(PQ_PARTES_registro_tarea.duracion_minutos) as total_minutos'),
        DB::raw('COUNT(PQ_PARTES_registro_tarea.id) as cantidad_tareas')
    ])
    ->groupBy(
        'PQ_PARTES_cliente.id', 
        'PQ_PARTES_cliente.nombre',
        'PQ_PARTES_tipo_cliente.id',
        'PQ_PARTES_tipo_cliente.descripcion'
    )
    ->orderBy('total_minutos', 'desc')
    ->get();

// Total general
$totalGeneral = RegistroTarea::where('usuario_id', auth()->id())
    ->when($request->has('fecha_desde'), function ($q) use ($request) {
        $q->where('fecha', '>=', $request->fecha_desde);
    })
    ->when($request->has('fecha_hasta'), function ($q) use ($request) {
        $q->where('fecha', '<=', $request->fecha_hasta);
    })
    ->sum('duracion_minutos');

$cantidadTotal = RegistroTarea::where('usuario_id', auth()->id())
    ->when($request->has('fecha_desde'), function ($q) use ($request) {
        $q->where('fecha', '>=', $request->fecha_desde);
    })
    ->when($request->has('fecha_hasta'), function ($q) use ($request) {
        $q->where('fecha', '<=', $request->fecha_hasta);
    })
    ->count();
```

### Índices Utilizados

- `idx_registro_usuario_fecha` - Filtro principal
- `idx_registro_cliente_fecha` - Agrupación por cliente

---

## Ejemplos de Uso

### cURL

```bash
# Resumen completo (todas las tareas)
curl -X GET "https://api.ejemplo.com/api/v1/tareas/resumen" \
  -H "Authorization: Bearer 1|abcdef1234567890" \
  -H "Accept: application/json"

# Resumen por período
curl -X GET "https://api.ejemplo.com/api/v1/tareas/resumen?fecha_desde=2025-01-01&fecha_hasta=2025-01-31" \
  -H "Authorization: Bearer 1|abcdef1234567890" \
  -H "Accept: application/json"
```

### JavaScript (Fetch)

```javascript
// Obtener resumen del mes actual
const hoy = new Date();
const primerDia = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
const ultimoDia = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);

const params = new URLSearchParams({
  fecha_desde: primerDia.toISOString().split('T')[0],
  fecha_hasta: ultimoDia.toISOString().split('T')[0]
});

const response = await fetch(`/api/v1/tareas/resumen?${params}`, {
  method: 'GET',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }
});

const data = await response.json();

if (data.error === 0) {
  console.log('Resumen por cliente:', data.resultado.resumen_por_cliente);
  console.log('Totales:', data.resultado.totales);
  
  // Mostrar en UI
  data.resultado.resumen_por_cliente.forEach(cliente => {
    console.log(`${cliente.cliente_nombre}: ${cliente.total_horas} horas`);
  });
}
```

---

## Notas

- Solo incluye tareas del usuario autenticado
- Se usa LEFT JOIN en lugar de subqueries para mejor performance
- Los totales se calculan de forma independiente para validación
- `total_horas` se calcula como `total_minutos / 60` con 2 decimales
- Si no hay tareas en el período, `resumen_por_cliente` será un array vacío
- Los clientes se ordenan por dedicación total (mayor a menor)

---

**Última actualización:** 2025-01-20

