---
alwaysApply: true
---
# description: Política de Tablas Tango – Separación y No Alteración

## Propósito

Establecer las reglas obligatorias para el tratamiento de tablas del ERP Tango en el proyecto PaqSuite. Los proyectos con Tango se gestionan **por separado** de los proyectos propios.

## Principios Acordados

### a) Diseño de tablas Tango en archivos separados

- El responsable del proyecto **proporcionará en archivos diferentes** el diseño de las tablas Tango necesarias para el funcionamiento particular de cada proceso.
- El agente y los desarrolladores **no deben inferir ni inventar** la estructura de tablas Tango.
- Los diseños de tablas Tango se referencian desde la documentación del módulo, pero **no se documentan en el mismo archivo** que las tablas propias.
- Ubicación esperada: `docs/modelo-datos/tango/` o archivos específicos que el responsable indique.

### b) Tablas Tango: prohibición de alteración

- **Las tablas Tango NO pueden ser alteradas bajo ningún concepto** por el equipo de desarrollo.
- Prohibido: `CREATE`, `ALTER`, `DROP`, `TRUNCATE` o cualquier modificación de estructura o datos en tablas Tango.
- Excepción: solo cuando exista **especificación estricta y explícita** del responsable del proyecto.
- En caso de duda: **no modificar**.

## Alcance

Esta política aplica a:
- Migraciones Laravel (no crear migraciones que alteren tablas Tango)
- Scripts SQL manuales
- Stored procedures que modifiquen tablas Tango
- Cualquier herramienta de sincronización de esquema (DBML, migrations, etc.)

## Relación con tablas propias

- Las tablas propias del proyecto (prefijo `PQ_*`) pueden tener **claves foráneas** hacia tablas Tango (ej. `ID_ARTICULO` → `STA11.ID_STA11`) o hacia otras tablas propias (ej. `ID_OPERARIO` → `PQ_SUELD_LEGAJOS.ID`).
- La integración se realiza mediante:
  - **Vistas** que mapean desde tablas Tango hacia un contrato uniforme (ej. `pq_vwarticulos`, `pq_vwoperarios`).
  - **Solo lectura** sobre tablas Tango; la app consume datos vía vistas o consultas SELECT.
- Las vistas se crean en el esquema de la aplicación, no alteran las tablas Tango.

## Interpretación para Cursor / Agente IA

1. **Nunca** proponer ni ejecutar cambios de esquema en tablas Tango.
2. **Siempre** asumir que el diseño de tablas Tango proviene de archivos proporcionados por el responsable.
3. Si se requiere modificar una tabla Tango, **detener** y solicitar especificación explícita.
4. Al documentar módulos que integran con Tango, referenciar los archivos de diseño Tango proporcionados, no describir la estructura interna.

## Referencias

- `docs/modelo-datos/md-empresas/md-partes-produccion.md` – Ejemplo de integración con STA11/LEGAJO vía vistas
- `debate-db.md` – Contexto histórico sobre Tango vs Propio
- `backend/config/erp.php` – Configuración ERP_MODE (propio | tango)
