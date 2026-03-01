# TR-004 – Cambio de contraseña

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-004 – Cambio de contraseña              |
| Épica              | 001 – Seguridad                            |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Usuario autenticado                        |
| Dependencias       | HU-001 (Login)                             |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-27                               |
| Estado             | Implementado                               |

**Origen:** [HU-004 – Cambio de contraseña](../../03-historias-usuario/001-Seguridad/HU-004-cambio-contraseña.md)

---

## 1) HU Refinada

- **Título:** Cambio de contraseña
- **Narrativa:** Como usuario autenticado quiero cambiar mi contraseña para mantener la seguridad de mi cuenta.
- **Contexto:** Modal desde menú usuario. Si first_login=true, obligatorio antes de acceder al resto.
- **In scope:** Modal con formulario, validaciones, actualización password_hash y first_login.
- **Out of scope:** Recuperación por email (HU-005).

---

## 2) Criterios de Aceptación

- Ítem Cambiar contraseña en menú usuario; abre Popup con: contraseña actual, nueva, confirmación.
- Validar contraseña actual correcta; nueva cumple políticas; nueva y confirmación coinciden.
- Si first_login=true, obligatorio cambiar antes de acceder.
- Tras éxito: actualizar password_hash, first_login=false; mensaje éxito, cerrar popup.
- Mensaje claro si contraseña actual incorrecta.

### Escenarios Gherkin

```gherkin
Feature: Cambio de contraseña

  Scenario: Usuario cambia contraseña correctamente
    Given el usuario está autenticado
    When accede a cambiar contraseña (perfil o menú)
    And ingresa contraseña actual correcta
    And ingresa nueva contraseña que cumple políticas
    And confirma la nueva contraseña
    And hace clic en Guardar
    Then la contraseña se actualiza
    And first_login pasa a false si estaba true
    And ve mensaje de éxito

  Scenario: Contraseña actual incorrecta
    Given el usuario está autenticado
    When ingresa contraseña actual incorrecta
    And hace clic en Guardar
    Then ve mensaje indicando contraseña actual incorrecta
    And la contraseña no se modifica

  Scenario: Obligatorio cambiar si first_login
    Given el usuario está autenticado
    And first_login es true
    When intenta acceder al layout principal
    Then es redirigido a cambiar contraseña
    And no puede acceder hasta cambiar
```

---

## 3) Reglas de Negocio

- Solo el usuario autenticado cambia su propia contraseña. Almacenar hasheada (bcrypt).

---

## 4) Impacto en Datos

- Tabla users: campos password_hash, first_login (ya existen).

---

## 5) Contratos de API

- PUT /api/users/me/password: Request currentPassword, newPassword, newPasswordConfirmation. Response 200. Errores 400, 401, 422.

---

## 6) Cambios Frontend

- Modal ChangePassword (formulario, validaciones). Ítem en UserMenu.
- data-testid: changePassword.modal, changePassword.submit

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD | Deps |
|----|------|-------------|-----|------|
| T1 | Backend | Endpoint PUT /api/users/me/password | Validaciones, actualización | - |
| T2 | Backend | Lógica first_login: redirigir a cambio si true | Middleware o guard | - |
| T3 | Frontend | Modal ChangePassword, integración en UserMenu | UI operativa | HU-001 |
| T4 | Tests | Integration: cambio correcto, contraseña incorrecta | Tests pasan | T1 |
| T5 | Tests | E2E: cambiar contraseña desde menú | Playwright | T3 |

---

## Archivos creados/modificados

**Backend:** POST /api/v1/auth/change-password (AuthController), ChangePasswordTest.

**Frontend:** ProfileView con sección de cambio de contraseña (profile.changePassword.form, profile.changePasswordSubmit, etc.).

## Notas y decisiones

- API usa POST /auth/change-password (no PUT /users/me/password).
- Cambio de contraseña integrado en ProfileView, no en UserMenu como modal separado.

## Pendientes / follow-ups

- Ítem "Cambiar contraseña" en UserMenu (si se requiere acceso rápido sin ir a perfil).
- first_login: redirigir a cambio si true (no implementado).
