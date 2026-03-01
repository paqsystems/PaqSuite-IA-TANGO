# TR-010 – Administración de usuarios

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-010 – Administración de usuarios        |
| Épica              | 001 – Seguridad                            |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Administrador                              |
| Dependencias       | HU-001 (Login)                             |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-27                               |
| Estado             | Implementado                               |

**Origen:** [HU-010 – Administración de usuarios](../../03-historias-usuario/001-Seguridad/HU-010-administracion-usuarios.md)

---

## 1) HU Refinada

- **Título:** Administración de usuarios
- **Narrativa:** Como administrador quiero gestionar los usuarios del sistema (alta, edición, baja) para controlar quién puede acceder.
- **Contexto:** CRUD usuarios tabla users. Solo administradores. Soft delete (inhabilitado).
- **In scope:** Listado con filtros, crear, editar, inhabilitar. Permisos en HU-013.
- **Out of scope:** Gestión de permisos (HU-013).

---

## 2) Criterios de Aceptación

- Listar usuarios; filtrar por código, nombre, email, activo, inhabilitado.
- Crear: código, nombre, email, contraseña inicial, supervisor, activo, inhabilitado. Código y email únicos.
- Editar (excepto código si es clave). Inhabilitar (soft) sin eliminar.
- Usuario inhabilitado no puede acceder. Validaciones obligatorias y formatos.

### Escenarios Gherkin

```gherkin
Feature: Administración de usuarios

  Scenario: Administrador lista usuarios
    Given el administrador está autenticado
    When accede a Administración de usuarios
    Then ve grilla con usuarios (código, nombre, email, activo, inhabilitado)
    And puede filtrar por código, nombre, email, activo, inhabilitado

  Scenario: Administrador crea usuario
    Given el administrador está en la pantalla de usuarios
    When hace clic en Crear usuario
    And completa código, nombre, email, contraseña inicial
    And hace clic en Guardar
    Then el usuario se crea en la tabla users
    And código y email son únicos (error si duplicado)

  Scenario: Administrador inhabilita usuario
    Given el administrador está en la pantalla de usuarios
    And existe un usuario activo
    When selecciona Inhabilitar para ese usuario
    And confirma la acción
    Then el usuario queda con inhabilitado=true
    And el usuario no puede acceder al sistema

  Scenario: No administrador no accede
    Given el usuario está autenticado
    And no tiene rol de administrador
    When intenta acceder a /admin/usuarios
    Then recibe 403
```

---

## 3) Reglas de Negocio

- Solo administradores. No eliminación física; inhabilitado=true.

---

## 4) Impacto en Datos

- Tabla users (ya existe).

---

## 5) Contratos de API

- GET /api/users: listado con filtros. GET /api/users/{id}. POST /api/users. PUT /api/users/{id}. DELETE o PUT para inhabilitar.
- Validación: código único, email único, longitud mínima contraseña.

---

## 6) Cambios Frontend

- Pantalla listado usuarios (grilla, filtros). Formulario crear/editar. Acción inhabilitar.
- data-testid: users.grid, users.create, users.edit, users.inhabilitar

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD | Deps |
|----|------|-------------|-----|------|
| T1 | Backend | CRUD usuarios (controller, validaciones) | Endpoints operativos | - |
| T2 | Backend | Política autorización solo admin | 403 si no admin | - |
| T3 | Frontend | Pantalla listado usuarios, filtros | Grilla operativa | HU-001 |
| T4 | Frontend | Formulario crear/editar usuario | CRUD completo | T3 |
| T5 | Tests | Integration: CRUD, validaciones | Tests pasan | T1 |
| T6 | Tests | E2E: crear usuario, inhabilitar | Playwright | T4 |

---

## Archivos creados/modificados

**Backend:** AdminAuthService, RequireAdmin middleware, UserController (Admin), rutas /api/v1/admin/users.

**Frontend:** admin.service, UsersAdminPage, rutas /admin/usuarios, Sidebar con enlace para admin.

## Notas y decisiones

- Admin = usuario con rol acceso_total en pq_rol (vía pq_permiso).
- es_admin en login response; Sidebar muestra Administración si esAdmin.
- Inhabilitar vía PUT /users/{id}/inhabilitar (soft delete).

## Pendientes / follow-ups

- Formularios crear/editar usuario (modales).
- Tests integration y E2E.
