# HU-007 – Parámetros generales del sistema

## Épica
000 – Generalidades

## Clasificación
MUST-HAVE

## Rol
Usuario con permiso de configuración (por módulo)

## Narrativa

Como usuario responsable de la configuración quiero editar los parámetros generales de cada módulo desde una pantalla dedicada para que el sistema se adapte a las necesidades de mi empresa sin modificar código.

## Criterios de aceptación

- Existe un proceso general de mantenimiento de la tabla `PQ_PARAMETROS_GRAL`.
- El proceso se invoca desde ítems de menú; cada ítem tiene `PQ_MENUS.Procedimiento` = nombre clave del módulo (ej. `PartesProduccion`).
- Solo se muestran y editan los registros cuyo `Programa` coincide con el `procedimiento` del ítem de menú desde el que se accedió.
- El proceso **no permite** agregar ni eliminar registros; solo editar el campo `Valor_*` correspondiente según `tipo_valor` de cada fila.
- Los parámetros se consultan en la base de datos de la empresa activa (cada empresa tiene su propia BD con su instancia de `PQ_PARAMETROS_GRAL`).
- Cada fila muestra: Clave, descripción (label), y el campo de valor editable según tipo (string, int, datetime, bool, decimal, text).
- Al guardar, se validan los tipos y rangos según corresponda.
- Los registros iniciales se cargan vía seed en los deploys (igual que `PQ_MENUS`).

## Tablas involucradas

- `PQ_PARAMETROS_GRAL` (en Company DB): Programa, Clave, tipo_valor, Valor_String, Valor_Text, Valor_Int, Valor_DateTime, Valor_Bool, Valor_Decimal
- `PQ_MENUS` (en Diccionario): procedimiento (vincula al proceso y filtra por módulo)

## Reglas de negocio

- Solo se editan valores; la estructura (claves) se define en seeds por módulo.
- El usuario debe tener permiso para la empresa activa.
- El proceso es reutilizable: cada módulo define sus claves en su propia HU de parámetros e invoca este proceso indicando su nombre clave.

## Dependencias

- HU-001 (Login) – autenticación
- HU-002 (Cambio empresa) – empresa activa en contexto
- Tabla `PQ_PARAMETROS_GRAL` creada (migración/script)
- Seeds por módulo que definen las filas (Programa, Clave, tipo_valor)

## HUs que invocan este proceso

Cada módulo que tenga parámetros generales tendrá una HU que:
- Define el nombre clave (PROGRAMA) del módulo.
- Lista las claves con sus tipos.
- Incluye un ítem de menú con `procedimiento` = nombre clave, que abre este proceso filtrando por ese valor.

## Referencias

- `docs/00-contexto/05-parametros-generales.md` – Objetivo y diseño
- `docs/modelo-datos/md-empresas/pq-parametros-gral.md` – Esquema y erDiagram (tabla en Company DB)
- `.cursor/rules/27-parametros-generales-por-modulo.md` – Formato de la HU por módulo
- `.cursor/rules/28-plan-tareas-hu-parametros-generales.md` – Plan de tareas del TR
