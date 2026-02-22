# Endpoint: Listar Clientes

## Información General

- **Método:** `GET`
- **Ruta:** `/api/v1/clientes`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Obtiene una lista paginada de clientes. Solo accesible para supervisores. Soporta filtros, búsqueda y paginación.

**Permisos:**
- **Solo supervisores** pueden acceder a este endpoint
- Si un usuario no supervisor intenta acceder, retornar error 403

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
| `page` | integer | No | Número de página | >= 1 (1301), default: 1 |
| `page_size` | integer | No | Tamaño de página | 1-100 (1302), default: 20 |
| `search` | string | No | Búsqueda por código o nombre | Máximo 100 caracteres |
| `tipo_cliente_id` | integer | No | Filtrar por tipo de cliente | Debe existir y estar activo/no inhabilitado |
| `activo` | boolean | No | Filtrar por estado activo | true/false |
| `inhabilitado` | boolean | No | Filtrar por estado inhabilitado | true/false |
| `sort` | string | No | Campo de ordenamiento | Whitelist (1303), default: "nombre" |
| `sort_dir` | string | No | Dirección de ordenamiento | "asc" o "desc" (1304), default: "asc" |

### Campos Permitidos para Ordenamiento (Whitelist)

- `code`
- `nombre`
- `created_at`
- `updated_at`

---

## Response

### Success (200 OK)

```json
{
  "error": 0,
  "respuesta": "Clientes obtenidos correctamente",
  "resultado": {
    "items": [
      {
        "id": 1,
        "code": "CLI001",
        "nombre": "Cliente A",
        "tipo_cliente": {
          "id": 1,
          "code": "CORP",
          "descripcion": "Corporativo"
        },
        "email": "cliente@ejemplo.com",
        "activo": true,
        "inhabilitado": false,
        "created_at": "2025-01-15T10:00:00Z",
        "updated_at": "2025-01-15T10:00:00Z"
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
| `resultado.items` | array | Lista de clientes |
| `resultado.items[].id` | integer | ID del cliente |
| `resultado.items[].code` | string | Código del cliente |
| `resultado.items[].nombre` | string | Nombre del cliente |
| `resultado.items[].tipo_cliente` | object | Tipo de cliente asociado |
| `resultado.items[].email` | string\|null | Email del cliente (opcional) |
| `resultado.items[].activo` | boolean | Estado activo |
| `resultado.items[].inhabilitado` | boolean | Estado inhabilitado |
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

### 403 Forbidden - No Autorizado

```json
{
  "error": 3101,
  "respuesta": "No tiene permisos para acceder a esta funcionalidad",
  "resultado": {}
}
```

**Códigos de error posibles:**
- `3101`: Acceso denegado (solo supervisores)

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

---

## Validaciones

### A Nivel de Request

1. **Permisos:**
   - El usuario debe ser supervisor (`supervisor = true`)
   - Si no es supervisor, retornar error 403

2. **Paginación:**
   - `page` debe ser >= 1
   - `page_size` debe estar entre 1 y 100

3. **Búsqueda:**
   - `search` se aplica a `code` y `nombre` (búsqueda parcial, case-insensitive)

### A Nivel de Negocio

1. **Filtros:**
   - `tipo_cliente_id` debe existir y estar activo/no inhabilitado
   - Los filtros se aplican en conjunto (AND lógico)

---

## Operaciones de Base de Datos

### Tablas Involucradas

- `PQ_PARTES_cliente` (SELECT con JOIN)
- `PQ_PARTES_tipo_cliente` (JOIN)

### Consultas

```php
$query = Cliente::with('tipoCliente')
    ->when($request->has('search'), function ($q) use ($request) {
        $search = $request->search;
        $q->where(function ($query) use ($search) {
            $query->where('code', 'LIKE', "%{$search}%")
                ->orWhere('nombre', 'LIKE', "%{$search}%");
        });
    })
    ->when($request->has('tipo_cliente_id'), function ($q) use ($request) {
        $q->where('tipo_cliente_id', $request->tipo_cliente_id);
    })
    ->when($request->has('activo'), function ($q) use ($request) {
        $q->where('activo', $request->activo);
    })
    ->when($request->has('inhabilitado'), function ($q) use ($request) {
        $q->where('inhabilitado', $request->inhabilitado);
    });

$sortField = $request->get('sort', 'nombre');
$sortDir = $request->get('sort_dir', 'asc');
$query->orderBy($sortField, $sortDir);

$clientes = $query->paginate($request->get('page_size', 20));
```

---

## Ejemplos de Uso

### cURL

```bash
# Listar todos los clientes
curl -X GET "https://api.ejemplo.com/api/v1/clientes" \
  -H "Authorization: Bearer 1|abcdef1234567890" \
  -H "Accept: application/json"

# Buscar clientes
curl -X GET "https://api.ejemplo.com/api/v1/clientes?search=Cliente" \
  -H "Authorization: Bearer 1|abcdef1234567890" \
  -H "Accept: application/json"

# Filtrar por tipo de cliente
curl -X GET "https://api.ejemplo.com/api/v1/clientes?tipo_cliente_id=1&activo=true" \
  -H "Authorization: Bearer 1|abcdef1234567890" \
  -H "Accept: application/json"
```

---

## Notas

- Solo supervisores pueden acceder a este endpoint
- Los clientes inhabilitados se muestran por defecto (pueden filtrarse)
- La búsqueda es case-insensitive y busca en código y nombre
- Se incluye la relación `tipo_cliente` en la respuesta

---

**Última actualización:** 2025-01-20

