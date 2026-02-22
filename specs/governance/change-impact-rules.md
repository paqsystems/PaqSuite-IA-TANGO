# 📄 Archivo 2 — `change-impact-rules.md`

```md
# Reglas de Impacto de Cambios (Código, Documentación y Tests)

Estas reglas aplican **cada vez que se modifica código existente**.

El objetivo es mantener coherencia entre:
- Código
- Documentación
- Especificaciones
- Tests

---

## Regla Principal

Ningún cambio de código se considera completo si no se actualizan
**todos los artefactos impactados** por dicho cambio.

---

## Actualizaciones Obligatorias

Ante una modificación de código, se DEBE:

### 1. Documentación
Actualizar toda la documentación afectada, incluyendo:
- Specs de endpoints
- Flujos E2E
- Reglas de negocio
- Documentación de arquitectura
- Contratos (por ejemplo, formato de respuesta)

### 2. Tests
Actualizar o agregar los tests correspondientes:
- Tests unitarios
- Tests de integración
- Tests E2E (si el cambio impacta el flujo completo)

Los tests deben:
- Reflejar el nuevo comportamiento.
- Validar las reglas actualizadas.
- Pasar correctamente.

---

## Regla de Consistencia

No debe existir:
- Documentación desactualizada respecto al código.
- Tests que validen comportamientos obsoletos.
- Cambios de comportamiento sin reflejo en specs.

---

## Ejemplos de Impacto

- Cambio en una regla de negocio → actualizar specs + tests.
- Cambio en un modelo/entidad → actualizar arquitectura + validaciones + tests.
- Cambio en una respuesta de API → actualizar contrato + frontend + tests.
- Cambio en autenticación/autorización → actualizar flow E2E + specs + tests.

---

## Prácticas Prohibidas

- Comentar tests en lugar de corregirlos.
- Dejar documentación obsoleta.
- Ajustar solo el código “para que funcione” sin actualizar specs.
- Introducir comportamiento nuevo sin reflejarlo en documentación.

---

## Cumplimiento

Si algún artefacto impactado no fue actualizado,
el cambio debe ser marcado explícitamente como incompleto.
