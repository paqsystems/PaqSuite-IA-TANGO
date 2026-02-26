# HU-004 – Cambio de contraseña

## Épica
001 – Seguridad y Acceso

## Clasificación
SHOULD-HAVE

## Rol
Usuario autenticado

## Narrativa

Como usuario autenticado quiero cambiar mi contraseña para mantener la seguridad de mi cuenta, especialmente cuando el sistema me lo solicita en el primer login.

## Criterios de aceptación

- El usuario puede acceder al cambio de contraseña desde el menú de usuario (Cambiar contraseña).
- Se abre un Popup/Modal con formulario: contraseña actual, contraseña nueva, confirmación.
- El sistema valida que la contraseña actual sea correcta.
- El sistema valida que la contraseña nueva cumpla políticas (longitud mínima, complejidad si se define).
- El sistema valida que contraseña nueva y confirmación coincidan.
- Si `users.first_login = true`, el usuario debe cambiar la contraseña antes de acceder al resto del sistema.
- Tras cambiar correctamente, se actualiza `users.password_hash` y `users.first_login = false`.
- Se muestra mensaje de éxito y se cierra el popup.
- En caso de error (contraseña actual incorrecta), se muestra mensaje claro.

## Reglas de negocio

- Solo el usuario autenticado puede cambiar su propia contraseña.
- La contraseña se almacena hasheada (bcrypt o equivalente).

## Dependencias

- HU-001 (Login)
- Menú de usuario (M05_ChangePassword_Popup)

## Referencias

- `docs/ui/mockups/mockup-spec-mainlayout.md` – M05_ChangePassword_Popup
