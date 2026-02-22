# Endpoint: Crear Tipo de Cliente

## Información General

- **Método:** `POST`
- **Ruta:** `/api/v1/tipos-cliente`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Crea un nuevo tipo de cliente. Solo accesible para supervisores.

---

## Request

### Body

```json
{
  "code": "CORP",
  "descripcion": "Corporativo",
  "activo": true
}
```

### Parámetros

| Campo | Tipo | Requerido | Descripción | Validaciones |
|-------|------|-----------|-------------|--------------|
| `code` | string | Sí | Código único del tipo | No vacío, único (1105, 4101) |
| `descripcion` | string | Sí | Descripción del tipo | No vacío, máximo 200 caracteres |
| `activo` | boolean | No | Estado activo | Default: true |

---

## Response

### Success (201 Created)

```json
{
  "error": 0,
  "respuesta": "Tipo de cliente creado correctamente",
  "resultado": {
    "id": 1,
    "code": "CORP",
    "descripcion": "Corporativo",
    "activo": true,
    "inhabilitado": false,
    "created_at": "2025-01-20T10:00:00Z"
  }
}
```

---

**Última actualización:** 2025-01-20

