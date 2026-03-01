# Análisis de consistencia: Diccionario_000205_012 vs md-diccionario-diagramas.md

**Fecha:** 2026-03-01  
**Base de datos:** Diccionario_000205_012  
**Referencia:** `docs/modelo-datos/md-diccionario/md-diccionario-diagramas.md` y `md-diccionario.md`

> **Alcance:** Solo se comparan las tablas definidas en el md-diccionario. Se ignoran las tablas adicionales en la BD (Tango/PaqSystems).

---

## Resumen ejecutivo

| Tipo | Cantidad |
|------|----------|
| **Errores** (tablas/columnas faltantes) | 15 |
| **Advertencias** (tipos distintos, columnas extra en tablas del diccionario) | 12 |

---

## ERRORES – Tablas y columnas faltantes

### Tablas faltantes (no existen en Diccionario_000205_012)

- `pq_menus`
- `Pq_Rol`
- `PQ_RolAtributo`
- `Pq_Permiso`
- `pq_grid_layouts`
- `PQ_GrupoEmpresario`
- `PQ_GrupoEmpresario_Empresas`

### Tabla `PQ_REPORTE_IA` – esquema distinto

La tabla existe pero con esquema legacy. Columnas esperadas vs actuales:

| Esperado (md-diccionario) | En BD |
|---------------------------|-------|
| `procedimiento` | **Faltante** |
| `Name` | **Faltante** |
| `DisplayName` | **Faltante** |
| `LayoutData` | **Faltante** |
| `Empresa` | **Faltante** |
| `created_at` | **Faltante** |
| `updated_at` | **Faltante** |
| `Empresas` | **Faltante** |

### Tablas que sí existen y coinciden

- `users` (con advertencia menor: `password` vs `password_hash`)
- `PQ_Empresa`
- `PQ_SistemaAlarmas_Cabecera`
- `PQ_SistemaAlarmas_Detalle`
- `PQ_TareasProgramadas_Cabecera`
- `PQ_TareasProgramadas_Parametros`

---

## ADVERTENCIAS – Diferencias en tablas del diccionario

### Tabla `users`

| Esperado (md-diccionario) | En BD |
|---------------------------|-------|
| `password_hash` | `password` (nvarchar) – nombre distinto |

**Columna extra en BD:** `password` (el diseño espera `password_hash`).

### Tabla `PQ_REPORTE_IA`

Esquema legacy en BD. Columnas actuales:

| Campo en BD | Tipo |
|-------------|------|
| id | int |
| nombre | varchar |
| descripcion | text |
| reporte | varbinary |
| proceso | varchar |
| nombre_proceso | varchar |
| imagen_boton | image |
| usuario | varchar |
| creacion | datetime |
| modificacion | datetime |
| eliminado | datetime |

---

## Recomendaciones

1. **users:** Crear migración para renombrar `password` → `password_hash` (o añadir columna y migrar datos).
2. **Tablas faltantes:** Ejecutar scripts CREATE del `md-diccionario.md` para crear: `pq_menus`, `Pq_Rol`, `PQ_RolAtributo`, `Pq_Permiso`, `pq_grid_layouts`, `PQ_GrupoEmpresario`, `PQ_GrupoEmpresario_Empresas`.
3. **PQ_Empresa:** Verificar que exista la columna `theme` (si falta, añadirla).

4. **Dualidad de esquemas:** El backend (AuthService, PqEmpresa, SetCompanyConnection, GrupoEmpresarioController) soporta tanto **PQ_Empresa** (IDEmpresa, NombreEmpresa, NombreBD, Habilita) como **pq_empresa** (id, nombre_empresa, nombre_bd, habilita de la migración Laravel). La detección es automática según `Schema::hasColumn('pq_empresa', 'IDEmpresa')`.
4. **PQ_REPORTE_IA:** Evaluar si se migra el esquema legacy al nuevo o se mantiene el actual con mapeo en la aplicación.

---

## Cómo volver a ejecutar el análisis

```bash
cd backend
php artisan db:analyze-consistency --db=Diccionario_000205_012
```
