# Endpoint: Consulta Agrupada por Fecha

## Información General

- **Método:** `GET`
- **Ruta:** `/api/v1/informes/por-fecha`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Obtiene tareas agrupadas por fecha con totales.

---

## Request

### Query Parameters

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| `fecha_desde` | string (date) | No | Fecha inicial |
| `fecha_hasta` | string (date) | No | Fecha final |
| `cliente_id` | integer | No | Filtrar por cliente (solo supervisores) |
| `usuario_id` | integer | No | Filtrar por asistente (solo supervisores) |
| `tipo_tarea_id` | integer | No | Filtrar por tipo de tarea |

---

## Response

### Success (200 OK)

```json
{
  "error": 0,
  "respuesta": "Informe obtenido correctamente",
  "resultado": {
    "agrupado_por_fecha": [
      {
        "fecha": "2025-01-20",
        "total_minutos": 240,
        "total_horas": 4.0,
        "cantidad_tareas": 2
      }
    ],
    "totales": {
      "total_minutos": 240,
      "total_horas": 4.0,
      "cantidad_tareas": 2
    }
  }
}
```

---

## Notas

- Filtros automáticos según rol
- Ordenamiento cronológico (más reciente primero o más antigua primero, según diseño)

---

**Última actualización:** 2025-01-20

