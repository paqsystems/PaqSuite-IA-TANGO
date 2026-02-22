# Endpoint: Eliminar Tipo de Cliente

## Información General

- **Método:** `DELETE`
- **Ruta:** `/api/v1/tipos-cliente/{id}`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Elimina un tipo de cliente. No se puede eliminar si tiene clientes asociados.

---

## Response

### Success (200 OK)

```json
{
  "error": 0,
  "respuesta": "Tipo de cliente eliminado correctamente",
  "resultado": {}
}
```

---

## Errores

### 422 Unprocessable Entity

```json
{
  "error": 2115,
  "respuesta": "No se puede eliminar un tipo de cliente que tiene clientes asociados",
  "resultado": {}
}
```

**Códigos de error posibles:**
- `2115`: No se puede eliminar un tipo de cliente con clientes asociados

---

## Validaciones

### A Nivel de Negocio

1. **Integridad referencial:**
   - Verificar que no haya clientes asociados en `PQ_PARTES_cliente`
   - Si hay clientes, retornar error 2115

---

**Última actualización:** 2025-01-20

