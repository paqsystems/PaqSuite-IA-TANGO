# Seed PQ_MENUS

Fuente de verdad del menú del sistema. Ver `docs/03-hu-historias/Historia_PQ_MENUS_seed.md` para el flujo completo.

## Archivos

| Archivo | Propósito |
|---------|-----------|
| `PQ_MENUS.seed.v2.json` | Fuente de verdad (versionada en repo) |
| `Seed_PQ_MENUS.v2.sql` | Script SQL Server (MERGE idempotente) |

## Ejecución

**Laravel (MySQL / SQL Server):**
```bash
php artisan db:seed --class=PqMenuSeeder
```

**SQL Server puro:**
```sql
-- Ejecutar Seed_PQ_MENUS.v2.sql
```

## Agregar nueva opción

1. Editar `PQ_MENUS.seed.v2.json` con la nueva entrada (id, text, parent, order, procedimiento, routeName, tipo).
2. Ejecutar PqMenuSeeder.
3. Si se usa SQL Server puro: regenerar el SQL o añadir manualmente la fila al script.
