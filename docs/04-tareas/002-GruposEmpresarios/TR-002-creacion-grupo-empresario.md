# TR-002 – Creación de grupo empresario

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-002 – Creación de grupo empresario      |
| Épica              | 002 – Grupos empresarios                   |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Administrador                              |
| Dependencias       | HU-001 (Listado), HU-011 (Empresas)        |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-27                               |
| Estado             | Implementado                                |

**Origen:** [HU-002 – Creación de grupo empresario](../../03-historias-usuario/002-GruposEmpresarios/HU-002-creacion-grupo-empresario.md)

---

## 1) HU Refinada

- **Título:** Creación de grupo empresario
- **Narrativa:** Como administrador quiero crear un nuevo grupo empresario asignándole empresas para usarlo en reportes consolidados y procesos multi-empresa.
- **Contexto:** Formulario con descripción y selector múltiple de empresas. Al menos una empresa obligatoria.
- **In scope:** Crear grupo, asignar empresas (mínimo 1), validaciones, insert en ambas tablas.
- **Out of scope:** Edición, eliminación.

---

## 2) Criterios de Aceptación

- Formulario: descripción (obligatoria), selector/listado empresas disponibles (PQ_Empresa habilitadas).
- Selección múltiple de empresas. Al menos una obligatoria. Sin duplicados.
- Tras crear: insert PQ_GrupoEmpresario y PQ_GrupoEmpresario_Empresas.
- Redirigir a listado o detalle con mensaje éxito. Validaciones: descripción vacía, sin empresas.

### Escenarios Gherkin

```gherkin
Feature: Creación de grupo empresario

  Scenario: Administrador crea grupo con empresas
    Given el administrador está en el listado de grupos
    When hace clic en Crear
    And ingresa descripción "Grupo Norte"
    And selecciona al menos una empresa del selector
    And hace clic en Guardar
    Then se crea el grupo en PQ_GrupoEmpresario
    And se insertan asociaciones en PQ_GrupoEmpresario_Empresas
    And es redirigido a listado o detalle con mensaje éxito

  Scenario: Error - sin empresas seleccionadas
    Given el administrador está en el formulario de creación
    When ingresa descripción pero no selecciona empresas
    And hace clic en Guardar
    Then ve error de validación
    And mensaje indicando al menos una empresa obligatoria

  Scenario: Error - descripción vacía
    Given el administrador está en el formulario de creación
    When deja descripción vacía
    And selecciona empresas
    Then ve error de validación al guardar
```

---

## 3) Reglas de Negocio

- Grupo debe tener al menos una empresa. Solo empresas existentes y habilitadas. Descripción puede repetirse.

---

## 4) Impacto en Datos

- PQ_GrupoEmpresario, PQ_GrupoEmpresario_Empresas. PQ_Empresa para listar disponibles.

---

## 5) Contratos de API

- GET /api/empresas: listar habilitadas (para selector). Puede reutilizar endpoint de HU-011.
- POST /api/grupos-empresarios: { descripcion, empresaIds: number[] }. Validar al menos 1 empresa, sin duplicados.
- Response 201 con grupo creado.

---

## 6) Cambios Frontend

- Formulario crear grupo: campo descripción, selector múltiple empresas (TagBox o CheckBoxList).
- Validaciones en UI. Redirección tras éxito.
- data-testid: grupoEmpresario.create.form, grupoEmpresario.create.descripcion, grupoEmpresario.create.empresas, grupoEmpresario.create.submit

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD | Deps |
|----|------|-------------|-----|------|
| T1 | Backend | POST grupos-empresarios con validaciones | Insert grupo + asociaciones | HU-011 |
| T2 | Backend | Validación: al menos 1 empresa, sin duplicados | 422 si inválido | T1 |
| T3 | Frontend | Formulario crear grupo | Descripción, selector empresas | HU-001 Listado |
| T4 | Frontend | Integración API, validaciones, redirección | Flujo completo | T3, T1 |
| T5 | Tests | Integration: crear con/sin empresas, duplicados | Tests pasan | T1, T2 |
| T6 | Tests | E2E: crear grupo con empresas | Playwright | T4 |

---

## Archivos creados/modificados

### Backend
- `backend/app/Http/Controllers/Api/V1/Admin/GrupoEmpresarioController.php` (método store)
- `backend/routes/api.php` (ruta POST grupos-empresarios)

### Frontend
- `frontend/src/features/admin/pages/GrupoEmpresarioCrearPage.tsx` (formulario completo: descripción + TagBox empresas)
- `frontend/src/features/admin/services/admin.service.ts` (create en adminGruposEmpresariosApi)

## Comandos ejecutados

```bash
cd backend && php artisan migrate
cd frontend && npm run build
```

## Notas y decisiones

- TagBox de DevExtreme para selector múltiple de empresas.
- Reutiliza `adminEmpresasApi.list({ habilita: '1' })` para empresas habilitadas.

## Pendientes / follow-ups

- Ninguno
