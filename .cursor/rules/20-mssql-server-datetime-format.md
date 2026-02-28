# Regla: Formato de Fechas en SQL Server

## Contexto

Cuando el proyecto utiliza **SQL Server** como base de datos, considerar que SQL Server tiene particularidades en el manejo de fechas que pueden causar errores si no se manejan correctamente.

## Problema

SQL Server tiene dos tipos de fecha principales:
- `datetime` - Tipo legacy con formato más restrictivo
- `datetime2` - Tipo moderno con mejor compatibilidad

Laravel por defecto puede tener problemas con ambos tipos, produciendo el error:

```
La conversión del tipo de datos nvarchar en datetime produjo un valor fuera de intervalo
```

## IMPORTANTE: Usar datetime2 en migraciones

Para evitar problemas de compatibilidad, **siempre usar `datetime2`** en lugar de `datetime` para columnas de fecha/hora:

```php
// ❌ NO usar timestamp() - crea datetime
$table->timestamp('created_at');

// ❌ NO usar dateTime() - también crea datetime  
$table->dateTime('created_at');

// ✅ Usar SQL directo para datetime2
DB::statement("ALTER TABLE mi_tabla ADD created_at DATETIME2 NULL");
```

## Regla Obligatoria

### 1. Configuración de Base de Datos

En `config/database.php`, la conexión `sqlsrv` **DEBE** incluir:

```php
'sqlsrv' => [
    // ... otras configuraciones ...
    'date_format' => 'Y-m-d H:i:s',
],
```

### 2. Modelos Eloquent

Todos los modelos que usen timestamps **DEBEN** incluir:

```php
/**
 * Formato de fecha para SQL Server
 */
protected $dateFormat = 'Y-m-d H:i:s';
```

### 3. Inserciones con DB::table()

Al usar `DB::table()` para insertar registros, **SIEMPRE** usar `DB::raw('GETDATE()')` para timestamps:

```php
// ✅ CORRECTO
DB::table('MI_TABLA')->insert([
    'campo' => 'valor',
    'created_at' => DB::raw('GETDATE()'),
    'updated_at' => DB::raw('GETDATE()'),
]);

// ❌ INCORRECTO - Causa error en SQL Server
DB::table('MI_TABLA')->insert([
    'campo' => 'valor',
    'created_at' => now(),
    'updated_at' => now(),
]);
```

### 4. Seeders

Los seeders **DEBEN** usar `DB::raw('GETDATE()')` para timestamps:

```php
DB::table('USERS')->insert([
    'code' => 'ADMIN',
    'created_at' => DB::raw('GETDATE()'),
    'updated_at' => DB::raw('GETDATE()'),
]);
```

### 5. Tests

Los tests que insertan datos directamente **DEBEN** seguir la misma regla:

```php
// En setUp() o en el test
DB::table('USERS')->insert([
    'code' => 'TEST_USER',
    'created_at' => DB::raw('GETDATE()'),
    'updated_at' => DB::raw('GETDATE()'),
]);
```

## Formato Estándar

El formato de fecha estándar para este proyecto es:

- **Formato PHP/Laravel:** `Y-m-d H:i:s`
- **Formato SQL Server:** `YYYY-MM-DD HH:MM:SS`
- **Función SQL Server:** `GETDATE()` para obtener fecha/hora actual

## Migraciones

En las migraciones, usar los tipos de columna estándar de Laravel:

```php
$table->timestamps(); // Genera created_at y updated_at
$table->date('fecha'); // Solo fecha
$table->dateTime('fecha_hora'); // Fecha y hora
```

## Consultas con Fechas

Al filtrar por fechas en consultas:

```php
// ✅ CORRECTO
->whereDate('fecha', '2026-01-28')
->where('fecha', '>=', '2026-01-01')

// ✅ También válido con Carbon
->whereDate('fecha', Carbon::today())
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
| DB::table() insert | `DB::raw('GETDATE()')` |
| Seeders | `DB::raw('GETDATE()')` |
| Tests | `DB::raw('GETDATE()')` |
| config/database.php | `'date_format' => 'Y-m-d H:i:s'` |

---

## Impacto

- **Archivos afectados:** Todos los modelos, seeders, tests y código que interactúe con fechas
- **Migraciones:** No requieren cambios (usan tipos estándar de Laravel)
- **Frontend:** No afectado (las fechas se serializan correctamente en JSON)

## Referencias

- [TR-001(MH) - Login de Empleado](../../docs/04-tareas/TR-001(MH)-login-de-empleado.md)
- [Laravel Database Configuration](https://laravel.com/docs/10.x/database)
- [SQL Server GETDATE()](https://docs.microsoft.com/en-us/sql/t-sql/functions/getdate-transact-sql)

## Historial

| Fecha     | Cambio              |
|-----------|---------------------|
| 2026-01-27 | Creación de la regla |

**Origen:** Error encontrado durante implementación de TR-001(MH) (Laravel genera timestamps con milisegundos que SQL Server no acepta).
