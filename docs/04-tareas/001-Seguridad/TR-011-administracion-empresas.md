# TR-011 – Administración de empresas

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-011 – Administración de empresas        |
| Épica              | 001 – Seguridad                            |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Administrador                              |
| Dependencias       | HU-001 (Login)                             |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-27                               |
| Estado             | Implementado                               |

**Origen:** [HU-011 – Administración de empresas](../../03-historias-usuario/001-Seguridad/HU-011-administracion-empresas.md)

---

## 1) HU Refinada

- **Título:** Administración de empresas
- **Narrativa:** Como administrador quiero gestionar las empresas registradas, vinculando cada una con la base de datos y el identificador de Tango.
- **Contexto:** CRUD PQ_Empresa. Validación contra tabla EMPRESA de Tango. NombreBD único.
- **In scope:** Listado, crear, editar, habilitar/inhabilitar. Validación Tango, unicidad NombreBD.
- **Out of scope:** Creación física de Company DB (manual o automática según política).

---

## 2) Criterios de Aceptación

- Listar empresas; filtrar por nombre, habilitada.
- Crear: NombreEmpresa, NombreBD, Habilita, imagen, theme. Validar contra EMPRESA (Tango).
- NombreBD único; no asignado previamente. Validación asignación única.
- Editar, habilitar/inhabilitar. Empresa inhabilitada no en selector de usuarios.
- Usar código/ID de Tango para evitar duplicados.

### Escenarios Gherkin

```gherkin
Feature: Administración de empresas

  Scenario: Administrador lista empresas
    Given el administrador está autenticado
    When accede a Administración de empresas
    Then ve grilla con empresas (nombre, nombreBD, habilitada, theme)
    And puede filtrar por nombre, habilitada

  Scenario: Administrador crea empresa
    Given el administrador está en el listado de empresas
    When hace clic en Crear empresa
    And completa NombreEmpresa, NombreBD, theme
    And hace clic en Guardar
    Then la empresa se crea en PQ_Empresa
    And NombreBD es único (error si duplicado)

  Scenario: NombreBD duplicado
    Given existe una empresa con NombreBD "empresa_001"
    When el administrador intenta crear empresa con mismo NombreBD
    Then recibe error 422
    And mensaje indicando NombreBD ya asignado

  Scenario: Empresa inhabilitada no en selector
    Given el administrador inhabilitó una empresa
    When un usuario con permiso en esa empresa accede al selector
    Then la empresa inhabilitada no aparece en la lista
```

---

## 3) Reglas de Negocio

- Solo administradores. NombreBD para conectar Company DB. Validar contra EMPRESA (Tango) lectura.

---

## 4) Impacto en Datos

- Tabla PQ_Empresa (ya existe). Lectura EMPRESA (Tango).

---

## 5) Contratos de API

- GET /api/empresas: listado con filtros. GET /api/empresas/{id}. POST /api/empresas. PUT /api/empresas/{id}.
- Validación: NombreBD único, no ya asignado. Opcional: validar existencia en EMPRESA (Tango).

---

## 6) Cambios Frontend

- Pantalla listado empresas (grilla, filtros). Formulario crear/editar. Campos NombreEmpresa, NombreBD, theme, etc.
- data-testid: empresas.grid, empresas.create, empresas.edit

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD | Deps |
|----|------|-------------|-----|------|
| T1 | Backend | CRUD empresas (controller, validaciones) | Endpoints operativos | - |
| T2 | Backend | Validación NombreBD único, contra EMPRESA Tango | Rechazar si duplicado | - |
| T3 | Frontend | Pantalla listado empresas, filtros | Grilla operativa | HU-001 |
| T4 | Frontend | Formulario crear/editar empresa | CRUD completo | T3 |
| T5 | Tests | Integration: CRUD, validación unicidad | Tests pasan | T1, T2 |
| T6 | Tests | E2E: crear empresa, validar error si NombreBD duplicado | Playwright | T4 |

---

## Archivos creados/modificados

**Backend:** EmpresaAdminController, rutas /api/v1/admin/empresas.

**Frontend:** EmpresasAdminPage, admin.service, rutas /admin/empresas.

## Notas y decisiones

- Validación nombreBd único. Sin validación contra EMPRESA Tango (opcional).

## Pendientes / follow-ups

- Formularios crear/editar. Validación Tango si se requiere.
