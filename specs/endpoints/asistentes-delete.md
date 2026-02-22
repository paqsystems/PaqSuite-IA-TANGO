# Endpoint: Eliminar Empleado

## Información General

- **Método:** `DELETE`
- **Ruta:** `/api/v1/empleados/{id}`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Elimina un empleado. No se puede eliminar si tiene tareas asociadas.

---

## Response

### Success (200 OK)

```json
{
  "error": 0,
  "respuesta": "Empleado eliminado correctamente",
  "resultado": {}
}
```

---

## Errores

### 422 Unprocessable Entity

```json
{
  "error": 2113,
  "respuesta": "No se puede eliminar un empleado que tiene tareas asociadas",
  "resultado": {}
}
```

**Códigos de error posibles:**
- `2113`: No se puede eliminar un empleado con tareas asociadas

---

## Validaciones

### A Nivel de Negocio

1. **Integridad referencial:**
   - Verificar que no tenga tareas asociadas en `PQ_PARTES_registro_tarea`
   - Si tiene tareas, retornar error 2113

---

**Última actualización:** 2025-01-20

