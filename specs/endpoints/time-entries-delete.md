# Endpoint: Eliminar Registro de Tarea

## Información General

- **Método:** `DELETE`
- **Ruta:** `/api/v1/tareas/{id}`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Elimina un registro de tarea. Los usuarios normales solo pueden eliminar sus propias tareas. Los supervisores pueden eliminar cualquier tarea. No se puede eliminar una tarea cerrada.

**Permisos:**
- **Usuario normal:** Solo puede eliminar sus propias tareas
- **Supervisor:** Puede eliminar cualquier tarea
- **Tarea cerrada:** No se puede eliminar (error 2111)

---

## Request

### Headers

```
Authorization: Bearer {token}
Accept: application/json
```

### Path Parameters

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| `id` | integer | Sí | ID del registro de tarea |

---

## Response

### Success (200 OK)

```json
{
  "error": 0,
  "respuesta": "Tarea eliminada correctamente",
  "resultado": {}
}
```

---

## Errores

### 404 Not Found

```json
{
  "error": 4005,
  "respuesta": "Tarea no encontrada",
  "resultado": {}
}
```

### 403 Forbidden - No Autorizado

```json
{
  "error": 2104,
  "respuesta": "No se puede eliminar tarea de otro usuario",
  "resultado": {}
}
```

### 422 Unprocessable Entity - Tarea Cerrada

```json
{
  "error": 2111,
  "respuesta": "No se puede eliminar una tarea cerrada",
  "resultado": {}
}
```

**Códigos de error posibles:**
- `4005`: Tarea no encontrada
- `2104`: No se puede eliminar tarea de otro usuario (solo usuarios normales)
- `2111`: No se puede eliminar una tarea cerrada

---

## Validaciones

### A Nivel de Request

1. **Permisos:**
   - Usuario normal: Solo puede eliminar tareas donde `usuario_id = usuario_autenticado.id`
   - Supervisor: Puede eliminar cualquier tarea

2. **Estado de tarea:**
   - La tarea no debe estar cerrada (`cerrado = false`)
   - Si está cerrada, retornar error 2111

---

## Operaciones de Base de Datos

### Tablas Involucradas

- `PQ_PARTES_registro_tarea` (DELETE)

---

**Última actualización:** 2025-01-20

