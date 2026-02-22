# Consignas del Proyecto – MVP Web

## Descripción General

Este documento contiene las consignas y requisitos para el MVP (Minimum Viable Product) del proyecto final del Master de Lidr AI4Devs 2025.

**Referencia completa:** Ver `.cursor/consignas.md` para las consignas detalladas del Master.

---

## Alcance del MVP

### Flujo E2E prioritario

Definir un flujo End-to-End con principio y fin claros, que aporte valor completo.

**Flujo definido para este proyecto:**
```
Login → Registro de tarea diaria → Visualización de tareas / resumen
```

### Planificación del flujo

Para el flujo E2E se deben definir:
- 3–5 historias Must-Have (imprescindibles)
- 1–2 historias Should-Have (opcionales pero deseables)

**Estado actual:** 25 historias MUST-HAVE y 30 historias SHOULD-HAVE definidas en `docs/historias-y-tickets.md`

---

## Artefactos a producir

### 1. Documentación de producto
- Objetivo del sistema
- Características principales
- Funcionalidades del MVP

**Estado:** ✅ Completado en `docs/producto.md`

### 2. Historias de usuario y tickets
- Historias con criterios de aceptación claros
- Tickets con trazabilidad:
  - Historia asociada
  - Módulo
  - Impacto

**Estado:** ✅ Completado en `docs/historias-y-tickets.md` (55 historias, 33 tickets)

### 3. Arquitectura y modelo de datos
- Diagrama de arquitectura del sistema
- Modelo de datos:
  - Entidades
  - Relaciones
  - Restricciones

**Estado:** ✅ Completado en `docs/arquitectura.md` y `docs/modelo-datos.md`

### 4. Backend
- API o servicios con acceso a base de datos
- Operaciones necesarias para soportar el flujo E2E

**Estado:** ⏳ En desarrollo

### 5. Frontend
- Implementación usable del flujo E2E
- Navegación clara y coherente

**Estado:** ⏳ En desarrollo

### 6. Suite de tests
- Tests unitarios
- Tests de integración
- Al menos un test E2E del flujo principal

**Estado:** ✅ Playwright instalado y configurado. Tests pendientes de implementación.

### 7. Infra y despliegue
- Pipeline básico de CI/CD
- Gestión mínima de secretos
- URL pública accesible o entorno accesible para el evaluador

**Estado:** ⏳ Pendiente

### 8. Registro del uso de IA
- Prompts clave utilizados
- Herramientas de IA usadas
- Ejemplos de antes/después
- Explicación de ajustes humanos realizados

**Estado:** ✅ Documentado en `prompts.md` y `docs/ia-log.md`

---

## Referencias

- **Consignas completas del Master:** `.cursor/consignas.md`
- **Contexto del proyecto:** `PROJECT_CONTEXT.md`
- **Guía para agentes IA:** `AGENTS.md`
- **Historias y tickets:** `docs/historias-y-tickets.md`
- **Arquitectura:** `docs/arquitectura.md`
- **Modelo de datos:** `docs/modelo-datos.md`
- **Producto:** `docs/producto.md`
