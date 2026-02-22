# Endpoint: Actualizar Tipo de Tarea

## Información General

- **Método:** `PUT`
- **Ruta:** `/api/v1/tipos-tarea/{id}`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Actualiza un tipo de tarea existente. El código (`code`) no es modificable.

---

## Request

### Body

```json
{
  "descripcion": "Desarrollo Actualizado",
  "is_generico": false,
  "is_default": false,
  "activo": true,
  "inhabilitado": false
}
```

---

## Response

### Success (200 OK)

```json
{
  "error": 0,
  "respuesta": "Tipo de tarea actualizado correctamente",
  "resultado": {
    "id": 1,
    "code": "TIPO001",
    "descripcion": "Desarrollo Actualizado",
    "is_generico": false,
    "is_default": false,
    "activo": true,
    "inhabilitado": false,
    "updated_at": "2025-01-20T11:00:00Z"
  }
}
```

---

## Errores

### 422 Unprocessable Entity

```json
{
  "error": 2117,
  "respuesta": "Solo puede haber un tipo de tarea por defecto",
  "resultado": {}
}
```

---

## Validaciones

### A Nivel de Negocio

1. **Regla de tipo por defecto:**
   - Si se establece `is_default = true`, verificar que no haya otro tipo (distinto al actual) con `is_default = true`
   - Si `is_default = true`, forzar `is_generico = true`

---

**Última actualización:** 2025-01-20

