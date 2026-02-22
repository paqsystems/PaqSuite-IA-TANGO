# Instrucciones para Crear Pull Request - Entrega 2

## Información del PR

**Rama Base (target):** `main`  
**Rama Compare (source):** `feature-entrega2-PAQ`  
**Repositorio:** (usar la URL real del repo, p. ej. https://github.com/paqsystems/Lidr-AI4Devs2025-ProyectoFinal)

---

## Crear el Pull Request

### Opción 1: Desde GitHub (Recomendado)

1. Ir a la página del repositorio y crear un nuevo PR.
2. **Base:** `main`
3. **Compare:** `feature-entrega2-PAQ`
4. Completar título y descripción según se indica abajo.

### Opción 2: Desde la línea de comandos (gh cli)

```bash
gh pr create --base main --head feature-entrega2-PAQ --title "feat: Entrega 2 - Código funcional, primer MVP ejecutable" --body-file docs/_ENTREGA-2/INSTRUCCIONES-PR-ENTREGA-2.md
```

(Nota: el body del PR debe pegarse desde la "Descripción del PR" de este archivo.)

---

## Título del PR

```
feat: Entrega 2 - Código funcional, primer MVP ejecutable
```

---

## Descripción del PR

Copia y pega el siguiente contenido en la descripción del PR:

```markdown
## Entrega 2 - Código funcional (primer MVP ejecutable)

Esta PR contiene el código funcional requerido para la Entrega 2: backend, frontend y base de datos conectados, con el flujo principal "casi" completo.

### Referencias a consignas

- Rama: `feature-entrega2-PAQ` (formato feature-entrega2-[iniciales])
- Requisito: "Código funcional: Backend, frontend y base de datos ya conectados, con el flujo principal 'casi' completo."
- Verificación: `docs/_ENTREGA-2/VERIFICACION-ENTREGA-2.md`

### Contenido principal

#### Evidencia visual
- Se incluye el video **"Proyecto Final - Entrega 2.mp4"** para visualizar el funcionamiento del proyecto y demostrar el flujo principal implementado.

#### Backend (Laravel + Sanctum)
- Autenticación: login, logout (HU-001)
- Perfil de usuario (TR-006)
- Dashboard (HU-051)
- Informes: consulta detallada, por cliente (HU-044, HU-046)
- **Clientes:** CRUD completo + asignación tipos de tarea (HU-008 a HU-012, TR-008 a TR-012)
- **Tareas:** CRUD, listado propio/supervisor, clientes, tipos de tarea, empleados (HU-028, HU-029, HU-030, HU-033, HU-034)

#### Frontend (React + TypeScript)
- Login y navegación
- Gestión de clientes: listado, alta, edición, eliminación, tipos de tarea
- Gestión de tareas: listado, alta, edición, eliminación, filtros
- Consulta detallada y por cliente
- Dashboard y perfil de usuario

#### Base de datos
- Conexión SQL Server (Lidr)
- Migraciones y seeders documentados en `docs/deploy-ci-cd.md`

#### Tests
- Backend: PHPUnit (Unit + Feature)
- Frontend: Vitest (servicios, lógica)
- E2E: Playwright (auth, tareas, clientes, informes, dashboard, perfil)


### Historias / Tickets cubiertos

- **HU-001:** Autenticación
- **HU-008 a HU-012:** ABM Clientes + asignación tipos de tarea (TR-008 a TR-012)
- **HU-028, HU-029, HU-030:** Carga, edición y eliminación de tareas
- **HU-031, HU-032:** Edición/eliminación por supervisor
- **HU-033, HU-034:** Listado de tareas propias / todas
- **HU-044, HU-046:** Consulta detallada y agrupada por cliente
- **HU-051:** Dashboard principal

### Próximos pasos

- Entrega 3: Versión completa desplegada, CI/CD, URL pública.

### Referencias

- Consignas: `.cursor/consignas.md`
- Verificación Entrega 2: `docs/_ENTREGA-2/VERIFICACION-ENTREGA-2.md`
- Registro de IA: `docs/ia-log.md`
- Prompts: `prompts.md`
```

---

## Después de Crear el PR

1. **Copiar la URL del PR** (ej: `https://github.com/.../pull/N`).
2. **Completar el formulario de entrega:**
   - URL: https://lidr.typeform.com/proyectoai4devs
   - Incluir la URL del Pull Request de la Entrega 2.

---

## Verificación previa

Antes de crear el PR, comprobar:

- [ ] Rama base: `main`
- [ ] Rama compare: `feature-entrega2-PAQ`
- [ ] Cambios commiteados y push realizados
- [ ] Tests pasando en backend y frontend (según documentación en `docs/testing.md`)

---

**Última actualización:** 2026-01-31
