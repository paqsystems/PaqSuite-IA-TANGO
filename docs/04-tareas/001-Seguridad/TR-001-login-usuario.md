# TR-001 – Login de usuario

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-001 – Login de usuario                  |
| Épica              | 001 – Seguridad                            |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Usuario del sistema                        |
| Dependencias       | -                                          |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-27                               |
| Estado             | Implementado                               |

**Origen:** [HU-001 – Login de usuario](../../03-historias-usuario/001-Seguridad/HU-001-login-usuario.md)

---

## 1) HU Refinada

- **Título:** Login de usuario
- **Narrativa:** Como usuario del sistema quiero autenticarme con mi código y contraseña para acceder a las funcionalidades según mis permisos.
- **Contexto:** Autenticación contra tabla `users` (Dictionary DB). Token (Sanctum). Redirección según cantidad de empresas asignadas.
- **In scope:** Formulario login, validaciones, token, redirección (una empresa / varias / ninguna).
- **Out of scope:** Login social (users_identities), recuperación de contraseña (HU-005).

---

## 2) Criterios de Aceptación

- Formulario con código y contraseña; validación no vacío antes de enviar.
- Validar usuario existe, activo, no inhabilitado, contraseña correcta.
- Token generado (Sanctum); almacenado en frontend (localStorage/sessionStorage).
- Redirección: 1 empresa → layout principal; varias → selector empresa; ninguna → mensaje.
- Mensaje de error genérico si credenciales inválidas (sin revelar si usuario existe).

### Escenarios Gherkin

```gherkin
Feature: Login de usuario

  Scenario: Login exitoso con una empresa
    Given el usuario tiene credenciales válidas
    And está asignado a una sola empresa en Pq_Permiso
    When ingresa código y contraseña correctos
    And hace clic en "Iniciar sesión"
    Then recibe un token
    And es redirigido al layout principal
    And la empresa activa queda establecida

  Scenario: Login exitoso con varias empresas
    Given el usuario tiene credenciales válidas
    And está asignado a varias empresas en Pq_Permiso
    When ingresa código y contraseña correctos
    And hace clic en "Iniciar sesión"
    Then recibe un token
    And es redirigido al selector de empresa

  Scenario: Login fallido - credenciales inválidas
    Given el usuario está en la pantalla de login
    When ingresa código o contraseña incorrectos
    And hace clic en "Iniciar sesión"
    Then ve mensaje de error genérico
    And no se revela si el usuario existe o no
    And no recibe token

  Scenario: Usuario sin empresas asignadas
    Given el usuario existe y está activo
    And no tiene permisos en Pq_Permiso (ninguna empresa)
    When ingresa credenciales correctas
    Then recibe error 403
    And mensaje indicando que no tiene empresas asignadas
```

---

## 3) Reglas de Negocio

- Solo `activo=true` e `inhabilitado=false` pueden acceder.
- Usuario debe tener al menos un permiso en `Pq_Permiso`.

---

## 4) Impacto en Datos

- Tabla `users` (ya existe). Sin migraciones nuevas.

---

## 5) Contratos de API

### POST /api/login

**Request:** `{ "codigo": "string", "password": "string" }`

**Response (200):** `{ "token": "...", "user": { "id", "codigo", "name_user", "email", ... }, "empresas": [...], "redirectTo": "layout"|"selector" }`

**Errores:** 401 (credenciales inválidas), 403 (usuario inhabilitado o sin empresas).

---

## 6) Cambios Frontend

- Pantalla Login (formulario, validaciones, manejo de errores).
- Almacenamiento de token; redirección según respuesta.
- data-testid: `login.form`, `login.codigo`, `login.password`, `login.submit`.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD | Deps |
|----|------|-------------|-----|------|
| T1 | Backend | Endpoint POST /api/login (Sanctum) | Token generado, validaciones | - |
| T2 | Backend | Validación usuario activo, inhabilitado, permisos | 401/403 según caso | T1 |
| T3 | Frontend | Pantalla Login (formulario, validaciones) | Formulario operativo | - |
| T4 | Frontend | Integración con API, almacenamiento token, redirección | Flujo completo | T1, T3 |
| T5 | Tests | Integration: login válido, inválido, sin empresas | Tests pasan | T1 |
| T6 | Tests | E2E: login exitoso, redirección | Playwright | T4 |

---

## Archivos creados/modificados

**Backend:**
- `backend/app/Services/AuthService.php` – empresas, redirectTo, ERROR_NO_EMPRESAS
- `backend/app/Http/Controllers/Api/V1/AuthController.php` – respuesta ampliada, 403 sin empresas
- `backend/tests/Unit/Services/AuthServiceTest.php` – seed empresas, test sin empresas

**Frontend:**
- `frontend/src/shared/utils/tokenStorage.ts` – empresas, empresaActiva
- `frontend/src/features/auth/services/auth.service.ts` – LoginResult con redirectTo, empresas
- `frontend/src/features/auth/components/LoginForm.tsx` – redirección según redirectTo
- `frontend/src/features/auth/components/EmpresaSelectorPage.tsx` – selector de empresa
- `frontend/src/config/sessionContext.ts` – getEmpresa, setEmpresa
- `frontend/src/app/AppLayout.tsx` – redirección a /select-empresa
- `frontend/src/app/App.tsx` – ruta /select-empresa
- `frontend/tests/e2e/auth-login.spec.ts` – data-testid actualizados

## Comandos ejecutados

```bash
php artisan test tests/Unit/Services/AuthServiceTest.php
# Requiere MySQL activo; falla si no hay conexión
```

## Notas y decisiones

- API usa `usuario`/`password` en request; frontend mapea `codigo`/`password`.
- `redirectTo`: `layout` (1 empresa), `selector` (varias), 403 si ninguna.
- Usuario SINEMPR en tests para validar ERROR_NO_EMPRESAS.

## Pendientes / follow-ups

- Ejecutar tests con MySQL activo (Docker o local).
- Ejecutar E2E Playwright cuando backend esté disponible.
