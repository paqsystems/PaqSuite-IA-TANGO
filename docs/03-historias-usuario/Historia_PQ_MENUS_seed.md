# Historia: Inicialización y versionado del Menú del Sistema (PQ_MENUS)

## Objetivo
Implementar una metodología reproducible y versionada para la tabla **dbo.PQ_MENUS** (catálogo de opciones de menú), de forma que el menú pueda reconstruirse de manera consistente en cualquier entorno (dev/test/prod) y quede auditado en control de versiones.

## Alcance (incluye)
- Fuente de verdad del menú en el repositorio (seed).
- Script SQL idempotente para sincronizar **dbo.PQ_MENUS**.
- Validaciones/constraints mínimos para evitar degradación del árbol.
- Integración del seed en el flujo de deploy/migraciones del MVP.

## Fuera de alcance (por ahora)
- ABM completo de edición de menú en UI (opcional a futuro).
- Gestión avanzada de internacionalización (i18n) de textos.
- Sincronización “estricta” con borrado de registros no presentes en el seed (se evalúa más adelante).

## Criterios de aceptación
1. Existe un archivo seed versionado en el repo (ej.: `PQ_MENUS.seed.json`) que representa el árbol.
2. Existe un script `Seed_PQ_MENUS.sql` idempotente (MERGE) que:
   - Inserta faltantes
   - Actualiza cambios
   - No duplica ni rompe jerarquías
3. Al ejecutar el seed en un entorno vacío, el menú queda cargado y navegable.
4. El orden dentro de cada parent es determinístico (0..n) y consistente entre entornos.
5. Existen constraints/índices mínimos para preservar integridad del árbol.

## Tareas técnicas (checklist)
### A. Base de datos
- [ ] Crear tabla `dbo.PQ_MENUS` (si no existe) o ajustar su schema.
- [ ] Agregar índice/constraint **UNIQUE(parent, [order])**.
- [ ] Agregar índice por `parent` (performance para lectura del árbol).
- [ ] (Opcional) Defaults: `expanded=0`, `enabled=1`, `parent=0`.

### B. Seed versionado
- [ ] Agregar al repo el archivo `PQ_MENUS.seed.json` (fuente de verdad).
- [ ] Agregar al repo el script `Seed_PQ_MENUS.sql` (MERGE idempotente).
- [ ] Documentar cómo actualizar el menú (cambio del seed + regeneración del SQL si aplica).

### C. Integración en deploy/migraciones
- [ ] Integrar la ejecución del seed en el pipeline de migraciones del MVP (post-migration step).
- [ ] Asegurar que el seed corre en:
  - dev (automático)
  - test (automático)
  - prod (controlado, con checklist)
- [ ] Registrar en logs que el seed se aplicó correctamente.

### D. Pruebas / verificación
- [ ] Test de humo: ejecutar seed 2 veces y verificar que no duplica (idempotencia).
- [ ] Validación jerárquica: no existen parents inexistentes (salvo `parent=0`).
- [ ] Validación orden: no existen duplicados `(parent, order)`.

## Notas de implementación
- **PQ_MENUS es catálogo**: no debe depender de cargas manuales como fuente de verdad.
- El seed propone **IDs estables** (no identity) para permitir referenciar permisos/roles.
