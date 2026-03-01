# TR-005 – Detalle de grupo empresario

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-005 – Detalle de grupo empresario       |
| Épica              | 002 – Grupos empresarios                   |
| Prioridad          | SHOULD-HAVE                                |
| Roles              | Administrador                              |
| Dependencias       | HU-001 (Listado)                           |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-27                               |
| Estado             | Implementado                                |

**Origen:** [HU-005 – Detalle de grupo empresario](../../03-historias-usuario/002-GruposEmpresarios/HU-005-detalle-grupo-empresario.md)

---

## 1) HU Refinada

- **Título:** Detalle de grupo empresario
- **Narrativa:** Como administrador quiero ver el detalle de un grupo empresario para conocer su descripción y empresas sin entrar en modo edición.
- **Contexto:** Vista solo lectura. Acceso desde listado (clic fila o botón Ver). Botones editar/eliminar.
- **In scope:** Pantalla detalle con descripción, listado empresas (solo lectura), acciones editar/eliminar.
- **Out of scope:** Edición inline.

---

## 2) Criterios de Aceptación

- Acceso desde listado (clic en fila o botón Ver).
- Mostrar descripción del grupo. Listado empresas asignadas (nombre, código o id) solo lectura.
- Botones o enlaces para editar o eliminar.
- Si grupo no existe o fue eliminado: mensaje apropiado, redirigir a listado.

### Escenarios Gherkin

```gherkin
Feature: Detalle de grupo empresario

  Scenario: Administrador accede al detalle desde listado
    Given el administrador está en el listado de grupos
    When hace clic en una fila o en botón "Ver"
    Then navega a la pantalla de detalle
    And ve descripción del grupo
    And ve listado de empresas asignadas (solo lectura)
    And ve botones Editar y Eliminar

  Scenario: Grupo no existe (404)
    Given el administrador accede a /grupos-empresarios/999 (id inexistente)
    When la API responde 404
    Then ve mensaje apropiado
    And es redirigido al listado

  Scenario: Navegación a editar desde detalle
    Given el administrador está en el detalle de un grupo
    When hace clic en "Editar"
    Then navega al formulario de edición con datos cargados
```

---

## 3) Reglas de Negocio

- Solo administradores. Información solo lectura; modificar vía pantalla edición.

---

## 4) Impacto en Datos

- Sin cambios. Lectura PQ_GrupoEmpresario, PQ_GrupoEmpresario_Empresas, PQ_Empresa.

---

## 5) Contratos de API

- GET /api/grupos-empresarios/{id}: ya definido en TR-003. Incluir empresas con nombre.
- Response: { id, descripcion, empresas: [{ id, nombreEmpresa }] }

---

## 6) Cambios Frontend

- Pantalla detalle: descripción, grilla o lista de empresas (solo lectura). Botones Editar, Eliminar.
- Ruta /grupos-empresarios/:id. Manejar 404 (redirigir a listado).
- data-testid: grupoEmpresario.detail, grupoEmpresario.detail.empresas, grupoEmpresario.detail.edit

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD | Deps |
|----|------|-------------|-----|------|
| T1 | Backend | GET grupos-empresarios/{id} con empresas (si no existe en TR-003) | Detalle completo | - |
| T2 | Frontend | Pantalla detalle (solo lectura) | Descripción, listado empresas | HU-001 |
| T3 | Frontend | Navegación desde listado, botones editar/eliminar | Flujo completo | T2 |
| T4 | Frontend | Manejo 404: redirigir a listado | UX correcta | T3 |
| T5 | Tests | E2E: ver detalle desde listado, navegar a editar | Playwright | T3 |

---

## Archivos creados/modificados

### Backend
- GET grupos-empresarios/{id} ya implementado en TR-003 (GrupoEmpresarioController::show)

### Frontend
- `frontend/src/features/admin/pages/GrupoEmpresarioDetallePage.tsx` (creado)
- `frontend/src/app/App.tsx` (ruta /admin/grupos-empresarios/:id)
- `frontend/src/features/admin/pages/GruposEmpresariosAdminPage.tsx` (enlace Ver en columna Acciones)

## Comandos ejecutados

```bash
cd frontend && npm run build
```

## Notas y decisiones

- Vista solo lectura: descripción, listado de empresas.
- Botones Editar y Eliminar en cabecera.

## Pendientes / follow-ups

- Ninguno
