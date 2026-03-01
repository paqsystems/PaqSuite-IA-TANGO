# TR-002 – Cambio de empresa activa

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-002 – Cambio de empresa activa         |
| Épica              | 000 – Generalidades                        |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Usuario con acceso a varias empresas       |
| Dependencias       | HU-001 (Login)                             |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-27                               |
| Estado             | Implementado                               |

**Origen:** [HU-002 – Cambio de empresa activa](../../03-historias-usuario/000-Generalidades/HU-002-cambio-empresa-activa.md)

---

## 1) HU Refinada

- **Título:** Cambio de empresa activa
- **Narrativa:** Como usuario con acceso a varias empresas quiero cambiar la empresa activa sin cerrar sesión para operar en otra empresa sin volver a autenticarme.
- **Contexto:** Selector en menú de usuario. Solo empresas con permiso en Pq_Permiso. Header X-Company-Id. Token no se invalida.
- **In scope:** Cambio desde menú usuario, actualización contexto, recarga vista, header X-Company-Id.
- **Out of scope:** Selección inicial post-login (cubierto en 001-Seguridad TR-002).

---

## 2) Criterios de Aceptación

- Cambiar empresa activa desde menú de usuario (debajo del avatar).
- Solo empresas con permiso (Pq_Permiso). Al seleccionar: actualizar contexto, recargar/ajustar vista.
- Nombre empresa visible en header/barra. Peticiones incluyen X-Company-Id.
- Si una sola empresa: selector oculto o deshabilitado (solo lectura).
- Cambio no invalida token; usuario permanece autenticado.

### Escenarios Gherkin

```gherkin
Feature: Cambio de empresa activa

  Scenario: Usuario con varias empresas cambia la activa
    Given el usuario está autenticado
    And tiene permiso en Empresa A y Empresa B
    And la empresa activa es Empresa A
    When abre el selector de empresa (CompanySwitcher)
    And selecciona Empresa B
    Then la empresa activa pasa a ser Empresa B
    And la vista se recarga o ajusta al nuevo contexto
    And las peticiones incluyen X-Company-Id de Empresa B
    And el usuario permanece autenticado (sin logout)

  Scenario: Usuario con una sola empresa
    Given el usuario está autenticado
    And tiene permiso solo en Empresa A
    When accede al menú de usuario
    Then el selector de empresa está oculto o deshabilitado
    And la empresa activa es Empresa A por defecto

  Scenario: Backend valida X-Company-Id
    Given el usuario está autenticado
    When envía una petición con X-Company-Id de empresa sin permiso
    Then el backend responde 403 (empresa no autorizada)
```

---

## 3) Reglas de Negocio

- Solo empresas con permiso. Empresa activa en estado global frontend, enviada en cada request. Backend valida X-Company-Id.

---

## 4) Impacto en Datos

- Tablas Pq_Permiso, PQ_Empresa (ya existen).

---

## 5) Contratos de API

- GET /api/empresas/permisos-usuario (o equivalente). Middleware validación X-Company-Id.
- Nota: Puede compartir implementación con 001-Seguridad TR-002 (Selección empresa).

---

## 6) Cambios Frontend

- CompanySwitcher en menú usuario; al cambiar: actualizar estado, recargar vista, enviar X-Company-Id.
- data-testid: companySwitcher, companySwitcher.option

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD | Deps |
|----|------|-------------|-----|------|
| T1 | Backend | Endpoint empresas del usuario (si no existe) | Lista según Pq_Permiso | - |
| T2 | Backend | Middleware X-Company-Id (si no existe) | 403 si no autorizado | - |
| T3 | Frontend | CompanySwitcher en UserMenu | Cambio sin logout | HU-001 |
| T4 | Frontend | Recarga/ajuste vista al cambiar empresa | Contexto actualizado | T3 |
| T5 | Tests | E2E: cambiar empresa desde menú, verificar contexto | Playwright | T4 |

---

## Nota sobre dependencias

Esta TR complementa la funcionalidad de 001-Seguridad TR-002 (Selección empresa). Si ambas se implementan juntas, unificar en un solo CompanySwitcher reutilizable.

---

## Archivos creados/modificados

<!-- Completar al ejecutar -->

## Comandos ejecutados

<!-- Completar al ejecutar -->

## Notas y decisiones

<!-- Completar al ejecutar -->

## Pendientes / follow-ups

<!-- Completar al ejecutar -->
