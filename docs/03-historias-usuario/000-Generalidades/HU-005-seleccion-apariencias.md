# HU-005 – Selección de apariencia (look & feel) por empresa

## Épica
000 – Generalidades

## Clasificación
SHOULD-HAVE

## Rol
Administrador de empresa / Usuario con permiso de configuración

## Narrativa

Como administrador o usuario con permiso de configuración quiero elegir la apariencia (look & feel) del sistema para cada empresa, utilizando los temas predefinidos de DevExtreme, para que cada empresa pueda tener una identidad visual diferenciada según su preferencia.

## Contexto / Objetivo

Cada empresa (tenant) puede configurar una apariencia distinta para el sistema. La selección se realiza por empresa y se aplica al cargar el contexto (tras elegir o cambiar de empresa). Se utiliza una lista cerrada de temas precompilados de DevExtreme para garantizar consistencia y mantenibilidad.

## Prerrequisito obligatorio (bloqueo)

**Las tareas de esta HU NO deben ejecutarse hasta que la HU-011 (Administración de empresas) esté completada.**

La HU-011 implementa el CRUD de empresas, la pantalla de administración y los endpoints de empresas. Esta HU extiende esa funcionalidad agregando el campo `Theme` y el selector de apariencia. Sin la HU-011 terminada, no existe formulario de edición de empresa ni API de empresas donde integrar el tema.

## Suposiciones explícitas

- El frontend usa DevExtreme React (ya integrado).
- Existe modelo multiempresa con `PQ_Empresa` y contexto de empresa activa (`X-Company-Id`).
- La especificación en `docs/01-arquitectura/ui/01_MainLayout_PostLogin_Specification.md` define la estrategia "Theming por Empresa (Opción A)".
- **HU-011 completada:** pantalla de administración de empresas operativa, endpoints GET/PUT de empresa implementados.

## In scope

- Lista cerrada de temas DevExtreme (Generic, Material, Fluent).
- Persistencia de la apariencia por empresa en Dictionary DB.
- Selector de apariencia en administración de empresa o en configuración de empresa.
- Carga dinámica del CSS del tema al seleccionar/cambiar empresa.
- Fallback a tema por defecto si no hay configuración o el tema no existe.

## Out of scope

- ThemeBuilder o temas personalizados (colores/logo por empresa).
- Cambio de apariencia en tiempo real sin recargar contexto de empresa.
- Temas por usuario (la apariencia es por empresa).

---

## Criterios de aceptación

### AC1 – Lista de temas disponibles

- Se ofrece una lista cerrada de temas DevExtreme predefinidos.
- Incluye al menos: Generic (light, dark), Material (blue, teal, purple, orange en light/dark), Fluent (blue, saas en light/dark).
- Cada tema tiene un nombre legible para el selector (ej. "Material Blue Claro", "Generic Oscuro").

### AC2 – Configuración por empresa

- El administrador (o usuario con permiso) puede asignar un tema a cada empresa.
- La configuración se guarda en backend y se aplica cuando cualquier usuario de esa empresa accede al sistema.
- Si una empresa no tiene tema configurado, se usa el tema por defecto (ej. `generic.light` o `material.blue.light`).

### AC3 – Aplicación del tema

- Al seleccionar o cambiar de empresa, el sistema carga el tema configurado para esa empresa.
- El tema se aplica dinámicamente (carga del CSS correspondiente) sin redeploy.
- No debe producirse "flash" de estilos: el tema se carga antes de montar el layout principal.

### AC4 – Ubicación del selector

- El selector de apariencia está disponible en la administración de empresas (edición de empresa) o en un área de configuración de empresa.
- Solo usuarios con permiso de configuración pueden cambiar la apariencia.

### AC5 – Persistencia

- La preferencia se persiste en el campo `Theme` de la tabla `PQ_Empresa`.
- La preferencia se mantiene ante nuevo acceso y al cambiar de empresa.

### AC6 – Escenarios Gherkin

```gherkin
Scenario: Administrador asigna tema a empresa
  Given soy administrador con permiso de configuración
  And estoy editando la empresa "Acme Corp"
  When selecciono el tema "Material Blue Claro" en el selector de apariencia
  And guardo la configuración
  Then la apariencia se persiste para esa empresa
  And cualquier usuario de Acme Corp verá ese tema al acceder

Scenario: Usuario cambia de empresa y ve distinto tema
  Given estoy autenticado con empresa "Acme" activa (tema Material Blue)
  When cambio la empresa activa a "Beta Inc" (tema Generic Oscuro)
  Then la interfaz se actualiza al tema Generic Oscuro
  And no hay flash de estilos previos

Scenario: Empresa sin tema configurado usa fallback
  Given la empresa "Nueva SA" no tiene tema configurado
  When un usuario de Nueva SA accede al sistema
  Then se aplica el tema por defecto (generic.light o material.blue.light)
```

---

## Reglas de negocio

1. La apariencia es por empresa, no por usuario.
2. Solo se permiten temas de la lista cerrada (no se aceptan valores arbitrarios).
3. Solo usuarios con permiso de configuración de empresa pueden cambiar la apariencia.
4. Si el tema configurado no existe o falla al cargar, se usa el tema por defecto.
5. El tema se aplica al contexto de empresa activa; al cambiar de empresa, se aplica el tema de la nueva empresa.

---

## Impacto en datos

### Tabla afectada

- **`PQ_Empresa`** (Dictionary DB): agregar columna `Theme` (varchar(50), nullable).
  - Valores: uno de la lista cerrada de temas.
  - NULL = usar tema por defecto (generic.light o material.blue.light).

### Lista cerrada de temas (Theme)

| ThemeName | Descripción legible |
|-----------|----------------------|
| `generic.light` | Generic Claro |
| `generic.dark` | Generic Oscuro |
| `generic.light.compact` | Generic Claro Compacto |
| `generic.dark.compact` | Generic Oscuro Compacto |
| `material.blue.light` | Material Blue Claro |
| `material.blue.dark` | Material Blue Oscuro |
| `material.teal.light` | Material Teal Claro |
| `material.teal.dark` | Material Teal Oscuro |
| `material.purple.light` | Material Purple Claro |
| `material.purple.dark` | Material Purple Oscuro |
| `material.orange.light` | Material Orange Claro |
| `material.orange.dark` | Material Orange Oscuro |
| `fluent.blue.light` | Fluent Blue Claro |
| `fluent.blue.dark` | Fluent Blue Oscuro |
| `fluent.saas.light` | Fluent SaaS Claro |
| `fluent.saas.dark` | Fluent SaaS Oscuro |

### Migración

```php
// Agregar columna Theme a PQ_Empresa
Schema::table('PQ_Empresa', function (Blueprint $table) {
    $table->string('Theme', 50)->nullable()->after('Habilita');
});
```

### Rollback

```php
Schema::table('PQ_Empresa', function (Blueprint $table) {
    $table->dropColumn('Theme');
});
```

---

## Contratos de API

El atributo `theme` se incluye en los endpoints existentes de administración de empresas (HU-011):

### GET /api/empresas/{id} (o equivalente)

**Response (200):** El payload de empresa incluye el campo `theme`:
```json
{
  "id": 1,
  "nombreEmpresa": "Acme Corp",
  "theme": "material.blue.light",
  ...
}
```

### PUT /api/empresas/{id} (o equivalente)

**Request:** El body puede incluir `theme`:
```json
{
  "nombreEmpresa": "Acme Corp",
  "theme": "material.blue.light",
  ...
}
```

**Validación:** `theme` debe estar en la lista cerrada o ser null.

**Códigos:** 400 (theme inválido), 401, 403, 404, 422.

**Autorización:** Usuario con permiso de administración de empresas.

---

## Cambios Frontend

### Componentes afectados

- **ThemeLoader / ThemeProvider:** Carga dinámica del CSS según `themeName` de la empresa activa.
- **AppLayout / MainLayout:** Integrar ThemeLoader; aplicar tema antes de montar contenido.
- **Administración de empresas:** Agregar selector de apariencia en formulario de edición.
- **main.tsx:** Ajustar carga inicial de tema (o cargar tema por defecto hasta resolver empresa).

### Estrategia de carga

1. Usuario autenticado con empresa activa → el payload de empresa incluye `theme` (o se obtiene al listar empresas).
2. Reemplazar `<link id="dx-theme-link" href="...">` en `<head>` con el CSS del tema seleccionado.
3. Rutas de archivos: `devextreme/dist/css/dx.{theme}.css` (ej. `dx.material.blue.light.css`).

### data-testid

- `appearance.selector` – selector de apariencia.
- `appearance.selector.option.{themeName}` – opción de tema.

### Accesibilidad

- `aria-label` con `t("appearance.selector.label", "Apariencia")`.

---

## Plan de tareas / Tickets

> **Bloqueo:** Todas las tareas de esta HU requieren que la **HU-011 (Administración de empresas)** esté completada. No iniciar ninguna tarea hasta verificar que la HU-011 está cerrada.

| ID | Tipo | Descripción | DoD | Deps |
|----|------|-------------|-----|------|
| T1 | DB | Migración: agregar columna `Theme` a `PQ_Empresa` | Columna nullable creada, rollback definido | HU-011 |
| T2 | Backend | Incluir `theme` en payload de empresas (GET listado, GET detalle) | Frontend recibe tema al resolver contexto de empresa | HU-011, T1 |
| T3 | Backend | Incluir `theme` en PUT/PATCH de empresa; validación lista cerrada | Persistencia en PQ_Empresa.Theme | HU-011, T1 |
| T4 | Frontend | ThemeLoader: cargar CSS dinámicamente según theme de empresa activa | Reemplazar link en head, fallback a generic.light | HU-011 |
| T5 | Frontend | Integrar ThemeLoader en flujo de empresa activa | Tema se aplica al seleccionar/cambiar empresa, sin flash | HU-011, T4, T2 |
| T6 | Frontend | Selector de apariencia en formulario de edición de empresa | Dropdown con temas, se guarda con el resto del formulario | HU-011, T3 |
| T7 | Tests | Unit: validación theme, ThemeLoader fallback | Tests pasan | T4 |
| T8 | Tests | Integration: CRUD empresa con theme, validación lista cerrada | Tests pasan | T2, T3 |
| T9 | Tests | E2E: cambiar tema en admin empresas, verificar aplicación al cambiar empresa | Playwright cubre flujo | T5, T6 |
| T10 | Docs | Actualizar 01_MainLayout_PostLogin_Specification.md con implementación | Documentación alineada | T5 |

---

## Estrategia de tests

### Unit

- Validación de `theme`: solo acepta valores de la lista cerrada o null.
- ThemeLoader: si theme inválido o null, usa generic.light.

### Integration

- GET empresa: response incluye theme (o null).
- PUT empresa: 200 con theme válido, 422 con theme inválido.

### E2E (Playwright)

- Login → ir a administración empresas → editar empresa → seleccionar tema → guardar.
- Cambiar empresa activa → verificar que la UI refleja el tema de la nueva empresa.

---

## Riesgos y edge cases

- **Tema no encontrado:** Fallback a generic.light, log de advertencia.
- **Cambio de empresa sin recarga:** El ThemeLoader debe re-ejecutarse al cambiar empresa activa.
- **Primera carga (login):** Hasta tener empresa activa, usar tema por defecto.

---

## Dependencias

- **HU-011 (Administración de empresas)** – **BLOQUEANTE.** Esta HU no debe ejecutarse hasta que la HU-011 esté completada. Proporciona el CRUD de empresas, la pantalla de administración y los endpoints donde se integra el campo `theme`.
- HU-002 (Cambio de empresa activa) – contexto de empresa para aplicar el tema al cambiar.
- DevExtreme con temas precompilados (dx.light.css, dx.material.blue.light.css, etc.).

---

## Referencias

- `docs/01-arquitectura/ui/01_MainLayout_PostLogin_Specification.md` – Theming por Empresa (Opción A)
- `docs/03-historias-usuario/001-Seguridad/HU-011-administracion-empresas.md` – Administración de empresas
- `docs/03-historias-usuario/000-Generalidades/HU-002-cambio-empresa-activa.md` – Cambio de empresa
- [DevExtreme Predefined Themes](https://js.devexpress.com/jQuery/Documentation/Guide/Themes_and_Styles/Predefined_Themes/)
- `.cursor/rules/24-devextreme-grid-standards.md` – Estándar de grillas
