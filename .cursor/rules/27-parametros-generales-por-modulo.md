# Regla: Parámetros generales al generar HUs de módulo

## Objetivo

Cada vez que se solicite generar **todas las historias de usuario** de un tema o módulo específico, la generación debe incluir la definición de **parámetros generales** del módulo y una **HU que invoque al proceso de mantenimiento** (HU-007).

## Alcance

- **Cuándo aplica:** Al generar el conjunto completo de HUs de un módulo (ej. Partes de Producción, Ventas, Stock).
- **Qué incluir:** Una HU de parámetros generales del módulo que defina claves, tipos y que invoque al proceso general.

## Formato de la HU de parámetros del módulo

### 1. Nombre clave del módulo (campo PROGRAMA)

Definir una **palabra clave sin espacios** para el módulo (ej. `PartesProduccion`, `Ventas`, `Stock`). Este valor se almacena en `PQ_PARAMETROS_GRAL.Programa` y en `PQ_MENUS.Procedimiento` del ítem de menú que abre el proceso.

### 2. Tabla de claves y tipos

Para cada parámetro configurable, definir:

| CLAVE (campo Clave) | TIPO_VALOR | Descripción |
|---------------------|------------|-------------|
| nombre_parametro    | S / T / I / D / B / N | Breve descripción |

**Tipos:** S=varchar, T=text, I=int, D=datetime, B=bit, N=decimal

### 3. Criterios de aceptación

- La HU **invoca** al proceso general de parámetros (HU-007), filtrando por el nombre clave del módulo.
- Se lista la tabla de claves con sus tipos.
- Se aclara que el proceso **no permite** agregar ni eliminar registros; solo editar el campo `Valor_*` según `tipo_valor`.

### 4. Referencias

- `docs/03-historias-usuario/000-Generalidades/HU-007-Parametros-generales.md` – Proceso general
- `docs/modelo-datos/md-empresas/pq-parametros-gral.md` – Diseño de la tabla (Company DB)
- `docs/00-contexto/05-parametros-generales.md` – Objetivo y reglas

## Ejemplo

```markdown
## Parámetros del módulo PartesProduccion

**PROGRAMA:** PartesProduccion

| CLAVE | TIPO_VALOR | Descripción |
|-------|------------|-------------|
| duracion_minima_minutos | I | Duración mínima de un parte (minutos) |
| duracion_maxima_horas | N | Duración máxima por parte (horas) |
| fecha_limite_edicion_dias | I | Días hacia atrás permitidos para editar |
| requiere_aprobacion | B | Si los partes requieren aprobación |

El ítem de menú "Parámetros Partes" tendrá `procedimiento = 'PartesProduccion'` para filtrar estos registros.
```

## Referencias

- `.cursor/rules/26-dashboard-indicadores-por-modulo.md` – Indicadores (también obligatorio por módulo)
- `.cursor/rules/28-plan-tareas-hu-parametros-generales.md` – Plan de tareas del HU-007
