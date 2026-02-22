# PLAYBOOK BACKEND — Laravel (PHP) + Sanctum — Sistema de Partes

## Objetivo
Reglas internas de implementación BackEnd para cumplir el contrato de APIs y los requisitos de seguridad.

## Stack tecnológico nativo

**Herramientas obligatorias (ecosistema Laravel):**
- **Eloquent ORM** para modelos y acceso a datos
- **Laravel Migrations (Schema Builder)** para definición de esquema
- **Seeders y Factories** para datos de prueba
- **Laravel Sanctum** para autenticación
- **Query Builder** para consultas complejas

**NO usar:**
- PSL/Prisma (optimizado para Node.js/TypeScript, no para Laravel)
- Herramientas externas cuando Laravel ofrece solución nativa

**Ventajas del stack nativo:**
- Integración total con el ecosistema Laravel
- IntelliSense y herramientas del IDE
- Migraciones versionadas y controladas
- Seeders/Factories integrados para desarrollo y testing
- Soporte completo para SQL Server, PostgreSQL, MySQL

---

## Arquitectura propuesta
- Controllers: delgados (sin lógica).
- FormRequests: validación de entrada.
- Services: casos de uso / lógica.
- Policies/Gates: autorización.
- Resources/DTOs: salida controlada.
- Handler global: errores normalizados.

---

## Modelos y Migrations

### Migrations (Schema Builder)
- Definir esquema usando Laravel Migrations con Schema Builder.
- Cada cambio de estructura genera una migration nueva y versionada.
- Migrations deben ser reversibles (método `down()`).
- Ver detalles en `/.cursor/rules/09-data-access-orm-sql.md`.

### Modelos Eloquent
- Definir `$fillable` explícitamente.
- Usar `$casts` para tipos de datos.
- Definir relaciones como métodos públicos.
- Crear scopes para consultas reutilizables.
- No exponer modelos crudos: usar Resources/DTOs.

### Seeders y Factories
- Seeders para datos iniciales (catálogos, usuarios admin).
- Factories para datos de prueba en desarrollo/testing.
- Usar en tests y seeders de desarrollo.

---

## Autenticación (Sanctum)
- Proteger rutas con middleware de Sanctum.
- Definir endpoints públicos explícitos (login/health).
- Definir política de expiración/rotación y abilities (si tokens personales).

---

## Validación
- Ninguna escritura a BD sin Request validado.
- Mensajes claros en `respuesta`.
- Validación -> error 1000-1999 + HTTP 422.

---

## Documentación de Código (Obligatoria)

**Regla fundamental:** **TODAS las clases, métodos y propiedades deben documentarse** durante la codificación.

### Reglas Obligatorias

- ✅ **Todas las clases** (públicas, privadas, internas) deben tener PHPDoc
- ✅ **Todos los métodos** (públicos, privados, protegidos, estáticos) deben tener PHPDoc
- ✅ **Todas las propiedades** (públicas, privadas, protegidas, constantes) deben tener PHPDoc

### Formato PHPDoc

Usar PHPDoc estándar con:
- `@param` para parámetros de métodos
- `@return` para valores de retorno
- `@throws` para excepciones (si aplica)
- `@var` para propiedades

### Ejemplo

```php
/**
 * Servicio para gestionar el registro de tareas diarias.
 * 
 * Este servicio maneja la lógica de negocio relacionada con la creación,
 * actualización y consulta de registros de tareas.
 */
class RegistroTareaService
{
    /**
     * ID del usuario autenticado que realiza la operación.
     * 
     * @var int
     */
    private int $usuarioId;

    /**
     * Crea un nuevo registro de tarea para el usuario autenticado.
     * 
     * @param array $datos Datos del registro de tarea
     * @return RegistroTarea El registro de tarea creado
     * @throws ValidationException Si los datos no son válidos
     */
    public function crearRegistroTarea(array $datos): RegistroTarea
    {
        // Implementación...
    }
}
```

**Referencia completa:** Ver `specs/governance/code-documentation-rules.md` para reglas detalladas y más ejemplos.

---

## Documentación de API (Swagger/OpenAPI)

### Instalación y Configuración
- **Paquete:** `darkaonline/l5-swagger` (L5-Swagger para Laravel)
- **Configuración:** Archivo `config/l5-swagger.php`
- **Generación:** Comando `php artisan l5-swagger:generate`

### Configuración Mínima Requerida
```php
// config/l5-swagger.php (ejemplo de configuración clave)
'defaults' => [
    'api' => [
        'title' => 'Sistema de Registro de Tareas API',
        'version' => '1.0.0',
    ],
    'routes' => [
        'api' => 'api/documentation',
    ],
    'paths' => [
        'use_absolute_path' => env('L5_SWAGGER_USE_ABSOLUTE_PATH', false),
        'docs' => storage_path('api-docs'),
    ],
],
'security' => [
    'bearerAuth' => [
        'type' => 'http',
        'scheme' => 'bearer',
        'bearerFormat' => 'JWT',
    ],
],
```

### Anotaciones en Controladores
- Usar anotaciones PHPDoc con estándar OpenAPI
- Documentar cada endpoint con: `@OA\Get`, `@OA\Post`, `@OA\Put`, `@OA\Delete`
- Incluir: parámetros, request body, responses, security requirements

### Schemas Reutilizables
- Definir schemas comunes en un archivo separado o en traits
- Schemas obligatorios:
  - `ApiResponse` (envelope estándar)
  - `ApiErrorResponse` (formato de error)
  - `PaginatedResponse` (respuestas paginadas)

### Sincronización con Especificaciones
- Los archivos en `specs/endpoints/*.md` deben reflejar la misma información que la especificación OpenAPI
- Al modificar un endpoint, actualizar tanto la especificación OpenAPI como el archivo en `specs/endpoints/`
- Usar la especificación OpenAPI como fuente de verdad para el contrato

### Integración con CI/CD
- Generar documentación OpenAPI en cada build
- Validar que la especificación sea válida (OpenAPI lint)
- Publicar la especificación generada en el artefacto de build

---

## Seguridad
Ver `/.cursor/rules/08-security-sessions-tokens.md`.

---

## ORM/SQL complejo
Ver `/.cursor/rules/09-data-access-orm-sql.md`.

**Regla importante:** NO usar subqueries en WHERE para verificar existencia. Usar LEFT JOIN + verificación de NULL en su lugar.

---

## Configuración de conectividad (credenciales encriptadas)
- Archivo externo por instalación (json/ini/xml).
- Credenciales encriptadas (AES-256).
- Clave fuera del repo.
- Nunca loguear credenciales.

---
