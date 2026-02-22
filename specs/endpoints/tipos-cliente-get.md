# Endpoint: Obtener Tipo de Cliente

## Información General

- **Método:** `GET`
- **Ruta:** `/api/v1/tipos-cliente/{id}`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Obtiene el detalle de un tipo de cliente específico. Solo accesible para supervisores.

---

## Response

### Success (200 OK)

```json
{
  "error": 0,
  "respuesta": "Tipo de cliente obtenido correctamente",
  "resultado": {
    "id": 1,
    "code": "CORP",
    "descripcion": "Corporativo",
    "activo": true,
    "inhabilitado": false,
    "created_at": "2025-01-15T10:00:00Z",
    "updated_at": "2025-01-15T10:00:00Z"
  }
}
```

---

**Última actualización:** 2025-01-20

