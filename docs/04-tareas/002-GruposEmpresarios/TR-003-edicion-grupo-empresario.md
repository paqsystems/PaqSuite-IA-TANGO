# TR-003 – Edición de grupo empresario

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-003 – Edición de grupo empresario       |
| Épica              | 002 – Grupos empresarios                   |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Administrador                              |
| Dependencias       | HU-001, HU-002 (Listado, Creación), HU-011 |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-27                               |
| Estado             | Implementado                                |

**Origen:** [HU-003 – Edición de grupo empresario](../../03-historias-usuario/002-GruposEmpresarios/HU-003-edicion-grupo-empresario.md)

---

## 1) HU Refinada

- **Título:** Edición de grupo empresario
- **Narrativa:** Como administrador quiero editar un grupo empresario existente para modificar su descripción o las empresas que lo componen.
- **Contexto:** Formulario similar a creación. Agregar/quitar empresas. Mínimo una empresa siempre.
- **In scope:** Editar descripción, agregar empresas, quitar empresas, validación mínimo 1.
- **Out of scope:** Creación, eliminación.

---

## 2) Criterios de Aceptación

- Editar descripción. Agregar empresas desde disponibles. Quitar empresas del grupo.
- Grupo debe conservar al menos una empresa. Error si se intenta quitar la última.
- Sin empresas duplicadas. Tras guardar: update PQ_GrupoEmpresario, sync PQ_GrupoEmpresario_Empresas.
- Mensaje éxito, actualizar vista (listado o detalle).

### Escenarios Gherkin

```gherkin
Feature: Edición de grupo empresario

  Scenario: Administrador edita grupo - agrega y quita empresas
    Given existe un grupo con al menos 2 empresas
    When el administrador accede a editar ese grupo
    And modifica la descripción
    And agrega una empresa nueva
    And quita una empresa (queda al menos 1)
    And hace clic en Guardar
    Then se actualiza PQ_GrupoEmpresario
    And se sincroniza PQ_GrupoEmpresario_Empresas
    And ve mensaje de éxito

  Scenario: Error - intentar quitar la última empresa
    Given existe un grupo con una sola empresa
    When el administrador edita y quita esa empresa
    Then no puede guardar (o recibe error 422)
    And mensaje indicando que debe conservar al menos una empresa
```

---

## 3) Reglas de Negocio

- Grupo con al menos una empresa siempre. Solo administradores.

---

## 4) Impacto en Datos

- PQ_GrupoEmpresario (update descripcion). PQ_GrupoEmpresario_Empresas (delete + insert para sync).

---

## 5) Contratos de API

- GET /api/grupos-empresarios/{id}: detalle con empresas asignadas.
- PUT /api/grupos-empresarios/{id}: { descripcion, empresaIds }. Validar al menos 1 empresa.
- Lógica: eliminar asociaciones actuales, insertar nuevas (o diff).

---

## 6) Cambios Frontend

- Formulario edición (reutilizar estructura de creación). Cargar datos existentes. Selector empresas con valores actuales.
- Validación: no permitir guardar si queda sin empresas.
- data-testid: grupoEmpresario.edit.form, grupoEmpresario.edit.submit

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD | Deps |
|----|------|-------------|-----|------|
| T1 | Backend | GET grupos-empresarios/{id} con empresas | Detalle para edición | - |
| T2 | Backend | PUT grupos-empresarios/{id} | Update descripcion, sync empresas | - |
| T3 | Backend | Validación: al menos 1 empresa al editar | 422 si quitar última | T2 |
| T4 | Frontend | Formulario edición (cargar datos) | Editar descripción y empresas | HU-001, HU-002 |
| T5 | Tests | Integration: editar, quitar última (error) | Tests pasan | T2, T3 |
| T6 | Tests | E2E: editar grupo, agregar y quitar empresas | Playwright | T4 |

---

## Archivos creados/modificados

### Backend
- `backend/app/Http/Controllers/Api/V1/Admin/GrupoEmpresarioController.php` (métodos show, update)
- `backend/routes/api.php` (rutas GET/PUT grupos-empresarios/{id})

### Frontend
- `frontend/src/features/admin/pages/GrupoEmpresarioEditarPage.tsx` (creado)
- `frontend/src/features/admin/services/admin.service.ts` (get, update en adminGruposEmpresariosApi)
- `frontend/src/features/admin/pages/GruposEmpresariosAdminPage.tsx` (columna Acciones: Ver, Editar)
- `frontend/src/app/App.tsx` (ruta /admin/grupos-empresarios/:id/editar)

## Comandos ejecutados

```bash
cd frontend && npm run build
```

## Notas y decisiones

- Formulario edición reutiliza estructura de creación (descripción + TagBox empresas).
- Sync de empresas: delete + insert en pq_grupo_empresario_empresas.

## Pendientes / follow-ups

- Ninguno
