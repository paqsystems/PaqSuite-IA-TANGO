# TR-002 – Selección de empresa activa

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-002 – Selección de empresa activa       |
| Épica              | 001 – Seguridad                            |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Usuario con varias empresas                |
| Dependencias       | HU-001 (Login)                             |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-27                               |
| Estado             | Implementado                               |

**Origen:** [HU-002 – Selección de empresa activa](../../03-historias-usuario/001-Seguridad/HU-002-seleccion-empresa.md)

---

## 1) HU Refinada

- **Título:** Selección de empresa activa
- **Narrativa:** Como usuario asignado a varias empresas quiero seleccionar la empresa activa para operar en su contexto.
- **Contexto:** Lista de empresas según Pq_Permiso. Header X-Company-Id en requests. Theme por empresa.
- **In scope:** Selector de empresas, contexto empresa activa, header X-Company-Id, validación backend.
- **Out of scope:** Cambio de empresa sin recargar.

---

## 2) Criterios de Aceptación

- Lista de empresas según Pq_Permiso (nombre, opcional imagen/theme).
- Al seleccionar: contexto empresa activa, header X-Company-Id en peticiones.
- Menú recargado según permisos en esa empresa; theme aplicado.
- Empresa activa visible en TopBar; cambio desde menú usuario.
- 403 si X-Company-Id no autorizado; persistencia en sesión/localStorage.

### Escenarios Gherkin

```gherkin
Feature: Selección de empresa activa

  Scenario: Usuario selecciona empresa desde CompanySwitcher
    Given el usuario está autenticado
    And tiene permiso en Empresa A y Empresa B
    When abre el CompanySwitcher
    And selecciona Empresa B
    Then la empresa activa pasa a ser Empresa B
    And las peticiones incluyen X-Company-Id de Empresa B
    And el menú se recarga según permisos en esa empresa
    And el theme de la empresa se aplica

  Scenario: Backend rechaza X-Company-Id no autorizado
    Given el usuario está autenticado
    When envía petición con X-Company-Id de empresa sin permiso
    Then el backend responde 403
    And el mensaje indica empresa no autorizada

  Scenario: Empresa activa persistida
    Given el usuario seleccionó Empresa A
    When recarga la página o abre nueva pestaña
    Then la empresa activa sigue siendo Empresa A
    And no requiere volver a seleccionar
```

---

## 3) Reglas de Negocio

- Solo empresas con permiso en Pq_Permiso. Validación obligatoria de X-Company-Id.

---

## 4) Impacto en Datos

- Tablas PQ_Empresa, Pq_Permiso (ya existen).

---

## 5) Contratos de API

- GET /api/empresas/permisos-usuario: lista de empresas del usuario.
- Middleware: validar X-Company-Id contra Pq_Permiso; 403 si no autorizado.

---

## 6) Cambios Frontend

- CompanySwitcher en TopBar/menú usuario; estado global empresa activa; envío X-Company-Id.
- data-testid: companySwitcher, companySwitcher.option.{id}

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD | Deps |
|----|------|-------------|-----|------|
| T1 | Backend | Endpoint empresas del usuario | Lista según Pq_Permiso | - |
| T2 | Backend | Middleware validación X-Company-Id | 403 si no autorizado | - |
| T3 | Frontend | CompanySwitcher (lista, selección) | UI en menú usuario | HU-001 |
| T4 | Frontend | Estado empresa activa, header X-Company-Id | Envío en requests | T3 |
| T5 | Tests | Integration: validación X-Company-Id | Tests pasan | T2 |
| T6 | Tests | E2E: cambiar empresa | Playwright | T4 |

---

## Archivos creados/modificados

**Backend:**
- `backend/app/Http/Middleware/ValidateCompanyId.php` – valida X-Company-Id contra pq_permiso
- `backend/app/Http/Controllers/Api/V1/EmpresaController.php` – GET /api/v1/empresas
- `backend/app/Services/AuthService.php` – getEmpresasDelUsuario público
- `backend/app/Http/Kernel.php` – alias middleware `company`
- `backend/routes/api.php` – ruta /empresas, middleware company en grupo auth

**Frontend:**
- `frontend/src/shared/api/apiClient.ts` – apiFetch con Authorization + X-Company-Id
- `frontend/src/features/company/components/CompanySwitcher.tsx` – selector dropdown
- `frontend/src/features/company/components/CompanySwitcher.css`
- `frontend/src/app/AppLayout.tsx` – CompanySwitcher en header
- `frontend/src/features/user/services/user.service.ts` – usa apiFetch
- `frontend/src/features/auth/services/auth.service.ts` – logout usa apiFetch

## Comandos ejecutados

```bash
npm run build  # frontend
```

## Notas y decisiones

- Middleware `company` valida X-Company-Id solo cuando está presente; si no viene, pasa.
- CompanySwitcher: si 1 empresa, muestra nombre estático; si varias, dropdown con recarga al cambiar.
- apiFetch añade X-Company-Id desde getEmpresaActiva(); skipCompanyId para logout.

## Pendientes / follow-ups

- Tests integration: validación X-Company-Id (403 si no autorizado).
- E2E: cambiar empresa con Playwright.
