# Endpoint: Consulta Agrupada por Tipo de Tarea

## Información General

- **Método:** `GET`
- **Ruta:** `/api/v1/informes/por-tipo`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Obtiene tareas agrupadas por tipo de tarea con totales. Solo accesible para supervisores.

---

## Request

### Query Parameters

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| `fecha_desde` | string (date) | No | Fecha inicial |
| `fecha_hasta` | string (date) | No | Fecha final |
| `cliente_id` | integer | No | Filtrar por cliente |
| `usuario_id` | integer | No | Filtrar por asistente |

---

## Response

### Success (200 OK)

```json
{
  "error": 0,
  "respuesta": "Informe obtenido correctamente",
  "resultado": {
    "agrupado_por_tipo": [
      {
        "tipo_tarea_id": 1,
        "tipo_tarea_code": "TIPO001",
        "tipo_tarea_descripcion": "Desarrollo",
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
- Agrupación por `tipo_tarea_id`
- Ordenamiento por total de horas descendente

---

**Última actualización:** 2025-01-20

