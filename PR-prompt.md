# Pull Request – Selector de idioma en pantalla de login (HU-004)

## Título sugerido

```
feat(auth): selector de idioma en pantalla de login (HU-004)
```

## Descripción

Implementación del criterio de aceptación de HU-004: el selector de idioma debe estar disponible en la pantalla de login (antes de autenticarse), además de permanecer en el layout principal tras el login.

## Cambios incluidos

### 1. Selector de idioma en login

- **`LoginForm`** o layout de pantallas públicas: añadir `LanguageSelector` visible en la pantalla de login.
- Ubicación sugerida: esquina superior derecha o junto al formulario, según diseño PaqSystems.
- El selector debe usar los mismos idiomas soportados (es, en) y aplicar el cambio de inmediato vía `i18n.changeLanguage()`.

### 2. Persistencia para usuarios no autenticados

- Según HU-004: "Usuarios no autenticados (en login): la selección se guarda temporalmente (ej. localStorage) y al autenticarse se envía al backend para persistir en `users.locale`."
- Si el backend ya expone endpoint para actualizar `locale`, enviar la preferencia tras login exitoso.
- Si no existe aún, guardar en `localStorage` y documentar como pendiente la sincronización con backend.

### 3. Estilos del selector en login

- El selector en la pantalla de login tiene fondo claro (a diferencia del header oscuro post-login).
- Ajustar estilos en `LoginForm.css` o crear clase `.language-selector--login` para que sea legible sobre el fondo del formulario.

## Referencias

- `docs/03-historias-usuario/000-Generalidades/HU-004-seleccion-idioma.md`
- `docs/design/paqsystems-main-shell-design.md` – Sección 4.1
- `frontend/src/shared/components/LanguageSelector.tsx`
- `frontend/src/app/AppLayout.css` – estilos actuales de `.language-selector`

## Checklist

- [ ] LanguageSelector visible en pantalla de login
- [ ] Selector funcional (cambio de idioma sin recargar)
- [ ] Estilos adecuados para fondo claro
- [ ] Persistencia en localStorage (o envío a backend si aplica)
- [ ] Tests E2E actualizados si el selector afecta flujo de login
