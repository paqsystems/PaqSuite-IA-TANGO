# Infraestructura y Despliegue – MVP

> **Nota:** Docker y CI/CD están preparados pero **desactivados**. Los workflows de GitHub Actions se encuentran en `docs/futuro/workflows/`. Para habilitarlos, ver `docs/futuro/DOCKER-CICD.md`.

## Objetivo
Disponer de una URL pública accesible para evaluación del MVP.

---

## Versión de la aplicación

### Fuente de verdad

- **Archivo único:** `VERSION` en la raíz del proyecto (ej: `1.1.0`).
- No duplicar la versión en `package.json` ni `composer.json` como fuente principal.
- Ver `.cursor/rules/23-versioning-and-deploy.md` para criterios de bump (MAJOR/MINOR/PATCH).

### Inyección en el build del frontend

El build de Vite **lee automáticamente** el archivo `VERSION` y lo inyecta en la aplicación:

- **Proceso:** `frontend/vite.config.ts` lee `VERSION` en la raíz y define `import.meta.env.VITE_APP_VERSION`.
- **Uso:** La versión se muestra en el footer del shell (ej: `v1.1.0`).
- **No requiere configurar** `VITE_APP_VERSION` en `.env`; el build lo toma del archivo `VERSION`.

**Comandos que usan la versión:** `npm run build` y `npm run dev` en `frontend/`.

---

## Base de Datos y Migraciones

### Requisitos
- **Motor:** MySQL 5.7+ o MariaDB 10.3+ (recomendado MySQL 8.0+)
- **Base de datos:** Configurada según entorno (ej: `_datosempresa`)
- **Driver Laravel:** `mysql` (extensión PHP PDO MySQL)
- **Túnel SSH:** Requerido para conexión remota (ver `docs/migracion-mssql-a-mysql.md`)

### Configuración de Conexión (.env)

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=_datosempresa
DB_USERNAME=forge
DB_PASSWORD=tu_password
```

**Nota:** El `DB_HOST=127.0.0.1` indica que se usa un túnel SSH local. El túnel debe estar activo antes de ejecutar migraciones o acceder a la base de datos. Ver instrucciones en `docs/migracion-mssql-a-mysql.md`.

### Estructura de Tablas

| Tabla | Descripción |
|-------|-------------|
| `USERS` | Autenticación centralizada (base DICCIONARIO, no en bases de empresas) |
| `PQ_PARTES_USUARIOS` | Empleados que cargan tareas |
| `PQ_PARTES_CLIENTES` | Clientes para los cuales se registran tareas |
| `PQ_PARTES_TIPOS_CLIENTE` | Catálogo de tipos de cliente |
| `PQ_PARTES_TIPOS_TAREA` | Catálogo de tipos de tarea |
| `PQ_PARTES_REGISTRO_TAREA` | Registros de tareas (tabla principal) |
| `PQ_PARTES_CLIENTE_TIPO_TAREA` | Asociación N:M Cliente-TipoTarea |

### Comandos de Migración

```bash
# Ejecutar migraciones (crear tablas)
php artisan migrate

# Ejecutar seeders (datos iniciales)
php artisan db:seed

# Recrear BD completa desde cero con datos
php artisan migrate:fresh --seed

# Rollback de todas las migraciones
php artisan migrate:reset

# Ver estado de migraciones
php artisan migrate:status
```

### Orden de Migraciones

Las migraciones se ejecutan en el siguiente orden (por dependencias):

1. `create_users_table` - Tabla USERS
2. `create_tipos_cliente_table` - PQ_PARTES_TIPOS_CLIENTE
3. `create_tipos_tarea_table` - PQ_PARTES_TIPOS_TAREA
4. `create_usuarios_table` - PQ_PARTES_USUARIOS (depende de USERS)
5. `create_clientes_table` - PQ_PARTES_CLIENTES (depende de USERS, TIPOS_CLIENTE)
6. `create_registro_tarea_table` - PQ_PARTES_REGISTRO_TAREA
7. `create_cliente_tipo_tarea_table` - PQ_PARTES_CLIENTE_TIPO_TAREA

### Datos de Seed

Los seeders crean los siguientes datos mínimos para testing:

**Usuarios de autenticación (USERS):**
| Code | Password | Descripción |
|------|----------|-------------|
| ADMIN | admin123 | Usuario supervisor |
| CLI001 | cliente123 | Usuario cliente |
| EMP001 | empleado123 | Usuario empleado |

**Empleados (PQ_PARTES_USUARIOS):**
| Code | Nombre | Supervisor |
|------|--------|------------|
| ADMIN | Administrador del Sistema | Sí |
| EMP001 | Empleado Demo | No |

**Tipos de Cliente (PQ_PARTES_TIPOS_CLIENTE):**
| Code | Descripción |
|------|-------------|
| CORP | Corporativo |
| PYME | Pequeña y Mediana Empresa |

**Tipos de Tarea (PQ_PARTES_TIPOS_TAREA):**
| Code | Descripción | Genérico | Default |
|------|-------------|----------|---------|
| GENERAL | Tarea General | Sí | Sí |
| SOPORTE | Soporte Técnico | Sí | No |
| DESARROLLO | Desarrollo de Software | No | No |

**Clientes (PQ_PARTES_CLIENTES):**
| Code | Nombre | Tipo | Con acceso |
|------|--------|------|------------|
| CLI001 | Cliente Demo S.A. | CORP | Sí |
| CLI002 | Empresa PyME Ejemplo | PYME | No |

### Troubleshooting

**Error: Driver MySQL no encontrado**
```bash
# Linux (Ubuntu/Debian)
sudo apt-get install php-mysql

# Linux (CentOS/RHEL)
sudo yum install php-mysql

# Mac (Homebrew)
brew install php mysql

# Windows
# Habilitar extensión en php.ini:
# extension=pdo_mysql
# extension=mysqli
```

**Error: Conexión rechazada**
- Verificar que el túnel SSH está activo y configurado correctamente
- Verificar que MySQL permite conexiones remotas (si aplica)
- Verificar firewall (puerto 3306)
- Verificar credenciales en `.env`
- Verificar que la base de datos existe en el servidor MySQL

**Error: Túnel SSH no disponible**
- Establecer túnel SSH antes de ejecutar migraciones
- Ver instrucciones detalladas en `docs/migracion-mssql-a-mysql.md`

**Error: Timeout en migraciones**
- Verificar conexión de red
- Aumentar timeout en `config/database.php` si es necesario
- Verificar que el túnel SSH sigue activo

---

## Entorno
- Un solo entorno productivo.
- Base de datos administrada por el proveedor.

---

## CI/CD (básico)

Pipeline con **GitHub Actions**. Actualmente **desactivado**; los workflows están en `docs/futuro/workflows/`. Ver `docs/futuro/DOCKER-CICD.md` para habilitar.

### Disparadores (cuando estén habilitados)
- **CI** (`.github/workflows/ci.yml`): Se ejecuta en `push` y `pull_request` a `main`, además de manual (`workflow_dispatch`).
- **CD** (`.github/workflows/cd.yml`): Build y push de imágenes Docker a GitHub Container Registry en `push` a `main`.

### Jobs del pipeline

| Job      | Descripción                                                  |
|----------|---------------------------------------------------------------|
| backend  | Tests Laravel (PHPUnit) con MySQL 8.0 como servicio            |
| frontend | Tests unitarios (Vitest) + build (Vite)                       |
| swagger  | Generación de documentación OpenAPI (`php artisan l5-swagger:generate`) |
| e2e      | Tests E2E (Playwright, Chromium) con backend y frontend      |

### Orden de ejecución
1. **backend** y **frontend** en paralelo
2. **swagger** tras backend
3. **e2e** tras backend y frontend (inicia backend con `php artisan serve`, Playwright inicia el frontend)

### Requisitos para CI
- **PHP:** 8.2 (shivammathur/setup-php)
- **Node.js:** 20
- **MySQL:** 8.0 (servicio de contenedor)
- **Composer** y **npm** con cache

### Ejecución local del pipeline
No es necesario instalar nada extra: GitHub Actions usa runners con PHP, Node, MySQL, etc. Solo asegúrate de que los tests pasen localmente:
```bash
# Backend
cd backend && php artisan test

# Frontend (unit + E2E)
cd frontend && npm run test:all
```

### Deploy automático (CD)
- Las imágenes Docker se publican en GitHub Container Registry (ghcr.io) al hacer push a `main`.
- Backend: `ghcr.io/<owner>/paqsuite-backend`
- Frontend: `ghcr.io/<owner>/paqsuite-frontend`
- Para desplegar: `docker pull` y ejecutar, o usar `docker/docker-compose.yml` con variables de entorno para BD externa.

---

## Gestión de secretos
- Variables de entorno:
  - DATABASE_URL
  - JWT_SECRET
- Uso de .env.example
- Nunca versionar secretos reales

---

## Frontend en Vercel
- **vercel.json:** `frontend/vercel.json` con rewrites para SPA (todas las rutas → index.html)
- Sin esto, URLs como `/login` devuelven 404 al abrirlas directamente

## Docker (Backend + Frontend, BD externa)

Estructura en `docker/`:

- `docker-compose.yml` – Backend (Laravel) y Frontend (nginx). Sin servicio db.
- Variables: `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` apuntan a BD externa (RDS, PlanetScale, etc.).

```bash
cd docker
cp .env.example .env
# Editar .env con credenciales de BD externa
docker compose up -d
```

- Backend: `http://localhost:8000`
- Frontend: `http://localhost:80`

Dockerfiles: `backend/Dockerfile`, `frontend/Dockerfile`.

## Despliegue
Opciones válidas:
- **Docker:** backend + frontend en contenedores; BD externa
- Backend: Render / Fly.io / Railway
- Frontend: Vercel / Netlify
- DB: MySQL/MariaDB administrado (ej: AWS RDS, DigitalOcean, PlanetScale)

---

## Acceso
- URL pública documentada.
- Usuario de prueba disponible para el evaluador.
