# 03 — Acceso a datos: ORM (Eloquent) + Queries complejas

## 0) Herramientas nativas de Laravel (obligatorio)

**NO usar herramientas externas como PSL/Prisma.**  
Para Laravel, se utiliza exclusivamente el ecosistema nativo:
- **Eloquent ORM** para modelos y relaciones
- **Laravel Migrations (Schema Builder)** para definición de esquema
- **Seeders y Factories** para datos de prueba
- **Query Builder** para consultas complejas

**Ventajas:**
- Integración total con el ecosistema Laravel
- IntelliSense y herramientas del IDE
- Migraciones versionadas y controladas
- Seeders/Factories integrados
- Soporte completo para SQL Server, PostgreSQL, MySQL

---

## 1) Migrations (Schema Builder)

### Definición de esquema
- Usar **Laravel Migrations** con Schema Builder (NO PSL/Prisma).
- Cada cambio de estructura debe generar una migration nueva.
- Migrations deben ser reversibles (método `down()`).

### Ejemplo de estructura:
```php
Schema::create('clientes', function (Blueprint $table) {
    $table->id();
    $table->string('nombre');
    $table->string('direccion')->nullable();
    $table->boolean('activo')->default(true);
    $table->timestamps();
    
    $table->index('activo');
});
```

### Convenciones:
- Nombres descriptivos: `create_clientes_table`, `add_activo_to_clientes_table`
- Versionar migrations en control de versiones
- No modificar migrations ya aplicadas en producción
- Usar `php artisan migrate:fresh` solo en desarrollo

---

## 2) Modelos Eloquent

### Estructura estándar:
```php
class Cliente extends Model {
    protected $fillable = ['nombre', 'direccion', 'activo'];
    protected $casts = ['activo' => 'boolean', 'created_at' => 'datetime'];
    
    // Relaciones
    public function tickets() {
        return $this->hasMany(Ticket::class);
    }
}
```

### Convenciones obligatorias:
- Definir `$fillable` explícitamente (no usar `$guarded` sin justificación).
- Usar `$casts` para tipos de datos (boolean, datetime, json).
- Definir relaciones como métodos públicos.
- No exponer modelos crudos: usar Resources/DTOs en APIs.

### Relaciones Eloquent:
- `hasMany`, `belongsTo`, `hasOne`, `belongsToMany` según corresponda.
- Usar eager loading (`with()`) para evitar N+1.
- Definir claves foráneas explícitamente si no siguen convenciones.

### Scopes:
- Crear scopes reutilizables para consultas comunes:
```php
public function scopeActivos($query) {
    return $query->where('activo', true);
}
```

## 3) Seeders y Factories

### Seeders:
- Usar para datos iniciales (catálogos, usuarios admin, etc.).
- Ejecutar con `php artisan db:seed`.
- Seeders deben ser idempotentes cuando sea posible.

### Factories:
- Usar para generar datos de prueba en desarrollo/testing.
- Definir factories para cada modelo principal.
- Usar en tests y seeders de desarrollo.

---

## 4) Convenciones de nombres físicos de base de datos

### Prefijo obligatorio de tablas

Todas las tablas físicas del sistema deben utilizar el prefijo obligatorio:
PQ_PARTES_

Esta convención aplica a:
- tablas principales
- tablas de historial
- tablas de catálogos
- tablas de relación

### Alcance de la convención

- El prefijo **NO** afecta:
  - nombres de entidades lógicas
  - nombres de clases
  - nombres de DTOs
  - nombres de endpoints
- El prefijo **SÍ** afecta:
  - nombres de tablas físicas
  - vistas
  - stored procedures (si se usan)
  - funciones relacionadas al dominio

### Objetivo

- Evitar colisiones con otras aplicaciones
- Facilitar mantenimiento, backup y auditoría
- Identificar claramente el dominio “Partes de Atención”
- Permitir convivencia con otros sistemas en la misma base

### Interpretación para Cursor

Cursor debe asumir que:
- El nombre lógico de una entidad **no coincide necesariamente** con el nombre físico de la tabla
- El prefijo `PQ_PARTES_` es **obligatorio**
- Cualquier mapeo ORM futuro debe respetar esta convención

---

## 5) ORM estándar
- CRUD y consultas medianas: Eloquent + Query Builder.
- No devolver modelos Eloquent crudos al cliente: usar Resources/DTOs.

---

## 6) Queries complejas
Cuando haya:
- reportes
- agregaciones pesadas
- joins múltiples con performance crítica
- CTE/window functions
Se permite:
- Query Builder con joins y subqueries (parametrizadas)
- Vistas
- Stored Procedures (si el motor lo soporta)
- SQL nativo parametrizado (DB::select con bindings)

---

## 7) Anti-patrones de consultas (prohibido)

### ❌ NO usar subqueries en WHERE para verificar existencia

**Prohibido:**
```sql
-- ❌ MAL: Subquery en WHERE
SELECT * FROM clientes 
WHERE code IN (SELECT cliente FROM partes);
```

**Obligatorio: usar LEFT JOIN + verificación de NULL**
```sql
-- ✅ BIEN: LEFT JOIN con verificación
SELECT clientes.*
FROM clientes
LEFT JOIN partes ON clientes.code = partes.cliente
WHERE partes.cliente IS NOT NULL;
```

**En Laravel Query Builder:**
```php
// ✅ BIEN: Usar leftJoin
Cliente::query()
    ->leftJoin('partes', 'clientes.code', '=', 'partes.cliente')
    ->whereNotNull('partes.cliente')
    ->select('clientes.*')
    ->get();
```

**Razones:**
- Mejor performance (el optimizador puede usar índices más eficientemente)
- Más legible y mantenible
- Evita problemas de escalabilidad con subqueries anidadas
- Compatible con eager loading de Eloquent

**Excepciones:**
- Subqueries en SELECT (para agregaciones calculadas) están permitidas
- Subqueries en FROM (CTEs, subconsultas como tablas derivadas) están permitidas
- Solo se prohíbe subqueries en WHERE para verificar existencia

---

## 8) Anti-SQL injection (obligatorio)
- Prohibido:
  - concatenar SQL con input
  - interpolar variables en `whereRaw` sin bindings
- Obligatorio:
  - bindings/params
  - whitelists para campos dinámicos (`sort`, `group_by`, etc.)
  - validar `page_size` max (DoS)

---

## 9) Performance básica
- Evitar N+1 (eager loading con `with()`, `load()`).
- Paginar listados.
- Índices en campos de búsqueda/filtros (definir en migrations).
- Auditoría: created_at/updated_at + tracking usuario donde aplique.

---

## Relación con otros documentos
- Complementa `docs/backend/PLAYBOOK_BACKEND_LARAVEL.md`
- Referenciado desde `.cursor/rules/05-backend-policy.md`
- Alineado con `docs/domain/DATA_MODEL.md`

## Uso
Este documento debe ser consultado por:
- Desarrolladores del backend para implementar acceso a datos
- Revisores de código para validar cumplimiento de reglas
- Arquitectos para definir estrategias de consultas

## Mantenimiento
- Actualizar cuando se identifiquen nuevos anti-patrones
- Mantener ejemplos actualizados con el código
- Documentar excepciones cuando sea necesario
