# Análisis de consistencia: MCP MS SQL vs md-diccionario

**Fecha:** 2026-03-01  
**Fuente:** MCP user-mssql (list_tables, execute_sql)  
**Referencia:** `docs/modelo-datos/md-diccionario/md-diccionario.md` y `md-diccionario-diagramas.md`

---

## Contexto

El MCP `user-mssql` está conectado a la base de datos **Lidr** (según `TABLE_CATALOG` en `INFORMATION_SCHEMA`).  
El proyecto espera trabajar con la base **Diccionario_000205_012** según `backend/.env.example` y el comando `db:analyze-consistency`.

> **Nota:** Si el MCP debe analizar Diccionario_000205_012, verificar que la variable de entorno `MSSQL_DATABASE` del MCP apunte a esa base.

---

## Resumen ejecutivo

| Tipo | Cantidad |
|------|----------|
| **Errores** (tablas/columnas faltantes) | 20 |
| **Advertencias** (tipos distintos, columnas extra) | 8 |

---

## ERRORES – Tablas y columnas faltantes

### Tablas faltantes (no existen en la BD)

La base **Lidr** solo contiene la tabla `USERS`. Faltan todas las tablas del diccionario:

- `pq_menus`
- `PQ_Empresa`
- `Pq_Rol`
- `PQ_RolAtributo`
- `Pq_Permiso`
- `pq_grid_layouts`
- `PQ_GrupoEmpresario`
- `PQ_GrupoEmpresario_Empresas`
- `PQ_REPORTE_IA`
- `PQ_SistemaAlarmas_Cabecera`
- `PQ_SistemaAlarmas_Detalle`
- `PQ_TareasProgramadas_Cabecera`
- `PQ_TareasProgramadas_Parametros`

### Tabla `USERS` – Columnas faltantes vs md-diccionario

| Esperado (md-diccionario) | En BD | Estado |
|---------------------------|-------|--------|
| `codigo` | `code` | Nombre distinto |
| `name_user` | — | **Faltante** |
| `email` | — | **Faltante** |
| `password_hash` | `password_hash` | ✓ OK |
| `first_login` | — | **Faltante** |
| `supervisor` | — | **Faltante** |
| `activo` | `activo` | ✓ OK |
| `inhabilitado` | `inhabilitado` | ✓ OK |
| `token` | — | **Faltante** |
| `menu_abrir_nueva_pestana` | — | **Faltante** |
| `locale` | — | **Faltante** |
| `created_at` | `created_at` | ✓ OK |
| `updated_at` | `updated_at` | ✓ OK |

---

## ADVERTENCIAS – Diferencias de esquema

### Tabla `USERS`

| Esperado (md-diccionario) | En BD |
|---------------------------|-------|
| `codigo` (nvarchar) | `code` (NVARCHAR(50)) – nombre distinto |
| `id` (bigint) | `id` (BIGINT) ✓ |

### Columnas extra en BD (no en md-diccionario)

- Ninguna relevante; la tabla USERS en BD tiene menos columnas que la esperada.

---

## Comparación de tipos de datos (tabla USERS)

| Columna | md-diccionario | En BD | Coincide |
|---------|----------------|-------|----------|
| id | bigint | BIGINT | ✓ |
| codigo/code | nvarchar(20) | NVARCHAR(50) | ⚠️ Longitud distinta |
| password_hash | nvarchar(255) | NVARCHAR(255) | ✓ |
| activo | bit | BIT | ✓ |
| inhabilitado | bit | BIT | ✓ |
| created_at | datetime | DATETIME | ✓ |
| updated_at | datetime | DATETIME | ✓ |

---

## Recomendaciones

1. **Verificar base de datos del MCP:** Confirmar que `MSSQL_DATABASE` apunte a `Diccionario_000205_012` si se desea analizar el diccionario completo.

2. **Tabla USERS (base Lidr):** Si esta base se usa como diccionario:
   - Renombrar `code` → `codigo` (o añadir alias en la aplicación).
   - Añadir columnas: `name_user`, `email`, `first_login`, `supervisor`, `token`, `menu_abrir_nueva_pestana`, `locale`.

3. **Tablas faltantes:** Ejecutar migraciones o scripts CREATE del `md-diccionario.md` para crear todas las tablas del diccionario en la base objetivo.

4. **Análisis con Laravel:** Para un análisis más completo contra la base configurada en Laravel, ejecutar:
   ```bash
   cd backend
   php artisan db:analyze-consistency
   ```

---

## Cómo volver a ejecutar el análisis

### Vía MCP user-mssql

```text
1. list_tables con output_format: "detailed" – obtiene esquema de tablas
2. execute_sql con: SELECT TABLE_CATALOG, TABLE_SCHEMA, TABLE_NAME 
   FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'
```

### Vía comando Laravel (usa conexión sqlsrv de config)

```bash
cd backend
php artisan db:analyze-consistency
php artisan db:analyze-consistency --db=Diccionario_000205_012
```
