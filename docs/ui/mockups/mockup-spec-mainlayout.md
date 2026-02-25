# Mockup Spec – Main Layout (Post Login)

Este documento describe los frames (Figma) y cómo implementarlos con DevExtreme React Components.

## Tokens básicos (referencia)
- TopBar height: 60px
- Footer height: 32px
- Sidebar width expanded: 260px
- Sidebar width collapsed: 72px
- Spacing base: 8px (usar múltiplos)
- Card radius: 12px (o el que provea el theme)
- Layout max content: fluid (100%)

---

# DESKTOP

## Frame: D01_MainLayout_ExpandedSidebar (1440 x 900)

### 1) TopBar (60px, sticky)
**DevExtreme**
- `Toolbar` (dxToolbar)

**Estructura (izq -> der):**
- `ToolbarItem` (left): Hamburguesa (icon)
  - Acción: toggle Drawer (expanded/collapsed)
- `ToolbarItem` (left): Logo PaqSystems (click -> home)
- `ToolbarItem` (center): Empresa Activa (texto truncable)
- `ToolbarItem` (right): Language flags (grupo de botones)
  - Implementación: `ButtonGroup` o botones dentro del Toolbar
- `ToolbarItem` (right): User icon / avatar
  - Acción: abre `DropDownButton` o `Menu`

### 2) Sidebar (260px)
**DevExtreme**
- `Drawer` (dxDrawer)
  - Panel: Sidebar
  - Content: Main content area

**Dentro del Sidebar**
- `ScrollView` (dxScrollView)
- Menú procesos:
  - preferido: `TreeView` (dxTreeView) para módulos + subprocesos
  - alternativo: `List` (dxList) si no hay jerarquía

**Regla visual**
- Ícono + texto
- Módulo activo resaltado por theme

### 3) Centro – Dashboard Container (contenido fluido)
**DevExtreme**
- Layout: CSS Grid o `ResponsiveBox` (dxResponsiveBox)
- Cards: `Box`/div + estilos theme (o usar templates DevExtreme)

**Grid sugerida (desktop)**
- Fila 1: 4 KPIs (cada uno ~ 1/4 del ancho)
- Fila 2: 2 cards grandes (1/2 y 1/2)
- Fila 3: 1 card ancho completo (alertas/pedientes)

**Widgets (ejemplos)**
- KPI: `Chart` opcional mini, o texto grande + delta
- Gráfico: `Chart` (dxChart)
- Listado: `DataGrid` simple (dxDataGrid) o `List`

> Nota: este dashboard NO existe en mobile (ver sección Mobile).

### 4) Footer (32px, sticky)
**DevExtreme**
- `Toolbar` o layout simple (div)
Contenido:
- Izquierda: Usuario + Rol
- Derecha: Versión + Ambiente

---

## Frame: D02_MainLayout_CollapsedSidebar (1440 x 900)

Igual a D01 pero:
- Sidebar width: 72px
- Menú muestra solo íconos
- Tooltip al hover (opcional)
**DevExtreme**
- `Drawer` con `minSize` y template del panel
- `TreeView` / `List` en modo compacto

---

## Frame: D03_MainLayout_TabsMode (1440 x 900)

Solo desktop/tablet.
- El centro contiene:
  - `TabPanel` (dxTabPanel)
    - Tab 0: Home (dashboard)
    - Tabs siguientes: procesos abiertos
- Modo solapas se habilita por preferencia de usuario (NO mobile).

---

# MENÚS / MODALES

## Frame: M01_UserMenu_Dropdown
**DevExtreme**
- `DropDownButton` o `Menu` vinculado a ícono usuario.

Items:
- Perfil del Usuario -> abre `Popup` M04
- Cambiar Empresa -> abre `Popup` M03
- Cambiar contraseña -> abre `Popup` M05
- Toggle (desktop/tablet): “Abrir procesos en solapas”
  - control: `Switch`
- Cerrar sesión

---

## Frame: M03_CompanySwitcher_Popup
**DevExtreme**
- `Popup` (dxPopup)
Contenido:
- `DataGrid` (si hay muchas empresas) o `List`
- Search: `TextBox`
Acción:
- seleccionar empresa -> set company context -> recargar permisos + theme

Medidas sugeridas:
- Desktop: 720 x 480
- Tablet: 620 x 520
- Mobile: fullscreen modal

---

## Frame: M04_Profile_Popup
**DevExtreme**
- `Popup` + `Form` (dxForm)
Campos típicos:
- Nombre, Email (read-only si aplica)
- Idioma preferido
- Preferencias UI (si se habilita)
Acciones:
- Guardar / Cancelar

---

## Frame: M05_ChangePassword_Popup
**DevExtreme**
- `Popup` + `Form`
Campos:
- Password actual
- Password nuevo
- Confirmación
Validaciones:
- fuerza y coincidencia
Acciones:
- Guardar / Cancelar

---

# TABLET

## Frame: T01_MainLayout_Default (834 x 1112)

Reglas:
- TopBar igual.
- Sidebar puede iniciar colapsado (72px) para maximizar contenido.
- Dashboard en 1–2 columnas.

**DevExtreme**
- `Drawer` (modo “shrink” o “overlap” según preferencia)
- `ResponsiveBox` o CSS grid con breakpoint.

## Frame: T02_MainLayout_TabsMode
- `TabPanel` habilitado.
- Tabs scroll horizontal.

---

# MOBILE (SIN DASHBOARD, SIN SOLAPAS)

## Frame: MB01_Home_QuickAccess (390 x 844)

Objetivo:
- Pantalla inicial orientada a ejecución rápida de procesos.

Contenido recomendado:
1) “Procesos frecuentes” (lista)
2) “Últimos procesos” (lista)
3) (Opcional futuro) búsqueda de procesos

**DevExtreme**
- TopBar: `Toolbar`
- Menú: `Drawer` overlay (cerrado por defecto)
- Listas: `List` (dxList)
- Botones: `Button`

Reglas:
- NO dashboard.
- NO TabPanel.
- No mostrar widgets analíticos.

---

## Frame: MB02_Drawer_Open
**DevExtreme**
- `Drawer` mode = `overlap`
- Drawer ocupa 85–90% del ancho
- cierra al seleccionar ítem

---

## Frame: MB03_UserMenu
**DevExtreme**
- `DropDownButton` o `ActionSheet`
  - (ActionSheet si querés sensación mobile nativa)

Items:
- Perfil
- Cambiar Empresa
- Cambiar contraseña
- Cerrar sesión

---

## Frame: MB04_CompanySwitcher_Popup
**DevExtreme**
- `Popup` fullscreen
- Lista con búsqueda

---

# Validaciones de usabilidad (Checklist)
- Contraste correcto en todos los themes.
- Truncado de nombre de empresa en TopBar.
- Sidebar items accesibles con teclado (desktop).
- Drawer mobile fácil de cerrar (swipe/backdrop).
