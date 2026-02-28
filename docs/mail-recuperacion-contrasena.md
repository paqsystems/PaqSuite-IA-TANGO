# Correo de recuperación de contraseña: no llega al usuario

## Causas habituales

1. **MAIL_MAILER=log** – En desarrollo, si no tienes SMTP configurado, Laravel puede estar usando el driver `log`. Los correos **no se envían**; se escriben en `storage/logs/laravel.log`. Revisa ese archivo para ver el contenido del mensaje.
2. **SMTP no configurado o incorrecto** – Si usas `MAIL_MAILER=smtp` pero no has definido `MAIL_HOST`, `MAIL_USERNAME`, `MAIL_PASSWORD` (o son erróneos), el envío falla. El controlador devuelve siempre 200 por seguridad; el error queda registrado en el **log** (desde esta corrección).
3. **Mailpit u otro catcher no está levantado** – Si en `.env` tienes `MAIL_HOST=mailpit` y `MAIL_PORT=1025`, el servidor debe tener Mailpit (u otro servidor SMTP) escuchando en ese puerto. Si no, la conexión falla.

---

## Qué hacer

### Opción A: Desarrollo sin SMTP (solo ver que el flujo funciona)

En el `.env` del **backend**:

```env
MAIL_MAILER=log
```

- Los correos **no se envían** por red.
- El contenido del correo (incluido el enlace de restablecimiento) se escribe en **`backend/storage/logs/laravel.log`**.
- Útil para comprobar que el usuario existe, tiene email y que se genera el enlace correcto.

Después de solicitar recuperación, abre `storage/logs/laravel.log` y busca el mensaje con el enlace `reset-password?token=...`. Puedes copiar esa URL y abrirla en el navegador para probar el restablecimiento.

### Opción B: Desarrollo con Mailpit (ver el correo en una interfaz)

1. Levanta Mailpit (por ejemplo con Docker):

   ```bash
   docker run -d -p 8025:8025 -p 1025:1025 --name mailpit axllent/mailpit
   ```

2. En el `.env` del backend:

   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=mailpit
   MAIL_PORT=1025
   MAIL_USERNAME=null
   MAIL_PASSWORD=null
   MAIL_ENCRYPTION=null
   MAIL_FROM_ADDRESS="noreply@tudominio.local"
   MAIL_FROM_NAME="${APP_NAME}"
   ```

3. Abre en el navegador: **http://localhost:8025**  
   Ahí verás todos los correos que Laravel “envía” (capturados por Mailpit).

### Opción C: Producción (envío real)

Configura un servidor SMTP real en `.env`:

- **Gmail (solo pruebas):** [Contraseña de aplicación](https://support.google.com/accounts/answer/185833), luego `MAIL_MAILER=smtp`, `MAIL_HOST=smtp.gmail.com`, `MAIL_PORT=587`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION=tls`.
- **SendGrid, Mailgun, Amazon SES, etc.:** Usa las variables que indique el proveedor.

Además, en producción define:

```env
FRONTEND_URL=https://tu-dominio-frontend.com
```

Así el enlace del correo apunta a tu frontend real.

---

## Si sigue sin llegar

1. **Revisa el log del backend** (`storage/logs/laravel.log`) después de solicitar recuperación. Si el envío falla, desde la última mejora verás una línea tipo:
   - `Recuperación de contraseña: fallo al enviar correo.` con el mensaje de error (ej. conexión rechazada, credenciales incorrectas).
2. **Comprueba que el usuario tenga email** – El correo solo se envía si el usuario existe **y** tiene email en `PQ_PARTES_USUARIOS` o `PQ_PARTES_CLIENTES`. Si buscas por código y ese usuario no tiene email, no se envía nada (y la API sigue respondiendo 200 por seguridad).
3. **Carpeta de spam** – En entornos reales, revisa la bandeja de spam del destinatario.

---

## Referencias

- TR-004(SH): `docs/04-tareas/TR-004(SH)-recuperación-de-contraseña.md`
- Variables de correo en backend: `backend/.env.example`
