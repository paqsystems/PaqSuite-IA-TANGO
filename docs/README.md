# Guía de Navegación – Documentación del Proyecto

Este documento sirve como **índice de navegación** para toda la documentación del MVP de registro de tareas. Su objetivo es orientar al programador hacia los documentos correctos según su necesidad.

---

## Documentos Principales (raíz de `docs/`)

| Documento | Propósito | Cuándo leerlo |
|-----------|-----------|---------------|
| **`producto.md`** | Objetivo del producto, usuarios, funcionalidades del MVP | Al iniciar, para entender el qué |
| **`historias-y-tickets.md`** | Historias de usuario (MUST/SHOULD), criterios de aceptación, tickets técnicos | **Documento central** – antes de implementar cualquier funcionalidad |
| **`arquitectura.md`** | Arquitectura del sistema, componentes, decisiones de diseño | Para entender la estructura técnica |
| **`modelo-datos.md`** | Entidades, relaciones, restricciones, convenciones de BD | Al trabajar con datos o migraciones |
| **`.cursor/rules/12-testing.md`** | Estrategia de testing, tipos de tests, cómo ejecutarlos | Al escribir o ejecutar tests |
| **`deploy-ci-cd.md`** | Infraestructura, pipeline, deploy, secretos | Al desplegar o configurar CI/CD |
| **`reglas-negocio.md`** | Reglas de negocio adicionales, validaciones | Al implementar lógica de negocio |

---

## Por Área Técnica

### API

| Documento | Propósito |
|-----------|-----------|
| **`api/CONTRATO_BASE.md`** | Formato estándar de respuestas API, manejo de errores |
| **`api/openapi.md`** | Documentación OpenAPI de la API |

### Backend (Laravel)

| Documento | Propósito |
|-----------|-----------|
| **`backend/PLAYBOOK_BACKEND_LARAVEL.md`** | **Guía principal** – convenciones, estructura, validación, PHPDoc |
| **`backend/autenticacion.md`** | Flujo de autenticación |
| **`backend/tareas.md`** | Lógica de registro de tareas |

### Frontend (React)

| Documento | Propósito |
|-----------|-----------|
| **`frontend/frontend-specifications.md`** | Especificaciones generales, estructura de carpetas |
| **`frontend/ui-layer-wrappers.md`** | **Crítico** – reglas de UI Layer, componentes reutilizables |
| **`frontend/i18n.md`** | Internacionalización obligatoria, función `t()` |
| **`frontend/features/features-structure.md`** | Organización de features |
| **`frontend/testing.md`** | Testing frontend con Vitest y Playwright |

---

## Historias y Tareas

- **`hu-historias/`** – Historias de usuario en detalle (HU-001, HU-028, etc.)
- **`hu-tareas/`** – Tareas técnicas derivadas (TR-001, TR-028, etc.)

Cada archivo sigue el patrón `HU-XXX` o `TR-XXX` con clasificación MH (Must-Have) o SH (Should-Have).

---

## Entregas y Plantillas

- **`_ENTREGA-1/`** – Documentación técnica (instrucciones, verificación)
- **`_ENTREGA-2/`** – Código funcional (instrucciones, verificación)
- **`plantillas/`** – Plantillas para artefactos del proyecto

---

## Orden de Lectura Recomendado

Para un programador nuevo, seguir el **Manual del Programador** (`_MANUAL-PROGRAMADOR.MD` en la raíz), que define una **Ruta de Lectura** por fases. Esta carpeta `docs/` complementa esa ruta.

**Resumen rápido:**
1. `producto.md` → contexto funcional
2. `historias-y-tickets.md` → qué implementar
3. `arquitectura.md` + `modelo-datos.md` → diseño técnico
4. `backend/PLAYBOOK_BACKEND_LARAVEL.md` o `frontend/` según tu área

---

## Referencias Cruzadas

- **Especificaciones técnicas detalladas:** `specs/` (ver `specs/README.md`)
- **Manual del programador:** `_MANUAL-PROGRAMADOR.MD` (raíz)
- **Contexto del proyecto:** `_PROJECT_CONTEXT.md` (raíz)
- **Reglas del agente IA:** `AGENTS.md` (raíz)
