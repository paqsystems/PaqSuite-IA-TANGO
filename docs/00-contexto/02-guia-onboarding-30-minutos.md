# Guía de Onboarding – 30 Minutos para Entender el ERP

**Nivel : Operativo (Uso General)**

## Objetivo

Permitir que un programador nuevo comprenda:

- Cómo está organizado el ERP
- Cómo se trabaja
- Qué debe leer antes de programar
- Cómo comenzar sin romper el sistema

Esta guía debe leerse el primer día de incorporación.

---

# ⏱ Minuto 0–5: Comprender el Sistema

Leer en este orden:

1. `docs/00-contexto/00-contexto-global-erp.md`
2. `docs/00-contexto/01-guia-estructura-documental-corporativa.md`

Objetivo:

- Entender qué es el ERP
- Comprender modelo multiempresa
- Comprender modelo de seguridad
- Entender separación diccionario / empresa

---

# ⏱ Minuto 5–10: Entender Arquitectura

Leer:

- `docs/01-arquitectura/01-arquitectura-proyecto.md`
- ADR existentes

Objetivo:

- Entender estructura en capas
- Entender cómo se resuelve el tenant
- Comprender modelo de autenticación
- Comprender responsabilidades de cada capa

---

# ⏱ Minuto 10–15: Entender el Producto

Leer:

- `docs/02-producto/flujos-e2e.md`
- Documento del módulo actual en `docs/02-producto/modules/`

Objetivo:

- Entender comportamiento funcional
- Entender qué problema resuelve el módulo
- Entender reglas funcionales

---

# ⏱ Minuto 15–20: Entender el Trabajo Actual

Leer:

- HU activas en `docs/03-historias-usuario/`
- TR asociadas en `docs/04-tareas/`

Objetivo:

- Entender qué se está desarrollando
- Entender por qué se está desarrollando
- Entender criterios de aceptación

---

# ⏱ Minuto 20–25: Entender Testing

Leer:

- `docs/05-testing/estrategia-testing.md`
- `docs/05-testing/matriz-trazabilidad.md`

Objetivo:

- Comprender cómo se valida el sistema
- Entender obligatoriedad de tests mínimos
- Comprender trazabilidad HU → TR → Tests

---

# ⏱ Minuto 25–30: Antes de Programar

Confirmar:

- ¿La HU está clara?
- ¿Existe impacto arquitectónico?
- ¿Requiere ADR?
- ¿Las TR están definidas?
- ¿Existe test mínimo definido?

Si alguna respuesta es NO:

Detenerse y consultar antes de programar.

---

# Reglas Fundamentales del ERP

1. No programar sin HU.
2. No modificar arquitectura sin documentar.
3. No mezclar responsabilidades entre capas.
4. No persistir datos operativos en la base diccionario.
5. No confiar en el tenant sin validación.
6. No cerrar tareas sin tests.

---

# Primer Día – Acciones Concretas

1. Clonar repositorio.
2. Ejecutar proyecto local.
3. Ejecutar todos los tests.
4. Revisar flujo E2E activo.
5. Preguntar dudas antes de modificar código.

---

# Cultura del Proyecto

En este ERP:

- La arquitectura precede al código.
- La documentación precede a la implementación.
- El testing precede al cierre de tareas.
- El orden precede a la velocidad.

El objetivo no es programar rápido.
El objetivo es construir un sistema escalable y mantenible.

---

# Conclusión

Si en 30 minutos comprendiste:

- El modelo conceptual
- La arquitectura
- El flujo E2E
- La estructura documental

Estás listo para comenzar a trabajar profesionalmente dentro del ERP.
