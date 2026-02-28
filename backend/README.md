# Backend – Laravel + Sanctum

## Sistema de Partes de Atención

Backend del **MVP de registro de tareas** para consultorías y empresas de servicios. Permite que empleados registren tareas diarias (fecha, cliente, tipo de tarea, duración) y se generen informes de dedicación.

---

## Stack Tecnológico

- **Framework:** Laravel (PHP)
- **Autenticación:** Laravel Sanctum (Bearer Token)
- **ORM:** Eloquent
- **Base de Datos:** MySQL / SQL Server / PostgreSQL
- **API:** REST, Base URL `/api/v1`
- **Testing:** PHPUnit

---

## Estructura Principal

```
backend/
├── app/
│   ├── Models/           # Modelos Eloquent
│   ├── Http/
│   │   ├── Controllers/   # Controladores API
│   │   ├── Requests/     # Form Requests (validación)
│   │   └── Middleware/    # Middleware
│   └── Services/          # Lógica de negocio (si aplica)
├── database/
│   ├── migrations/       # Migraciones de BD
│   └── seeders/           # Seeders
├── routes/
│   └── api.php           # Rutas API
└── tests/
    ├── Unit/             # Tests unitarios
    └── Feature/          # Tests de integración
```

---

## Modelos Implementados

| Modelo | Tabla | Descripción |
|--------|-------|-------------|
| **User** | `USERS` | Autenticación central (base DICCIONARIO, no en bases de empresas) |
| **PqMenu** | `PQ_MENUS` | Menús del sistema |

> **Nota:** El login se valida contra la tabla `USERS` por `code` y `password_hash`.

---

## Configuración Inicial

```bash
cd backend
composer install
cp .env.example .env
# Configurar en .env: DB_CONNECTION, DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD
php artisan key:generate
php artisan migrate
php artisan db:seed    # Opcional: datos iniciales
php artisan serve
```

---

## Ejecutar Tests

```bash
cd backend
php artisan test                    # Todos los tests
php artisan test --filter Unit      # Solo unitarios
php artisan test --filter Feature   # Solo integración
```

---

## Referencias Clave

| Documento | Propósito |
|-----------|-----------|
| **`docs/backend/PLAYBOOK_BACKEND_LARAVEL.md`** | Guía de desarrollo, convenciones, PHPDoc obligatorio |
| **`docs/api/CONTRATO_BASE.md`** | Formato de respuestas API |
| **`specs/contracts/response-envelope.md`** | Envelope estándar de respuestas |
| **`specs/endpoints/`** | Especificaciones detalladas de cada endpoint |
| **`specs/models/`** | Especificaciones de modelos y relaciones |
| **`docs/modelo-datos.md`** | Modelo de datos completo |

---

## Convenciones

- **Tablas:** `USERS` (auth), `PQ_MENUS` (menús), `personal_access_tokens` (Sanctum)
- **Formato de respuesta:** Envelope estándar (`error`, `respuesta`, `resultado`)
- **Validación:** Form Requests en todas las escrituras
- **Documentación:** PHPDoc obligatorio en clases, métodos y propiedades (ver `specs/governance/code-documentation-rules.md`)
