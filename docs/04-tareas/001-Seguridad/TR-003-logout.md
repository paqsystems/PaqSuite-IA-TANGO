# TR-003 – Cerrar sesión

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-003 – Cerrar sesión                     |
| Épica              | 001 – Seguridad                            |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Usuario autenticado                        |
| Dependencias       | HU-001 (Login)                             |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-27                               |
| Estado             | Implementado                               |

**Origen:** [HU-003 – Cerrar sesión](../../03-historias-usuario/001-Seguridad/HU-003-logout.md)

---

## 1) HU Refinada

- **Título:** Cerrar sesión
- **Narrativa:** Como usuario autenticado quiero cerrar sesión de forma segura para que mi token deje de ser válido.
- **Contexto:** Opción en menú usuario; invalidar token backend; limpiar frontend; redirigir a login.
- **In scope:** Logout desde menú, invalidación token, limpieza contexto, redirección.
- **Out of scope:** Timeout automático de sesión.

---

## 2) Criterios de Aceptación

- Opción Cerrar sesión en menú usuario (debajo del avatar).
- Al seleccionar: invalidar token en backend; eliminar token y contexto empresa en frontend; redirigir a login.
- Peticiones con token anterior reciben 401. Sin confirmación (acción inmediata).

### Escenarios Gherkin

```gherkin
Feature: Cerrar sesión

  Scenario: Usuario cierra sesión desde menú
    Given el usuario está autenticado
    When hace clic en "Cerrar sesión" en el menú usuario
    Then el token se invalida en backend
    And el token y contexto empresa se eliminan del frontend
    And es redirigido a la pantalla de login

  Scenario: Token anterior inválido tras logout
    Given el usuario cerró sesión
    When intenta una petición con el token anterior
    Then recibe 401 (no autenticado)
```

---

## 3) Reglas de Negocio

- Logout explícito (usuario lo solicita).

---

## 4) Impacto en Datos

- Sin cambios. Opcional: limpiar users.token si se persiste.

---

## 5) Contratos de API

- POST /api/logout: invalidar token. Response 204. Errores: 401.

---

## 6) Cambios Frontend

- Ítem Cerrar sesión en UserMenu; handler que llama API, limpia storage, redirige a /login.
- data-testid: userMenu.logout

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD | Deps |
|----|------|-------------|-----|------|
| T1 | Backend | Endpoint POST /api/logout | Token invalidado | - |
| T2 | Frontend | Ítem Cerrar sesión en UserMenu | Handler logout, redirección | HU-001 |
| T3 | Tests | E2E: logout, verificar redirección | Playwright | T2 |

---

## Archivos creados/modificados

**Backend:** Endpoint POST /api/v1/auth/logout ya existía (AuthController).

**Frontend:**
- `frontend/src/features/auth/services/auth.service.ts` – logout con apiFetch
- `frontend/src/app/AppLayout.tsx` – botón Cerrar sesión con data-testid="userMenu.logout"

## Notas y decisiones

- Logout usa apiFetch con skipCompanyId (no requiere empresa).
- data-testid userMenu.logout según TR.

## Pendientes / follow-ups

- E2E: logout, verificar redirección a /login.
