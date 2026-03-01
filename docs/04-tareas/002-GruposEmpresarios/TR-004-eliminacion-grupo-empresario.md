# TR-004 – Eliminación de grupo empresario

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-004 – Eliminación de grupo empresario   |
| Épica              | 002 – Grupos empresarios                   |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Administrador                              |
| Dependencias       | HU-001, HU-002, HU-003 (Listado, Creación, Edición) |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-27                               |
| Estado             | Implementado                                |

**Origen:** [HU-004 – Eliminación de grupo empresario](../../03-historias-usuario/002-GruposEmpresarios/HU-004-eliminacion-grupo-empresario.md)

---

## 1) HU Refinada

- **Título:** Eliminación de grupo empresario
- **Narrativa:** Como administrador quiero eliminar un grupo empresario cuando ya no se utilice para liberar el diccionario.
- **Contexto:** Confirmación antes de eliminar. Cascada: primero PQ_GrupoEmpresario_Empresas, luego PQ_GrupoEmpresario. Validar referencias (parámetros, módulos).
- **In scope:** Eliminación con confirmación, validación dependencias, mensajes claros.
- **Out of scope:** Soft delete (eliminación física).

---

## 2) Criterios de Aceptación

- Solicitar eliminación desde listado o detalle. Confirmación: "¿Está seguro de eliminar el grupo X?"
- Al confirmar: eliminar PQ_GrupoEmpresario_Empresas, luego PQ_GrupoEmpresario.
- Si grupo referenciado (parámetros, procesos): validar e informar antes de permitir.
- Si hay dependencias que impiden: mensaje claro indicando dónde se usa.
- Tras eliminar: redirigir a listado con mensaje éxito.

### Escenarios Gherkin

```gherkin
Feature: Eliminación de grupo empresario

  Scenario: Administrador elimina grupo sin dependencias
    Given existe un grupo sin referencias en parámetros/procesos
    When el administrador hace clic en Eliminar
    And confirma en el diálogo "¿Está seguro de eliminar el grupo X?"
    Then se eliminan registros de PQ_GrupoEmpresario_Empresas
    And se elimina el registro de PQ_GrupoEmpresario
    And es redirigido a listado con mensaje éxito

  Scenario: Error - grupo con dependencias
    Given el grupo está referenciado en parámetros u otros módulos
    When el administrador intenta eliminar
    Then recibe 409 Conflict
    And mensaje indicando dónde se usa el grupo
    And el grupo no se elimina

  Scenario: Confirmación antes de eliminar
    Given el administrador hace clic en Eliminar
    When aparece el diálogo de confirmación
    And hace clic en Cancelar
    Then el grupo no se elimina
    And permanece en el listado
```

---

## 3) Reglas de Negocio

- Solo administradores. Cascada: primero tabla unión, luego grupo. Bloquear o advertir si en uso.

---

## 4) Impacto en Datos

- Delete PQ_GrupoEmpresario_Empresas (where id_grupo). Delete PQ_GrupoEmpresario.
- Validar referencias en PQ_Parametros_Gral u otras tablas de módulos.

---

## 5) Contratos de API

- DELETE /api/grupos-empresarios/{id}: eliminar grupo. Validar dependencias antes.
- Si dependencias: 409 Conflict con mensaje detallado. Si OK: 204.
- Opcional: GET /api/grupos-empresarios/{id}/dependencias para pre-validar.

---

## 6) Cambios Frontend

- Botón eliminar en listado y detalle. Diálogo confirmación. Llamar DELETE. Manejar 409 (mostrar mensaje dependencias).
- Redirección a listado tras éxito.
- data-testid: grupoEmpresario.delete, grupoEmpresario.delete.confirm

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD | Deps |
|----|------|-------------|-----|------|
| T1 | Backend | DELETE grupos-empresarios/{id} | Cascada delete | - |
| T2 | Backend | Validación dependencias (parámetros, etc.) | 409 si en uso | T1 |
| T3 | Frontend | Diálogo confirmación eliminación | Confirmar antes de eliminar | HU-001 |
| T4 | Frontend | Llamar DELETE, manejar 409, redirección | Flujo completo | T3 |
| T5 | Tests | Integration: eliminar OK, eliminar con dependencias (409) | Tests pasan | T1, T2 |
| T6 | Tests | E2E: eliminar grupo con confirmación | Playwright | T4 |

---

## Archivos creados/modificados

### Backend
- `backend/app/Http/Controllers/Api/V1/Admin/GrupoEmpresarioController.php` (método destroy)
- `backend/routes/api.php` (ruta DELETE grupos-empresarios/{id})

### Frontend
- `frontend/src/features/admin/services/admin.service.ts` (delete en adminGruposEmpresariosApi)
- `frontend/src/features/admin/pages/GruposEmpresariosAdminPage.tsx` (botón Eliminar en columna Acciones)
- `frontend/src/features/admin/pages/GrupoEmpresarioDetallePage.tsx` (botón Eliminar)

## Comandos ejecutados

```bash
cd frontend && npm run build
```

## Notas y decisiones

- Confirmación con `window.confirm()`.
- `tieneDependencias()` retorna false (stub); extensible para PQ_PARAMETROS_GRAL u otras tablas.
- DELETE retorna 204 No Content.

## Pendientes / follow-ups

- Implementar validación real de dependencias si se identifican tablas que referencien id_grupo.
