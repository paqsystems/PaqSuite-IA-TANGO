# TR-013 – Administración de permisos (asignaciones)

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-013 – Administración de permisos        |
| Épica              | 001 – Seguridad                            |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Administrador                              |
| Dependencias       | HU-010, HU-011, HU-012                     |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-27                               |
| Estado             | Implementado                               |

**Origen:** [HU-013 – Administración de permisos](../../03-historias-usuario/001-Seguridad/HU-013-administracion-permisos.md)

---

## 1) HU Refinada

- **Título:** Administración de permisos (asignaciones)
- **Narrativa:** Como administrador quiero asignar usuarios a empresas con roles específicos para controlar qué puede hacer cada usuario en cada empresa.
- **Contexto:** CRUD Pq_Permiso. Cada asignación: usuario + empresa + rol. PK (IDRol, IDEmpresa, IDUsuario).
- **In scope:** Listado con filtros, crear, editar (cambiar rol), eliminar. Validar existencia.
- **Out of scope:** Administración de usuarios/empresas/roles (HUs 010, 011, 012).

---

## 2) Criterios de Aceptación

- Listar asignaciones Pq_Permiso; filtrar por usuario, empresa, rol.
- Crear: seleccionar usuario, empresa, rol. Combinación única.
- Editar: cambiar rol. Eliminar: quitar acceso.
- Usuario sin permisos no puede acceder tras login. Validar usuario, empresa, rol existen.

### Escenarios Gherkin

```gherkin
Feature: Administración de permisos (asignaciones)

  Scenario: Administrador lista permisos con filtros
    Given el administrador está autenticado
    When accede a Administración de permisos
    Then ve grilla con asignaciones (usuario, empresa, rol)
    And puede filtrar por usuario, empresa, rol

  Scenario: Administrador asigna permiso usuario-empresa-rol
    Given el administrador está en el listado de permisos
    When hace clic en Crear permiso
    And selecciona usuario, empresa y rol
    And hace clic en Guardar
    Then se crea el registro en Pq_Permiso
    And la combinación es única (error si duplicado)

  Scenario: Combinación usuario-empresa-rol duplicada
    Given existe permiso usuario A + empresa X + rol R
    When el administrador intenta crear mismo usuario A + empresa X + rol R
    Then recibe error 422
    And mensaje indicando combinación ya existe

  Scenario: Usuario sin permisos no accede
    Given el usuario tiene credenciales válidas
    And no tiene registros en Pq_Permiso
    When hace login
    Then recibe error 403 (no tiene empresas asignadas)
```

---

## 3) Reglas de Negocio

- Solo administradores. Usuario puede tener múltiples permisos (uno por empresa).

---

## 4) Impacto en Datos

- Tabla Pq_Permiso (ya existe). PK compuesta IDRol, IDEmpresa, IDUsuario.

---

## 5) Contratos de API

- GET /api/permisos: listado con filtros (usuario, empresa, rol). POST /api/permisos. PUT /api/permisos/{id}. DELETE /api/permisos/{id}.
- Validación: combinación única; usuario, empresa, rol existentes.

---

## 6) Cambios Frontend

- Pantalla listado permisos (grilla, filtros). Formulario crear/editar con selects usuario, empresa, rol.
- data-testid: permisos.grid, permisos.create, permisos.edit, permisos.delete

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD | Deps |
|----|------|-------------|-----|------|
| T1 | Backend | CRUD permisos (controller, validaciones) | Endpoints operativos | - |
| T2 | Backend | Validación combinación única, existencia refs | 422 si duplicado/inválido | - |
| T3 | Frontend | Pantalla listado permisos, filtros | Grilla operativa | HU-001 |
| T4 | Frontend | Formulario crear/editar con selects | CRUD completo | T3, HU-010, HU-011, HU-012 |
| T5 | Tests | Integration: CRUD, validación unicidad | Tests pasan | T1, T2 |
| T6 | Tests | E2E: asignar permiso usuario-empresa-rol | Playwright | T4 |

---

## Archivos creados/modificados

**Backend:** PermisoController (Admin), rutas /api/v1/admin/permisos. Validación combinación única.

**Frontend:** PermisosAdminPage, rutas /admin/permisos.

## Pendientes / follow-ups

- Formularios crear/editar con selects usuario, empresa, rol.
