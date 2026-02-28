# PaqSystems UI – Figma

Este directorio documenta el diseño visual del **Shell principal post-login** (Main Layout) y sus variantes responsive.

## Archivo Figma (fuente de verdad visual)
- Nombre del archivo: **“PaqSystems UI – Main Layout”**
- Páginas:
  1) `01-Shell`
  2) `02-Menus-Modals`
  3) `03-Responsive`
  4) `99-Theme-Snapshots` (opcional pero recomendado)

> Guardar el link del archivo Figma aquí (pegar URL):
- Figma: <PEGAR_LINK_AQUI>

---

## Convenciones de Nomenclatura (Frames)

### Desktop
- `D01_MainLayout_ExpandedSidebar`
- `D02_MainLayout_CollapsedSidebar`
- `D03_MainLayout_TabsMode` (solo desktop/tablet)

### Menús y Modales
- `M01_UserMenu_Dropdown`
- `M02_LanguageSwitcher`
- `M03_CompanySwitcher_Popup`
- `M04_Profile_Popup`
- `M05_ChangePassword_Popup`

### Mobile (sin dashboards, sin solapas)
- `MB01_Home_QuickAccess`
- `MB02_Drawer_Open`
- `MB03_UserMenu`
- `MB04_CompanySwitcher_Popup`

### Tablet
- `T01_MainLayout_Default`
- `T02_MainLayout_TabsMode`

---

## Tamaños de Frames (Figma)

- Desktop: `1440 x 900`
- Tablet: `834 x 1112` (iPad portrait)
- Mobile: `390 x 844` (iPhone 13/14)

---

## Exportación y versionado en el repo

Exportar los frames principales a PNG y guardarlos en:

- `/docs/ui/mockups/`

Convención de nombres de export:
- `D01_MainLayout_ExpandedSidebar.png`
- `D02_MainLayout_CollapsedSidebar.png`
- `MB01_Home_QuickAccess.png`
- etc.

---

## Nota sobre Themes DevExtreme (por empresa)

Si se utiliza lista cerrada de themes (Opción A), se recomienda mantener una página en Figma:

- `99-Theme-Snapshots`

Contiene capturas rápidas del mismo frame (D01) con distintos themes, para validar contraste y legibilidad.