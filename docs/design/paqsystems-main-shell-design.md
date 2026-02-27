# PaqSystems UI – Main Shell (Diseño Base)

**Fuente:** [Figma – PaqSystems UI Main Shell](https://www.figma.com/design/xI76ckb7zistdVbSDYR9Aa/PaqSystems-UI-%E2%80%93-Main-Shell?node-id=0-1)

Este documento define el diseño base del proyecto, adaptado a DevExtreme.

---

## 1. Estructura general

```
┌─────────────────────────────────────────────────────────────────┐
│ HEADER (fondo oscuro)                                             │
│ [☰] PaqSystems [logo] EMPRESA DEMO S.A.  │ 🔔 ⚙️ 👤 [Español ▼] SU │
├──────────────┬────────────────────────────────────────────────────┤
│ SIDEBAR      │ MAIN CONTENT                                        │
│ (fondo claro)│                                                     │
│              │ Título / Subtítulo                                  │
│ • Ventas     │ KPIs / Grillas / Contenido                         │
│ • Stock      │                                                     │
│ • ...        │                                                     │
├──────────────┴────────────────────────────────────────────────────┤
│ FOOTER (fondo oscuro)                                             │
│ A SUPERVISOR                                                      │
└──────────────────────────────────────────────────────────────────┘
```

---

## 2. Paleta de colores

| Uso | Variable CSS | Valor | Descripción |
|-----|--------------|-------|-------------|
| Header/Footer bg | `--paq-header-bg` | `#1e293b` | Azul oscuro |
| Header/Footer text | `--paq-header-text` | `#ffffff` | Blanco |
| Sidebar bg | `--paq-sidebar-bg` | `#f8fafc` | Gris muy claro |
| Content bg | `--paq-content-bg` | `#ffffff` | Blanco |
| Texto principal | `--paq-text-primary` | `#1e293b` | Gris oscuro |
| Texto secundario | `--paq-text-secondary` | `#64748b` | Gris medio |
| Link activo | `--paq-accent` | `#0ea5e9` | Azul (compatible DevExtreme) |
| Éxito (KPI +) | `--paq-success` | `#22c55e` | Verde |
| Error (KPI -) | `--paq-error` | `#ef4444` | Rojo |

---

## 3. Variables dinámicas del shell

| Variable | Origen | Ejemplo |
|---------|--------|---------|
| **Empresa** | `sessionContext.getEmpresa()` / localStorage `session_empresa_nombre` | "Empresa Demo" |
| **Nombre usuario** | `user.nombre` (AuthUser) | "Juan Pérez" |
| **Versión app** | `appVersion` (inyectada en build desde `VERSION`) | "1.1.0" |

La empresa se actualizará con HU-002 (cambio de empresa activa). La versión se lee automáticamente del archivo `VERSION` en el build (ver `docs/deploy-ci-cd.md`).

---

## 4. Header

- **Fondo:** `--paq-header-bg`
- **Texto:** blanco
- **Izquierda:** hamburger, logo + "PaqSystems", nombre de empresa (variable)
- **Derecha:** selector de idioma (control dedicado), nombre usuario (variable), avatar con menú desplegable
- **Altura:** ~56px

### 4.1 Selector de idioma

- **Ubicación:** En el header, como control dedicado (dropdown o grupo de botones según diseño).
- **No forma parte** del menú de usuario; es un control independiente para cambiar el idioma de la aplicación.
- Ver HU-004 (Selección de idioma).

### 4.2 Menú de usuario (debajo del avatar)

Al hacer clic en el avatar se abre un menú desplegable con:

| Ítem | Acción | HU |
|------|--------|-----|
| Perfil del usuario | Abre pantalla o popup de perfil | — |
| Cambiar empresa | Abre selector de empresa activa | HU-002 |
| Cambiar contraseña | Abre modal de cambio de contraseña | HU-004 (Seguridad) |
| Abrir en otra pestaña | Toggle de preferencia (misma/nueva pestaña) | HU-003 |
| Cerrar sesión | Cierra la sesión | HU-003 (Seguridad) |

Referencia: `docs/ui/mockups/mockup-spec-mainlayout.md` – M01_UserMenu_Dropdown

---

## 5. Sidebar

- **Fondo:** `--paq-sidebar-bg`
- **Ancho:** 260px
- **Items:** icono + texto, item activo con fondo ligeramente más oscuro
- **Expandible:** secciones con submenú (flecha)

---

## 6. Main Content

- **Fondo:** `--paq-content-bg`
- **Título:** negrita, tamaño grande
- **Subtítulo:** gris, tamaño menor
- **KPIs:** cards con valor, variación %, icono ↑/↓
- **Grillas:** DevExtreme DataGrid según estándar 24

---

## 7. Footer

- **Fondo:** `--paq-header-bg`
- **Izquierda:** rol del usuario (variable, ej. "A SUPERVISOR")
- **Derecha:** versión de la aplicación (variable, ej. "v1.1.0")
- **Altura:** ~32px

---

## 8. Integración con DevExtreme

- Usar tema `dx.light.css` como base
- Sobrescribir variables CSS para header/sidebar/footer
- Componentes DevExtreme (DataGrid, Chart, etc.) mantienen su apariencia
- El shell (header, sidebar, footer) es custom; el contenido usa DevExtreme

---

## 9. Logo

- **Placeholder:** cuadrado con iniciales o icono genérico
- **Definitivo:** se reemplazará cuando se provea el logotipo de PaqSystems
