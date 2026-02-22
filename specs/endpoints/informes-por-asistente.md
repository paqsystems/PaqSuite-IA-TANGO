# Endpoint: Consulta Agrupada por Empleado

## Información General

- **Método:** `GET`
- **Ruta:** `/api/v1/informes/por-empleado`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Obtiene tareas agrupadas por empleado con totales. Solo accesible para supervisores.

**Permisos:**
- **Solo supervisores** pueden acceder a este endpoint

---

## Request

### Query Parameters

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| `fecha_desde` | string (date) | No | Fecha inicial |
| `fecha_hasta` | string (date) | No | Fecha final |
| `cliente_id` | integer | No | Filtrar por cliente |
| `tipo_tarea_id` | integer | No | Filtrar por tipo de tarea |

---

## Response

### Success (200 OK)

```json
{
  "error": 0,
  "respuesta": "Informe obtenido correctamente",
  "resultado": {
    "agrupado_por_empleado": [
      {
        "empleado_id": 1,
        "empleado_code": "JPEREZ",
        "empleado_nombre": "Juan Pérez",
        "total_minutos": 480,
        "total_horas": 8.0,
        "cantidad_tareas": 4
      }
    ],
    "totales": {
      "total_minutos": 480,
      "total_horas": 8.0,
      "cantidad_tareas": 4
    }
  }
}
```

---

## Notas

- Solo supervisores pueden acceder
- Agrupación por `empleado_id`
- Ordenamiento por total de horas descendente

---

**Última actualización:** 2025-01-20

