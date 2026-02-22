# Regla: Formato de Fechas en MySQL

## Contexto

cuando se diseñe el proyecto con **MySQL** como base de datos, considerar que MySQL maneja fechas de forma diferente a SQL Server y requiere un enfoque específico.

## Problema

MySQL tiene tipos de fecha principales:
- `DATETIME` - Almacena fecha y hora (rango: 1000-01-01 00:00:00 a 9999-12-31 23:59:59)
- `TIMESTAMP` - Similar a DATETIME pero con zona horaria y rango más limitado (1970-2038)
- `DATE` - Solo fecha

Laravel por defecto funciona bien con MySQL usando los tipos estándar del Schema Builder.

## IMPORTANTE: Usar Schema Builder de Laravel

Para evitar problemas de compatibilidad, **siempre usar Schema Builder de Laravel** en lugar de SQL directo:

```php
// ✅ CORRECTO - Usar Schema Builder
Schema::create('mi_tabla', function (Blueprint $table) {
    $table->timestamps(); // Genera created_at y updated_at como TIMESTAMP
    $table->dateTime('fecha_hora'); // Genera DATETIME
    $table->date('fecha'); // Genera DATE
});

// ❌ NO usar SQL directo a menos que sea absolutamente necesario
DB::statement("ALTER TABLE mi_tabla ADD created_at DATETIME NULL");
```

## Regla Obligatoria

### 1. Configuración de Base de Datos

En `config/database.php`, la conexión `mysql` **DEBE** incluir:

```php
'mysql' => [
    // ... otras configuraciones ...
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    // date_format no es necesario para MySQL, Laravel lo maneja automáticamente
],
```

### 2. Modelos Eloquent

Todos los modelos que usen timestamps **DEBEN** incluir:

```php
/**
 * Formato de fecha para MySQL
 */
protected $dateFormat = 'Y-m-d H:i:s';
```

**Nota:** Aunque MySQL acepta diferentes formatos, mantener `Y-m-d H:i:s` asegura consistencia y compatibilidad.

### 3. Inserciones con DB::table()

Al usar `DB::table()` para insertar registros, **SIEMPRE** usar `now()` de Laravel para timestamps:

```php
// ✅ CORRECTO - Usar now() de Laravel
DB::table('MI_TABLA')->insert([
    'campo' => 'valor',
    'created_at' => now(),
    'updated_at' => now(),
]);

// ✅ También válido con DB::raw('NOW()') si se necesita función SQL
DB::table('MI_TABLA')->insert([
    'campo' => 'valor',
    'created_at' => DB::raw('NOW()'),
    'updated_at' => DB::raw('NOW()'),
]);

// ❌ NO usar funciones específicas de otros motores (ej: GETDATE() de SQL Server)
```

### 4. Seeders

Los seeders **DEBEN** usar `now()` de Laravel para timestamps:

```php
DB::table('USERS')->insert([
    'code' => 'ADMIN',
    'created_at' => now(),
    'updated_at' => now(),
]);
```

### 5. Tests

Los tests que insertan datos directamente **DEBEN** seguir la misma regla:

```php
// En setUp() o en el test
DB::table('USERS')->insert([
    'code' => 'TEST_USER',
    'created_at' => now(),
    'updated_at' => now(),
]);
```

## Formato Estándar

El formato de fecha estándar para este proyecto es:

- **Formato PHP/Laravel:** `Y-m-d H:i:s`
- **Formato MySQL:** `YYYY-MM-DD HH:MM:SS`
- **Función MySQL:** `NOW()` para obtener fecha/hora actual (usar `now()` de Laravel en código)

## Migraciones

En las migraciones, usar los tipos de columna estándar de Laravel:

```php
$table->timestamps(); // Genera created_at y updated_at como TIMESTAMP
$table->date('fecha'); // Solo fecha (DATE)
$table->dateTime('fecha_hora'); // Fecha y hora (DATETIME)
$table->timestamp('fecha_exacta'); // TIMESTAMP con zona horaria
```

## Consultas con Fechas

Al filtrar por fechas en consultas:

```php
// ✅ CORRECTO
->whereDate('fecha', '2026-01-28')
->where('fecha', '>=', '2026-01-01')

// ✅ También válido con Carbon
->whereDate('fecha', Carbon::today())

// ✅ Usar funciones MySQL si es necesario
->whereRaw('DATE(fecha) = ?', ['2026-01-28'])
```

### 6. Laravel Sanctum (PersonalAccessToken)

Sanctum también necesita un modelo personalizado:

```php
// app/Models/PersonalAccessToken.php
class PersonalAccessToken extends SanctumPersonalAccessToken
{
    protected $dateFormat = 'Y-m-d H:i:s';
}

// app/Providers/AppServiceProvider.php
public function boot(): void
{
    Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
}
```

## Resumen

| Situación | Usar |
|-----------|------|
| Modelos Eloquent | `protected $dateFormat = 'Y-m-d H:i:s'` |
| Sanctum Tokens | Modelo personalizado con `$dateFormat` |
| DB::table() insert | `now()` (recomendado) o `DB::raw('NOW()')` |
| Seeders | `now()` |
| Tests | `now()` |
| Migraciones | Schema Builder estándar de Laravel |

---

## Diferencias con SQL Server

| Aspecto | SQL Server | MySQL |
|---------|------------|-------|
| Tipo principal | `DATETIME2` | `DATETIME` o `TIMESTAMP` |
| Función fecha actual | `GETDATE()` | `NOW()` |
| Manejo de NULLs en UNIQUE | Trata múltiples NULL como duplicados | Permite múltiples NULL |
| Schema Builder | Requiere cuidado con tipos | Funciona perfectamente |

**Migración desde SQL Server:** Si se migra desde SQL Server, reemplazar:
- `DB::raw('GETDATE()')` → `now()` o `DB::raw('NOW()')`
- SQL directo con `DATETIME2` → Schema Builder estándar
- Índices únicos con NULLs → No requieren lógica especial en MySQL

## Impacto

- **Archivos afectados:** Todos los modelos, seeders, tests y código que interactúe con fechas
- **Migraciones:** Usar Schema Builder estándar de Laravel (ya implementado)
- **Frontend:** No afectado (las fechas se serializan correctamente en JSON)

## Referencias

- [docs/migracion-mssql-a-mysql.md](../../docs/migracion-mssql-a-mysql.md) - Documentación completa de migración a MySQL
- [Laravel Database Configuration](https://laravel.com/docs/10.x/database)
- [MySQL Date and Time Functions](https://dev.mysql.com/doc/refman/8.0/en/date-and-time-functions.html)

## Historial

| Fecha     | Cambio              |
|-----------|---------------------|
| 2026-02-11 | Creación de la regla para MySQL (migración desde SQL Server) |

**Origen:** Migración del proyecto de SQL Server a MySQL. Regla actualizada para reflejar las mejores prácticas con MySQL.
