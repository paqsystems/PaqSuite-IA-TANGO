# Endpoint: Consulta Detallada de Tareas

## Información General

- **Método:** `GET`
- **Ruta:** `/api/v1/informes/detalle`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Obtiene un listado detallado de tareas con filtros. Los resultados se filtran automáticamente según el rol del usuario.

**Permisos y Filtros Automáticos:**
- **Usuario Cliente:** Solo ve tareas donde `cliente_id = usuario_autenticado.id` (filtro automático)
- **Usuario No Supervisor:** Solo ve tareas donde `usuario_id = usuario_autenticado.id` (filtro automático)
- **Supervisor:** Ve todas las tareas (sin filtro automático)

---

## Request

### Query Parameters

| Parámetro | Tipo | Requerido | Descripción | Validaciones |
|-----------|------|-----------|-------------|--------------|
| `fecha_desde` | string (date) | No | Fecha inicial | Formato YYYY-MM-DD |
| `fecha_hasta` | string (date) | No | Fecha final | Formato YYYY-MM-DD, >= fecha_desde (1305) |
| `tipo_cliente_id` | integer | No | Filtrar por tipo de cliente | Solo para supervisores. Clientes no ven este filtro |
| `cliente_id` | integer | No | Filtrar por cliente | Solo para supervisores. Clientes no ven este filtro (ya filtrado automáticamente) |
| `usuario_id` | integer | No | Filtrar por asistente | Solo para supervisores. Usuarios normales no ven este filtro (ya filtrado automáticamente) |
| `tipo_tarea_id` | integer | No | Filtrar por tipo de tarea | Debe existir y estar activo |
| `page` | integer | No | Número de página | >= 1, default: 1 |
| `page_size` | integer | No | Tamaño de página | 1-100, default: 20 |
| `sort` | string | No | Campo de ordenamiento | Whitelist, default: "fecha" |
| `sort_dir` | string | No | Dirección | "asc" o "desc", default: "desc" |

---

## Response

### Success (200 OK)

```json
{
  "error": 0,
  "respuesta": "Tareas obtenidas correctamente",
  "resultado": {
    "items": [
      {
        "id": 1,
        "usuario": {
          "id": 1,
          "code": "JPEREZ",
          "nombre": "Juan Pérez"
        },
        "cliente": {
          "id": 1,
          "nombre": "Cliente A",
          "tipo_cliente": {
            "id": 1,
            "descripcion": "Corporativo"
          }
        },
        "tipo_tarea": {
          "id": 1,
          "descripcion": "Desarrollo"
        },
        "fecha": "2025-01-20",
        "duracion_minutos": 120,
        "duracion_horas": 2.0,
        "sin_cargo": false,
        "presencial": false,
        "observacion": "Desarrollo de funcionalidad X",
        "cerrado": false
      }
    ],
    "page": 1,
    "page_size": 20,
    "total": 1,
    "total_pages": 1,
    "totales": {
      "total_minutos": 120,
      "total_horas": 2.0,
      "cantidad_tareas": 1
    }
  }
}
```

---

## Notas

- Los filtros automáticos se aplican según el rol
- Si no hay resultados, retornar mensaje informativo (no lista vacía)
- Los totales se calculan sobre los resultados filtrados

---

**Última actualización:** 2025-01-20

