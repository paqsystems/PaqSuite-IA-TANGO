# Endpoint: Actualizar Tipo de Cliente

## Información General

- **Método:** `PUT`
- **Ruta:** `/api/v1/tipos-cliente/{id}`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Actualiza un tipo de cliente existente. El código (`code`) no es modificable.

---

## Request

### Body

```json
{
  "descripcion": "Corporativo Actualizado",
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
  "respuesta": "Tipo de cliente actualizado correctamente",
  "resultado": {
    "id": 1,
    "code": "CORP",
    "descripcion": "Corporativo Actualizado",
    "activo": true,
    "inhabilitado": false,
    "updated_at": "2025-01-20T11:00:00Z"
  }
}
```

---

## Notas

- El código no es modificable

---

**Última actualización:** 2025-01-20

