# TR-012 – Administración de roles

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-012 – Administración de roles           |
| Épica              | 001 – Seguridad                            |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Administrador                              |
| Dependencias       | HU-001 (Login)                             |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-27                               |
| Estado             | Implementado                               |

**Origen:** [HU-012 – Administración de roles](../../03-historias-usuario/001-Seguridad/HU-012-administracion-roles.md)

---

## 1) HU Refinada

- **Título:** Administración de roles
- **Narrativa:** Como administrador quiero gestionar los roles del sistema para definir conjuntos de permisos reutilizables.
- **Contexto:** CRUD Pq_Rol. AccesoTotal=supervisor. Atributos en HU-014.
- **In scope:** Listado, crear, editar, eliminar (si no tiene permisos asignados).
- **Out of scope:** Atributos de rol (HU-014).

---

## 2) Criterios de Aceptación

- Listar roles: NombreRol, DescripcionRol, AccesoTotal.
- Crear/editar: NombreRol, DescripcionRol, AccesoTotal. AccesoTotal=true = supervisor.
- Eliminar solo si no tiene permisos asignados (o política de cascada).
- AccesoTotal no requiere permisos granulares en PQ_RolAtributo.

### Escenarios Gherkin

```gherkin
Feature: Administración de roles

  Scenario: Administrador lista roles
    Given el administrador está autenticado
    When accede a Administración de roles
    Then ve grilla con roles (NombreRol, DescripcionRol, AccesoTotal)

  Scenario: Administrador crea rol
    Given el administrador está en el listado de roles
    When hace clic en Crear rol
    And completa NombreRol, DescripcionRol, AccesoTotal
    And hace clic en Guardar
    Then el rol se crea en Pq_Rol

  Scenario: No eliminar rol con permisos asignados
    Given existe un rol con permisos en Pq_Permiso
    When el administrador intenta eliminar ese rol
    Then recibe error 422
    And mensaje indicando que tiene permisos asignados

  Scenario: Rol con AccesoTotal
    Given el administrador crea rol con AccesoTotal=true
    Then el rol tiene acceso a todo sin atributos en PQ_RolAtributo
```

---

## 3) Reglas de Negocio

- Solo administradores. Rol con AccesoTotal tiene acceso a todo.

---

## 4) Impacto en Datos

- Tabla Pq_Rol (ya existe).

---

## 5) Contratos de API

- GET /api/roles. GET /api/roles/{id}. POST /api/roles. PUT /api/roles/{id}. DELETE /api/roles/{id} (validar sin permisos).
- Validación: no eliminar si tiene Pq_Permiso o PQ_RolAtributo asignados.

---

## 6) Cambios Frontend

- Pantalla listado roles (grilla). Formulario crear/editar. Eliminar con validación.
- data-testid: roles.grid, roles.create, roles.edit, roles.delete

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD | Deps |
|----|------|-------------|-----|------|
| T1 | Backend | CRUD roles (controller, validaciones) | Endpoints operativos | - |
| T2 | Backend | Validación eliminar: sin permisos asignados | 422 si tiene permisos | - |
| T3 | Frontend | Pantalla listado roles | Grilla operativa | HU-001 |
| T4 | Frontend | Formulario crear/editar rol | CRUD completo | T3 |
| T5 | Tests | Integration: CRUD, no eliminar con permisos | Tests pasan | T1, T2 |
| T6 | Tests | E2E: crear rol, intentar eliminar con permisos | Playwright | T4 |

---

## Archivos creados/modificados

**Backend:** RolController (Admin), rutas /api/v1/admin/roles. DELETE validado: no eliminar si tiene pq_permiso.

**Frontend:** RolesAdminPage, rutas /admin/roles.

## Pendientes / follow-ups

- Formularios crear/editar. Tests.
