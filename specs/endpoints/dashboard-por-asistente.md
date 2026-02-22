# Endpoint: Resumen por Empleado para Dashboard

## Información General

- **Método:** `GET`
- **Ruta:** `/api/v1/dashboard/por-empleado`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Obtiene el resumen de dedicación por empleado (top N) para el dashboard. Solo accesible para supervisores.

**Permisos:**
- **Solo supervisores** pueden acceder a este endpoint

---

## Request

### Query Parameters

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| `fecha_desde` | string (date) | No | Fecha inicial |
| `fecha_hasta` | string (date) | No | Fecha final |
| `limit` | integer | No | Cantidad de empleados a retornar | Default: 5, máximo: 20 |

---

## Response

### Success (200 OK)

```json
{
  "error": 0,
  "respuesta": "Resumen por empleado obtenido correctamente",
  "resultado": {
    "top_empleados": [
      {
        "empleado_id": 1,
        "empleado_code": "JPEREZ",
        "empleado_nombre": "Juan Pérez",
        "total_minutos": 480,
        "total_horas": 8.0,
        "cantidad_tareas": 4,
        "porcentaje": 66.67
      }
    ],
    "total_general": {
      "total_minutos": 720,
      "total_horas": 12.0,
      "cantidad_tareas": 6
    }
  }
}
```

---

## Notas

- Solo supervisores pueden acceder
- Ordenamiento por total de horas descendente
- Limitado a top N (default: 5)

---

**Última actualización:** 2025-01-20

