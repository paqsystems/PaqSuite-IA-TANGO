# Pull Request – Parámetros generales, PQ_PARAMETROS_GRAL (Company DB), fix CI DevExtreme

## Título sugerido

```
docs: parámetros generales (Company DB), HU-007, reglas 27-28, fix CI DevExtreme
```

## Descripción

Implementación documental de parámetros generales por módulo, corrección de la ubicación de `PQ_PARAMETROS_GRAL` (tabla en Company DB, no en diccionario) y corrección del fallo de build en CI por licencia DevExtreme.

## Cambios incluidos

### 1. Parámetros generales (05-parametros-generales.md)

- Documento de contexto con objetivo, diseño y checklist completado.
- Tabla `PQ_PARAMETROS_GRAL` en **Company DB** (una BD por empresa), no en diccionario.

### 2. Diseño PQ_PARAMETROS_GRAL

- **`docs/modelo-datos/md-empresas/pq-parametros-gral.md`**: CREATE TABLE, erDiagram, mapeo tipo_valor.
- Eliminada de `md-diccionario.md` y `md-diccionario-diagramas.md` (no pertenece al diccionario).

### 3. HU-007 y reglas

- **HU-007-Parametros-generales.md**: proceso general de mantenimiento (solo edición de valores).
- **Regla 27**: formato de la HU de parámetros al generar HUs de un módulo.
- **Regla 28**: plan de tareas del HU-007.
- README Generalidades actualizado con HU-007.

### 4. Fix CI – DevExtreme license

- **`frontend/src/devextreme-license.ts`**: usa `VITE_DEVEXTREME_LICENSE` (env); vacío = modo trial.
- Eliminado del `.gitignore` para que el archivo se versione.
- `.env.example` documentado con la variable opcional.

## Referencias

- `docs/00-contexto/05-parametros-generales.md`
- `docs/modelo-datos/md-empresas/pq-parametros-gral.md`
- `docs/03-hu-historias/000-Generalidades/HU-007-Parametros-generales.md`
