# TR-005 – Recuperación de contraseña

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-005 – Recuperación de contraseña        |
| Épica              | 001 – Seguridad                            |
| Prioridad          | SHOULD-HAVE                                |
| Roles              | Usuario no autenticado                     |
| Dependencias       | -                                          |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-27                               |
| Estado             | Implementado                               |

**Origen:** [HU-005 – Recuperación de contraseña](../../03-historias-usuario/001-Seguridad/HU-005-recuperacion-contraseña.md)

---

## 1) HU Refinada

- **Título:** Recuperación de contraseña
- **Narrativa:** Como usuario que olvidé mi contraseña quiero solicitar un enlace de restablecimiento por email.
- **Contexto:** Enlace en login; formulario email; token temporal; email con enlace; formulario nueva contraseña.
- **In scope:** Forgot password, reset password, token con validez limitada.
- **Out of scope:** Cambio de contraseña autenticado (HU-004).

---

## 2) Criterios de Aceptación

- Enlace "¿Olvidaste tu contraseña?" en login; formulario para email.
- Si email existe: token generado, email enviado (o simulado en dev). Mensaje genérico.
- Token validez limitada (ej. 60 min). Enlace abre formulario: nueva contraseña, confirmación.
- Tras enviar: actualizar password_hash, invalidar token; redirigir a login con éxito.
- Error si token expirado o inválido.

### Escenarios Gherkin

```gherkin
Feature: Recuperación de contraseña

  Scenario: Usuario solicita recuperación (forgot password)
    Given el usuario está en la pantalla de login
    When hace clic en "¿Olvidaste tu contraseña?"
    And ingresa su email o código
    And hace clic en Enviar
    Then ve mensaje genérico (siempre igual, no revela si email existe)
    And si el email existe, recibe token por correo

  Scenario: Usuario restablece contraseña con token válido
    Given el usuario recibió enlace de recuperación
    And el token no ha expirado
    When accede al enlace con token
    And ingresa nueva contraseña y confirmación
    And hace clic en Restablecer
    Then la contraseña se actualiza
    And el token se invalida
    And es redirigido a login con mensaje de éxito

  Scenario: Token expirado o inválido
    Given el usuario accede al enlace de recuperación
    And el token está expirado o es inválido
    When intenta restablecer contraseña
    Then ve error 422
    And mensaje indicando token inválido o expirado
```

---

## 3) Reglas de Negocio

- No revelar si el email existe. Requiere MAIL_* en .env.

---

## 4) Impacto en Datos

- Tabla `password_reset_tokens` (Laravel) o equivalente para tokens temporales.

---

## 5) Contratos de API

### POST /api/forgot-password

**Request:** `{ "email": "string" }`

**Response (200):** `{ "message": "Si el email existe, recibirás instrucciones" }` (siempre mismo mensaje).

### POST /api/reset-password

**Request:** `{ "token": "string", "email": "string", "password": "string", "password_confirmation": "string" }`

**Response (200):** `{ "message": "Contraseña actualizada" }`

**Errores:** 422 (token inválido/expirado).

---

## 6) Cambios Frontend

- Pantalla/popup ForgotPassword (email). Pantalla ResetPassword (token en URL, nueva contraseña).
- data-testid: `forgotPassword.form`, `resetPassword.form`.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD | Deps |
|----|------|-------------|-----|------|
| T1 | Backend | ForgotPasswordController, envío email | Token generado, email (o log en dev) | - |
| T2 | Backend | ResetPasswordController | Validar token, actualizar password | T1 |
| T3 | Frontend | Pantalla ForgotPassword, enlace en login | UI operativa | - |
| T4 | Frontend | Pantalla ResetPassword (ruta con token) | UI operativa | - |
| T5 | Tests | Integration: forgot (no revelar existencia), reset | Tests pasan | T1, T2 |
| T6 | Tests | E2E: flujo completo (con mail simulado) | Playwright | T3, T4 |

---

## Archivos creados/modificados

**Backend:** POST /api/v1/auth/forgot-password, POST /api/v1/auth/reset-password (AuthController), PasswordResetService, Mail, PasswordResetTest.

**Frontend:** ForgotPasswordPage, ResetPasswordPage, enlace en LoginForm. data-testid: forgotPassword.form, resetPassword.form.

## Notas y decisiones

- API usa code_or_email en forgot-password.
- Token en URL para reset-password.

## Pendientes / follow-ups

- E2E flujo completo con mail simulado.
