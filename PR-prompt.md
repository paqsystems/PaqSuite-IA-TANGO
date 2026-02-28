# Pull Request – v1.1.0: diseño PaqSystems, parámetros generales, fix CI y tests

## Título sugerido

```
feat: diseño PaqSystems, parámetros generales (Company DB), fix CI DevExtreme, tests adaptados
```

## Descripción

Conjunto de cambios para la rama `v1.1.0`: diseño base del Main Shell, parámetros generales por módulo, corrección del build en CI (DevExtreme) y adaptación de los tests al esquema simplificado (solo tabla USERS).

## Cambios incluidos

### 1. Diseño PaqSystems Main Shell

- **`docs/design/paqsystems-main-shell-design.md`**: diseño base Figma adaptado a DevExtreme.
- Selector de idioma: control dedicado en el header (no en menú usuario).
- Menú de usuario (debajo del avatar): Perfil, Cambiar empresa, Cambiar contraseña, Abrir en otra pestaña, Cerrar sesión.
- HUs actualizadas (HU-002, HU-003, HU-004 idioma, HU-004 contraseña, HU-003 logout).
- **Regla 26**: indicadores de dashboard por módulo (visibilidad y rol supervisor/no supervisor).

### 2. Parámetros generales (PQ_PARAMETROS_GRAL)

- **`docs/00-contexto/05-parametros-generales.md`**: objetivo, diseño y checklist.
- **`docs/modelo-datos/md-empresas/pq-parametros-gral.md`**: tabla en **Company DB** (no en diccionario), CREATE TABLE, erDiagram.
- **HU-007-Parametros-generales.md**: proceso general de mantenimiento (solo edición de valores).
- **Reglas 27 y 28**: formato de HU de parámetros por módulo y plan de tareas.

### 3. Fix CI – DevExtreme license

- **`frontend/src/devextreme-license.ts`**: usa `VITE_DEVEXTREME_LICENSE` (env); vacío = modo trial.
- Archivo versionado (eliminado del `.gitignore`).
- `.env.example` documentado.

### 4. Fix tests – schema simplificado (solo USERS)

- **TestUsersSeeder**: solo tabla USERS con `name` y `email`; eliminadas referencias a PQ_PARTES_*.
- **Unit tests**: AuthServiceTest, PasswordResetServiceTest, UserProfileServiceTest adaptados.
- **Feature tests**: LoginTest, LogoutTest, ChangePasswordTest, PasswordResetTest, UserProfileTest con seeds simplificados.
- **Eliminados**: TipoClienteControllerTest, ClienteControllerTest, DashboardControllerTest, EmpleadoControllerTest, ReportControllerTest, TaskControllerTest, TipoTareaControllerTest, TaskServiceTest (controladores/modelos eliminados).

## Referencias

- `docs/design/paqsystems-main-shell-design.md`
- `docs/00-contexto/05-parametros-generales.md`
- `docs/modelo-datos/md-empresas/pq-parametros-gral.md`
- `docs/03-hu-historias/000-Generalidades/HU-007-Parametros-generales.md`
- `.cursor/rules/26-dashboard-indicadores-por-modulo.md`
- `.cursor/rules/27-parametros-generales-por-modulo.md`
- `.cursor/rules/28-plan-tareas-hu-parametros-generales.md`

## Checklist

- [x] Documentación actualizada
- [x] HUs alineadas con diseño
- [x] Reglas de indicadores y parámetros creadas
- [x] Fix CI DevExtreme
- [x] Tests adaptados al schema USERS
- [ ] CI pasa (verificar en GitHub Actions)
