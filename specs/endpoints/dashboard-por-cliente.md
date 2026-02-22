# Endpoint: Resumen por Cliente para Dashboard

## Información General

- **Método:** `GET`
- **Ruta:** `/api/v1/dashboard/por-cliente`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Obtiene el resumen de dedicación por cliente (top N) para el dashboard.

---

## Request

### Query Parameters

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| `fecha_desde` | string (date) | No | Fecha inicial |
| `fecha_hasta` | string (date) | No | Fecha final |
| `limit` | integer | No | Cantidad de clientes a retornar | Default: 5, máximo: 20 |

---

## Response

### Success (200 OK)

```json
{
  "error": 0,
  "respuesta": "Resumen por cliente obtenido correctamente",
  "resultado": {
    "top_clientes": [
      {
        "cliente_id": 1,
        "cliente_nombre": "Cliente A",
        "tipo_cliente": {
          "id": 1,
          "descripcion": "Corporativo"
        },
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

- Ordenamiento por total de horas descendente
- Limitado a top N (default: 5)
- Filtros automáticos según rol

---

**Última actualización:** 2025-01-20

