# Endpoint: Consulta Agrupada por Cliente

## Información General

- **Método:** `GET`
- **Ruta:** `/api/v1/informes/por-cliente`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Obtiene tareas agrupadas por cliente con totales. Similar a `reports-time-summary.md` pero con estructura de informe.

---

## Request

### Query Parameters

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| `fecha_desde` | string (date) | No | Fecha inicial |
| `fecha_hasta` | string (date) | No | Fecha final |
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
    "agrupado_por_cliente": [
      {
        "cliente_id": 1,
        "cliente_nombre": "Cliente A",
        "tipo_cliente": {
          "id": 1,
          "descripcion": "Corporativo"
        },
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

- Filtros automáticos según rol (cliente solo sus tareas, usuario normal solo sus tareas)
- Ordenamiento por total de horas descendente

---

**Última actualización:** 2025-01-20

