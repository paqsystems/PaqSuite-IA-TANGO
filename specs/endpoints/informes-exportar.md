# Endpoint: Exportar Informe a Excel

## Información General

- **Método:** `GET`
- **Ruta:** `/api/v1/informes/exportar`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Exporta los resultados de una consulta a Excel. Soporta todos los tipos de consulta (detalle, por asistente, por cliente, por tipo, por fecha).

---

## Request

### Query Parameters

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| `tipo` | string | Sí | Tipo de consulta | "detalle", "por-asistente", "por-cliente", "por-tipo", "por-fecha" |
| `fecha_desde` | string (date) | No | Fecha inicial |
| `fecha_hasta` | string (date) | No | Fecha final |
| `cliente_id` | integer | No | Filtrar por cliente |
| `usuario_id` | integer | No | Filtrar por asistente |
| `tipo_tarea_id` | integer | No | Filtrar por tipo de tarea |
| `tipo_cliente_id` | integer | No | Filtrar por tipo de cliente |

---

## Response

### Success (200 OK)

**Content-Type:** `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`

**Headers:**
```
Content-Disposition: attachment; filename="Tareas_2025-01-01_2025-01-31.xlsx"
Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
```

**Body:** Archivo Excel binario (XLSX)

---

## Errores

### 422 Unprocessable Entity

```json
{
  "error": 2120,
  "respuesta": "No hay datos para exportar",
  "resultado": {}
}
```

**Códigos de error posibles:**
- `2120`: No hay datos para exportar

---

## Validaciones

### A Nivel de Negocio

1. **Resultados vacíos:**
   - Si no hay resultados para los filtros, retornar error 2120
   - No generar archivo si no hay datos

2. **Formato de horas:**
   - Las horas se exportan en formato decimal (minutos / 60)

3. **Nombre de archivo:**
   - Incluir tipo de consulta y período en el nombre
   - Ejemplo: `Tareas_Detalle_2025-01-01_2025-01-31.xlsx`

---

## Notas

- El archivo se descarga directamente
- Formato XLSX compatible con Excel
- Estructura del archivo depende del tipo de consulta

---

**Última actualización:** 2025-01-20

