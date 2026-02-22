<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * DatabaseSeeder Principal
 * 
 * Ejecuta todos los seeders en el orden correcto (respetando dependencias).
 * 
 * Orden de ejecución:
 * 1. TipoClienteSeeder - Catálogo de tipos de cliente
 * 2. TipoTareaSeeder - Catálogo de tipos de tarea
 * 3. UserSeeder - Usuarios de autenticación (tabla USERS)
 * 4. UsuarioSeeder - Empleados (tabla PQ_PARTES_USUARIOS)
 * 5. ClienteSeeder - Clientes (tabla PQ_PARTES_CLIENTES)
 * 
 * Uso:
 *   php artisan db:seed
 *   php artisan migrate:fresh --seed
 * 
 * @see TR-00(MH)-Generacion-base-datos-inicial.md
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            TipoClienteSeeder::class,
            TipoTareaSeeder::class,
            UserSeeder::class,
            UsuarioSeeder::class,
            ClienteSeeder::class,
        ]);
    }
}
