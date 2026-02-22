# Endpoint: Listar Tipos de Cliente

## Información General

- **Método:** `GET`
- **Ruta:** `/api/v1/tipos-cliente`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Obtiene la lista de tipos de cliente activos disponibles en el sistema. Este catálogo se utiliza para clasificar los clientes.

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
| `activo` | boolean | No | Filtrar por estado activo | Default: true |

---

## Response

### Success (200 OK)

```json
{
  "error": 0,
  "respuesta": "Tipos de cliente obtenidos correctamente",
  "resultado": [
    {
      "id": 1,
      "descripcion": "Corporativo",
      "activo": true,
      "inhabilitado": false
    },
    {
      "id": 2,
      "descripcion": "PyME",
      "activo": true,
      "inhabilitado": false
    },
    {
      "id": 3,
      "descripcion": "Startup",
      "activo": true,
      "inhabilitado": false
    }
  ]
}
```

### Campos de Respuesta

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `resultado` | array | Lista de tipos de cliente |
| `resultado[].id` | integer | ID del tipo de cliente |
| `resultado[].descripcion` | string | Descripción del tipo de cliente |
| `resultado[].activo` | boolean | Indica si el tipo está activo |
| `resultado[].inhabilitado` | boolean | Indica si el tipo está inhabilitado (default: false) |

---

## Errores

### 3001 - No autenticado

```json
{
  "error": 3001,
  "respuesta": "No autenticado",
  "resultado": {}
}
```

### 3002 - Token inválido o expirado

```json
{
  "error": 3002,
  "respuesta": "Token inválido o expirado",
  "resultado": {}
}
```

---

## Operaciones de Base de Datos

### Tablas Involucradas

- `PQ_PARTES_tipo_cliente` (SELECT)

### Consultas

```php
// Eloquent
$tiposCliente = TipoCliente::where('activo', $request->get('activo', true))
    ->where('inhabilitado', false)
    ->orderBy('descripcion', 'asc')
    ->get(['id', 'descripcion', 'activo', 'inhabilitado']);
```

**Validaciones:**
- Usuario autenticado (3001)
- Token válido (3002)

**Índices Utilizados:**
- `idx_tipo_cliente_activo`

**Response Mapping:**
```php
// Modelo → Response
$tiposCliente->map(function ($tipo) {
    return [
        'id' => $tipo->id,
        'descripcion' => $tipo->descripcion,
        'activo' => $tipo->activo,
        'inhabilitado' => (bool) $tipo->inhabilitado
    ];
});
```

---

## Ejemplo de Uso

### JavaScript/TypeScript

```typescript
const response = await fetch('/api/v1/tipos-cliente', {
  method: 'GET',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }
});

const data = await response.json();

if (data.error === 0) {
  console.log('Tipos de cliente:', data.resultado);
  // data.resultado es un array de tipos de cliente
}
```

---

**Última actualización:** 2025-01-20

