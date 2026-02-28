# Actualización del menú del sistema (pq_menus)

Guía de cuándo y cómo actualizar la tabla `pq_menus` en la base de datos.

---

## 1. Actualizar cuando modificas el JSON (caso principal)

**Cuándo:** Cada vez que editas `docs/backend/seed/PQ_MENUS/PQ_MENUS.seed.v2.json` (agregar, modificar o reordenar opciones de menú).

**Comando:**
```bash
cd backend
php artisan db:seed --class=PqMenuSeeder
```

**Qué hace:** Sincroniza la tabla `pq_menus` con el contenido del JSON. Es idempotente: inserta registros nuevos y actualiza los existentes sin duplicar.

**Formato de entrada en el JSON:**
```json
{
  "id": 100311,
  "text": "Listado Grupos Empresarios",
  "expanded": 0,
  "parent": 100300,
  "order": 1,
  "enabled": 1,
  "procedimiento": "grupo_empresario_listado",
  "routeName": "/grupos-empresarios",
  "tipo": "ABM"
}
```

---

## 2. Setup inicial del entorno

**Cuándo:** Primera vez que configuras el proyecto o tras un `migrate:fresh` que borra todos los datos.

**Comando:**
```bash
cd backend
php artisan migrate:fresh --seed
```

**Qué hace:** Ejecuta todas las migraciones y luego el `DatabaseSeeder` completo (incluye `PqMenuSeeder`). El menú queda cargado automáticamente.

---

## 3. Migraciones sin borrar datos

**Cuándo:** Ejecutaste solo `migrate` (o `migrate:fresh` sin `--seed`) y la tabla `pq_menus` está vacía.

**Comando:**
```bash
cd backend
php artisan db:seed
```

**Qué hace:** Ejecuta el `DatabaseSeeder` completo (tipos de cliente, tipos de tarea, usuarios, clientes, menú). Incluye `PqMenuSeeder`.

---

## 4. Solo actualizar el menú (sin otros seeds)

**Cuándo:** Quieres cargar o actualizar únicamente el menú, sin tocar el resto de datos.

**Comando:**
```bash
cd backend
php artisan db:seed --class=PqMenuSeeder
```

**Qué hace:** Ejecuta solo `PqMenuSeeder`. Útil cuando el resto de datos ya está bien y solo cambiaste el JSON.

---

## 5. SQL Server puro (sin Laravel)

**Cuándo:** Usas SQL Server directamente, sin pasar por Laravel.

**Acción:** Ejecutar el script `docs/backend/seed/PQ_MENUS/Seed_PQ_MENUS.v2.sql` en el motor de base de datos.

**Nota:** Si modificas el JSON, debes regenerar o editar manualmente el script SQL para reflejar los cambios.

---

## Cuándo NO ejecutar el seeder

- **No** ejecutes `PqMenuSeeder` en cada arranque del entorno de desarrollo. Es innecesario si el menú ya está cargado y no cambiaste el JSON.
- **No** lo incluyas en scripts de inicio diario: añade ~130+ operaciones de BD sin beneficio.

---

## Resumen rápido

| Situación | Comando |
|-----------|---------|
| Modificaste el JSON | `php artisan db:seed --class=PqMenuSeeder` |
| Setup inicial / entorno limpio | `php artisan migrate:fresh --seed` |
| Migraste sin seed, tabla vacía | `php artisan db:seed` |
| Solo menú, sin otros seeds | `php artisan db:seed --class=PqMenuSeeder` |
| SQL Server sin Laravel | Ejecutar `Seed_PQ_MENUS.v2.sql` |

---

## Referencias

- `docs/backend/seed/PQ_MENUS/README.md` – Archivos y estructura del seed
- `docs/03-historias-usuario/Historia_PQ_MENUS_seed.md` – Flujo completo y criterios de aceptación
- `.cursor/rules/13-user-story-to-task-breakdown.md` – Inclusión de la tarea de menú en TRs
