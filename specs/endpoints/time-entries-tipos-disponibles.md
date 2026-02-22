# Endpoint: Obtener Tipos de Tarea Disponibles para Cliente

## Información General

- **Método:** `GET`
- **Ruta:** `/api/v1/tareas/tipos-disponibles`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Obtiene la lista de tipos de tarea disponibles para un cliente específico. Incluye tipos genéricos y tipos NO genéricos asignados al cliente.

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
| `cliente_id` | integer | Sí | ID del cliente | Debe existir y estar activo/no inhabilitado |

---

## Response

### Success (200 OK)

```json
{
  "error": 0,
  "respuesta": "Tipos de tarea obtenidos correctamente",
  "resultado": [
    {
      "id": 1,
      "code": "TIPO001",
      "descripcion": "Desarrollo",
      "is_generico": true,
      "is_default": true,
      "activo": true
    },
    {
      "id": 2,
      "code": "TIPO002",
      "descripcion": "Tipo Específico",
      "is_generico": false,
      "is_default": false,
      "activo": true
    }
  ]
}
```

### Campos de Respuesta

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `resultado[].id` | integer | ID del tipo de tarea |
| `resultado[].code` | string | Código del tipo |
| `resultado[].descripcion` | string | Descripción |
| `resultado[].is_generico` | boolean | Indica si es genérico |
| `resultado[].is_default` | boolean | Indica si es por defecto |
| `resultado[].activo` | boolean | Estado activo |

---

## Validaciones

### A Nivel de Negocio

1. **Tipos incluidos:**
   - Todos los tipos genéricos (`is_generico = true`) activos y no inhabilitados
   - Tipos NO genéricos (`is_generico = false`) asignados al cliente en `ClienteTipoTarea` activos y no inhabilitados

---

## Operaciones de Base de Datos

### Consultas

```php
// Tipos genéricos
$tiposGenericos = TipoTarea::where('is_generico', true)
    ->where('activo', true)
    ->where('inhabilitado', false)
    ->get();

// Tipos específicos asignados al cliente
$tiposEspecificos = TipoTarea::whereHas('clientes', function ($query) use ($clienteId) {
        $query->where('cliente_id', $clienteId);
    })
    ->where('is_generico', false)
    ->where('activo', true)
    ->where('inhabilitado', false)
    ->get();

$tiposDisponibles = $tiposGenericos->merge($tiposEspecificos);
```

---

**Última actualización:** 2025-01-20

