# Endpoint: Resumen Ejecutivo del Dashboard

## Información General

- **Método:** `GET`
- **Ruta:** `/api/v1/dashboard/resumen`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Obtiene un resumen ejecutivo con KPIs principales del dashboard. Los datos se filtran automáticamente según el rol del usuario.

**Permisos y Filtros Automáticos:**
- **Usuario Cliente:** Solo ve datos de sus propias tareas (filtro automático por `cliente_id`)
- **Usuario No Supervisor:** Solo ve datos de sus propias tareas (filtro automático por `usuario_id`)
- **Supervisor:** Ve datos de todas las tareas

---

## Request

### Query Parameters

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| `fecha_desde` | string (date) | No | Fecha inicial del período | Default: primer día del mes actual |
| `fecha_hasta` | string (date) | No | Fecha final del período | Default: último día del mes actual |

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
    "kpis": {
      "total_horas": 120.5,
      "total_minutos": 7230,
      "cantidad_tareas": 45,
      "promedio_horas_por_dia": 3.89,
      "dias_con_tareas": 31
    },
    "distribucion_por_cliente": [
      {
        "cliente_id": 1,
        "cliente_nombre": "Cliente A",
        "total_horas": 60.0,
        "porcentaje": 49.79
      }
    ],
    "distribucion_por_tipo": [
      {
        "tipo_tarea_id": 1,
        "tipo_tarea_descripcion": "Desarrollo",
        "total_horas": 80.0,
        "porcentaje": 66.39
      }
    ]
  }
}
```

---

## Notas

- Los KPIs se calculan sobre el período especificado
- Filtros automáticos según rol
- Distribuciones limitadas a top N (ej: top 5 o top 10)

---

**Última actualización:** 2025-01-20

