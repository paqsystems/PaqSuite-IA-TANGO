# Épica 001 – Seguridad y Acceso

## Objetivo

Definir todas las historias de usuario relacionadas con:

1. **Acceso al sistema:** Login, selección de empresa, logout
2. **Mantenimiento de tablas:** Administración de usuarios, empresas, roles, permisos y menú

## Tablas involucradas (Dictionary DB)

| Tabla | Propósito |
|-------|-----------|
| `users` | Usuarios del sistema (autenticación) |
| `PQ_Empresa` | Empresas registradas |
| `Pq_Rol` | Roles disponibles |
| `Pq_Permiso` | Asignaciones usuario → empresa → rol |
| `PQ_RolAtributo` | Permisos por rol y opción de menú |
| `pq_menus` | Opciones de menú del sistema |

**Nota:** La tabla `users_identities` no se aplica por el momento (ver `docs/modelo-datos/md-diccionario/md-diccionario.md`).

## Historias por área

### Acceso (login + selección de empresa)

| HU | Título | Clasificación |
|----|--------|---------------|
| [HU-001](HU-001-login-usuario.md) | Login de usuario | MUST-HAVE |
| [HU-002](HU-002-seleccion-empresa.md) | Selección de empresa activa | MUST-HAVE |
| [HU-003](HU-003-logout.md) | Cerrar sesión | MUST-HAVE |
| [HU-004](HU-004-cambio-contraseña.md) | Cambio de contraseña | SHOULD-HAVE |
| [HU-005](HU-005-recuperacion-contraseña.md) | Recuperación de contraseña | SHOULD-HAVE |

### Mantenimiento de tablas

| HU | Título | Clasificación |
|----|--------|---------------|
| [HU-010](HU-010-administracion-usuarios.md) | Administración de usuarios | MUST-HAVE |
| [HU-011](HU-011-administracion-empresas.md) | Administración de empresas | MUST-HAVE |
| [HU-012](HU-012-administracion-roles.md) | Administración de roles | MUST-HAVE |
| [HU-013](HU-013-administracion-permisos.md) | Administración de permisos (asignaciones) | MUST-HAVE |
| [HU-014](HU-014-administracion-atributos-rol.md) | Administración de atributos de rol | SHOULD-HAVE |
| [HU-015](HU-015-menu-sistema.md) | Menú del sistema (seed versionado) | MUST-HAVE |

## Dependencias

```
HU-001 (Login) → HU-002 (Selección empresa) → HU-003 (Logout)
HU-001 → HU-010, HU-011, HU-012, HU-013 (mantenimiento, requiere admin)
```

## Referencias

- `docs/01-arquitectura/01-arquitectura-proyecto.md` – Modelo multiempresa
- `docs/00-contexto/00-contexto-global-erp.md` – Contexto ERP
- `docs/modelo-datos/md-diccionario/md-diccionario.md` – Esquema Dictionary DB
- `docs/ui/mockups/mockup-spec-mainlayout.md` – M03 CompanySwitcher, M01 UserMenu
