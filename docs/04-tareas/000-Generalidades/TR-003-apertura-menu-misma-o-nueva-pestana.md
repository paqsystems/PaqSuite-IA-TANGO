# TR-003 – Apertura de opción de menú en misma o nueva pestaña

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-003 – Apertura menú misma/nueva pestaña |
| Épica              | 000 – Generalidades                        |
| Prioridad          | SHOULD-HAVE                                |
| Roles              | Usuario del frontend web                   |
| Dependencias       | HU-001 (Login)                             |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-27                               |
| Estado             | Implementado                               |

**Origen:** [HU-003 – Apertura de opción de menú en misma o nueva pestaña](../../03-historias-usuario/000-Generalidades/HU-003-apertura-menu-misma-o-nueva-pestana.md)

---

## 1) HU Refinada

- **Título:** Apertura de opción de menú en misma o nueva pestaña
- **Narrativa:** Como usuario del frontend web quiero elegir si las opciones del menú se abren en la misma pestaña o en una nueva.
- **Contexto:** Preferencia en users.menu_abrir_nueva_pestana. Toggle en menú usuario. Solo frontend web (no mobile).
- **In scope:** Toggle configuración, persistencia server-side, navegación según preferencia.
- **Out of scope:** Mobile – menú siempre mismo contexto.

---

## 2) Criterios de Aceptación

- Preferencia: misma pestaña (default) o nueva pestaña. Se aplica al clic en opción de menú.
- Misma pestaña: navegación SPA estándar. Nueva pestaña: target blank o equivalente.
- Persistencia en users.menu_abrir_nueva_pestana (Dictionary DB). Toggle en menú usuario.
- Nueva pestaña mantiene contexto (token, empresa activa) para no re-autenticar.
- Solo frontend web; mobile no ofrece esta opción.

### Escenarios Gherkin

```gherkin
Feature: Apertura de menú en misma o nueva pestaña

  Scenario: Usuario activa "Abrir en nueva pestaña"
    Given el usuario está autenticado
    And la preferencia actual es "misma pestaña"
    When activa el toggle "Abrir en nueva pestaña" en el menú usuario
    Then la preferencia se persiste en backend (menu_abrir_nueva_pestana=true)
    And al hacer clic en una opción del menú se abre en nueva pestaña

  Scenario: Clic en menú con preferencia "nueva pestaña"
    Given el usuario está autenticado
    And tiene activado "Abrir en nueva pestaña"
    When hace clic en una opción del menú lateral (ej. Usuarios)
    Then se abre en una nueva pestaña del navegador
    And la nueva pestaña mantiene sesión (token, empresa activa)
    And no requiere re-autenticación

  Scenario: Clic en menú con preferencia "misma pestaña" (default)
    Given el usuario está autenticado
    And tiene desactivado "Abrir en nueva pestaña" (o default)
    When hace clic en una opción del menú lateral
    Then la navegación ocurre en la misma pestaña (SPA estándar)
```

---

## 3) Reglas de Negocio

- Default: misma pestaña. Preferencia por usuario, no por empresa. Persistencia server-side.

---

## 4) Impacto en Datos

- Tabla users: campo menu_abrir_nueva_pestana (bit). Ya existe en modelo.

---

## 5) Contratos de API

- GET/PUT preferencias usuario (menu_abrir_nueva_pestana). O en payload de login.
- Endpoint: PUT /api/users/me/preferences con menuAbrirNuevaPestana boolean

---

## 6) Cambios Frontend

- Toggle Abrir en nueva pestaña en UserMenu. Lógica navegación según preferencia.
- Nueva pestaña: pasar token y empresa activa (localStorage compartido).
- data-testid: userMenu.openInNewTab

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD | Deps |
|----|------|-------------|-----|------|
| T1 | Backend | Endpoint preferencias GET/PUT | Persistencia operativa | - |
| T2 | Frontend | Toggle en UserMenu | Cambio preferencia | HU-001 |
| T3 | Frontend | Lógica navegación menú según preferencia | target o navigate según toggle | T2 |
| T4 | Frontend | Nueva pestaña: mantener token y empresa activa | Sin re-login en nueva pestaña | T3 |
| T5 | Tests | Integration: actualizar preferencia | Tests pasan | T1 |
| T6 | Tests | E2E: toggle nueva pestaña, clic menú, verificar nueva pestaña con sesión | Playwright | T4 |

---

## Archivos creados/modificados

<!-- Completar al ejecutar -->

## Comandos ejecutados

<!-- Completar al ejecutar -->

## Notas y decisiones

<!-- Completar al ejecutar -->

## Pendientes / follow-ups

<!-- Completar al ejecutar -->
