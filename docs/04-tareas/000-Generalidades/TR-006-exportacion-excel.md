# TR-006 – Exportación a Excel desde grillas

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-006 – Exportación a Excel desde grillas |
| Épica              | 000 – Generalidades                        |
| Prioridad          | SHOULD-HAVE                                |
| Roles              | Usuario que opera con grillas              |
| Dependencias       | HU-001 (Layouts), estándar grillas         |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-27                               |
| Estado             | Implementado                               |

**Origen:** [HU-006 – Exportación a Excel desde grillas](../../03-historias-usuario/000-Generalidades/HU-006-exportacion-excel.md)

---

## 1) HU Refinada

- **Título:** Exportación a Excel desde grillas
- **Narrativa:** Como usuario que trabajo con grillas quiero exportar los datos visibles a Excel para analizarlos fuera del sistema, compartirlos o archivarlos, con la opción de elegir el nivel de formato según mi necesidad.
- **Contexto:** Toda grilla del sistema (según estándar HU-001 y 24-devextreme-grid-standards) debe ofrecer botón de exportación. Tres modalidades: planilla básica, planilla formateada, tabla dinámica (para pivots).
- **Suposiciones:** Grillas usan DevExtreme DataGrid; librería `xlsx` ya en proyecto; identificación proceso+grid_id según HU-001.
- **In scope:** Botón Exportar, tres modalidades, datos con filtros aplicados, respeto de permisos.
- **Out of scope:** Exportación batch/programática; exportación a PDF; personalización de columnas solo para exportación.

---

## 2) Criterios de Aceptación

- Botón "Exportar a Excel" en toolbar de cada grilla; habilitado solo si hay datos.
- Sin datos: mensaje "No hay datos para exportar", botón deshabilitado.
- Modalidad 1 (Planilla básica): encabezados + filas, sin formato, datos crudos.
- Modalidad 2 (Planilla formateada): títulos resaltados, fechas/números formateados, totales en pie, ancho de columnas.
- Modalidad 3 (Tabla dinámica): para PivotGrid, exportar como pivot table de Excel.
- Usuario elige modalidad antes de exportar (dropdown/menú); por defecto: planilla formateada.
- Opción "exportar solo filas seleccionadas" si hay selección múltiple.
- Datos exportados: vista actual (filtros, ordenamiento, agrupación); paginación: "página actual" o "todas las filas" (límite ej. 10.000).
- Nombre archivo: `{proceso}_{fecha}.xlsx` (ej. clientes_2025-02-27.xlsx).

### Escenarios Gherkin

```gherkin
Feature: Exportación a Excel desde grillas

  Scenario: Usuario exporta grilla con datos (planilla básica)
    Given el usuario está en una pantalla con grilla (ej. Usuarios)
    And hay al menos un registro en la grilla
    When hace clic en "Exportar a Excel"
    And selecciona "Planilla básica"
    Then se descarga un archivo XLSX
    And el archivo contiene encabezados y filas con los datos visibles
    And el nombre del archivo sigue el patrón {proceso}_{fecha}.xlsx

  Scenario: Usuario exporta grilla con datos (planilla formateada)
    Given el usuario está en una pantalla con grilla
    And hay datos en la grilla
    When hace clic en "Exportar a Excel"
    And selecciona "Planilla formateada"
    Then se descarga un archivo XLSX
    And el archivo tiene títulos resaltados y formatos aplicados
    And incluye totales en pie si aplica

  Scenario: Botón deshabilitado sin datos
    Given el usuario está en una pantalla con grilla
    And no hay datos (grilla vacía)
    Then el botón "Exportar a Excel" está deshabilitado
    Or al hacer clic muestra mensaje "No hay datos para exportar"

  Scenario: Exportación respeta filtros aplicados
    Given el usuario está en una grilla con filtros
    And ha aplicado un filtro que reduce los resultados
    When exporta a Excel
    Then el archivo contiene solo los datos que pasan el filtro
    And no incluye los registros filtrados
```

---

## 3) Reglas de Negocio

1. Datos exportados respetan permisos del usuario (misma lógica que la grilla).
2. Filtros aplicados en la grilla se aplican al exportar.
3. Límite razonable de filas (ej. 10.000); si se supera, informar al usuario.
4. Formato: XLSX (compatible Excel y LibreOffice).

---

## 4) Impacto en Datos

- No se crean tablas nuevas.
- Datos se leen de las mismas fuentes que la grilla (API, store).
- No se persiste preferencia de modalidad por usuario (opcional a futuro).

---

## 5) Contratos de API

- No se requieren endpoints nuevos si la grilla tiene todos los datos en memoria.
- Si la grilla usa paginación del servidor y no tiene todos los datos: puede requerirse endpoint de exportación que aplique filtros y devuelva dataset. Contrato dependerá del proceso específico (documentar en TR de cada módulo que lo necesite).

---

## 6) Cambios Frontend

### Componentes

- **Utilidad `exportToExcel`:** Módulo compartido con funciones:
  - `exportBasic(data, columns, filename)` – planilla básica
  - `exportFormatted(data, columns, summaryColumns, filename, locale)` – planilla formateada
  - `exportPivotTable(data, rowFields, columnFields, dataFields, filename)` – tabla dinámica (si aplica)
- **ExportButton / ExportMenu:** Botón en toolbar con dropdown de modalidades.
- **DataGridDX / wrapper:** Integrar ExportButton según estándar; pasar proceso, grid_id, dataSource, columnDefs.

### Integración DevExtreme

- DevExtreme DataGrid ofrece `export` con `enabled: true` y `excelExporter` / ExcelJS.
- Alternativa: usar `xlsx` (ya en proyecto) para control fino.
- Para tabla dinámica: ExcelJS o DevExtreme PivotGrid export.

### data-testid

- `grid.{proceso}.{grid_id}.exportExcel` – botón exportar
- `grid.{proceso}.{grid_id}.exportExcel.menu` – menú de modalidades
- `grid.{proceso}.{grid_id}.exportExcel.option.basic` – opción planilla básica
- `grid.{proceso}.{grid_id}.exportExcel.option.formatted` – opción planilla formateada
- `grid.{proceso}.{grid_id}.exportExcel.option.pivot` – opción tabla dinámica

### Accesibilidad

- `aria-label` con `t("grid.export.aria", "Exportar a Excel")`.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD | Deps |
|----|------|-------------|-----|------|
| T1 | Frontend | Utilidad `exportToExcel`: planilla básica (xlsx) | Función que genera XLSX sin formato, descarga | - |
| T2 | Frontend | Utilidad `exportToExcel`: planilla formateada (títulos, formatos, totales) | Función que genera XLSX formateado, con totales | T1 |
| T3 | Frontend | Utilidad `exportToExcel`: tabla dinámica para pivots (si PivotGrid existe) | Función que genera XLSX con pivot table | - |
| T4 | Frontend | Componente `ExportButton` con menú de modalidades | Botón visible, deshabilitado si sin datos, data-testid | HU-001 |
| T5 | Frontend | Integrar ExportButton en DataGrid wrapper (estándar grillas) | Cada grilla con proceso+grid_id tiene export | T4, T1, T2 |
| T6 | Frontend | Lógica "exportar página actual" vs "todas las filas" (si paginación) | Opción según configuración de grilla | T5 |
| T7 | Frontend | Opción "exportar solo filas seleccionadas" | Si selection múltiple, exportar solo seleccionados | T5 |
| T8 | Tests | Unit: utilidades exportToExcel (planilla básica, formateada) | Tests pasan | T1, T2 |
| T9 | Tests | E2E: exportar desde grilla, verificar descarga y contenido | Playwright cubre al menos 1 grilla | T5 |
| T10 | Docs | Actualizar 24-devextreme-grid-standards con exportación | Regla incluye export como característica obligatoria | T5 |

---

## 8) Estrategia de Tests

### Unit

- Utilidades exportToExcel: dado un dataset, el XLSX generado contiene los datos esperados.
- Planilla formateada: totales correctos, formatos de fecha/número según locale.

### Integration

- No aplica (exportación es cliente-side con datos ya cargados).

### E2E (Playwright)

- Navegar a pantalla con grilla → clic en Exportar → verificar que se descarga archivo.
- Verificar nombre de archivo y que contiene datos (opcional: leer XLSX con librería).

---

## Dependencias

- **HU-001 (Layouts persistentes de grillas)** – estándar de grillas, identificación proceso+grid_id. Si HU-001 no está implementada, usar convención proceso+grid_id en el TR.
- `.cursor/rules/24-devextreme-grid-standards.md`
- Librería `xlsx` (ya en proyecto)

---

## Archivos creados/modificados

<!-- Completar al ejecutar -->

## Comandos ejecutados

<!-- Completar al ejecutar -->

## Notas y decisiones

<!-- Completar al ejecutar -->

## Pendientes / follow-ups

<!-- Completar al ejecutar -->
