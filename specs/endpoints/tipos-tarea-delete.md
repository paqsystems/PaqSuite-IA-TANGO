# Endpoint: Eliminar Tipo de Tarea

## Información General

- **Método:** `DELETE`
- **Ruta:** `/api/v1/tipos-tarea/{id}`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Elimina un tipo de tarea. No se puede eliminar si tiene tareas asociadas o clientes asignados.

---

## Response

### Success (200 OK)

```json
{
  "error": 0,
  "respuesta": "Tipo de tarea eliminado correctamente",
  "resultado": {}
}
```

---

## Errores

### 422 Unprocessable Entity

```json
{
  "error": 2114,
  "respuesta": "No se puede eliminar un tipo de tarea que está en uso",
  "resultado": {}
}
```

**Códigos de error posibles:**
- `2114`: No se puede eliminar un tipo de tarea en uso

---

## Validaciones

### A Nivel de Negocio

1. **Integridad referencial:**
   - Verificar que no tenga tareas asociadas en `PQ_PARTES_registro_tarea`
   - Verificar que no tenga clientes asignados en `PQ_PARTES_cliente_tipo_tarea`
   - Si tiene referencias, retornar error 2114

---

**Última actualización:** 2025-01-20

