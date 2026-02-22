<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder: TestTasksSeeder
 * 
 * Crea datos de prueba específicos para tests de carga de tareas (TR-028).
 * Extiende los datos existentes de TestUsersSeeder agregando:
 * - Cliente adicional activo (CLI002)
 * - Tipo de tarea NO genérico (ESPECIAL)
 * - Asignación ClienteTipoTarea (CLI001 → ESPECIAL)
 * 
 * Datos requeridos (algunos ya existen en otros seeders):
 * - 2 clientes activos: CLI001 (existe), CLI002 (crear)
 * - 1 cliente inactivo: CLIINACTIVO (existe)
 * - Tipos genéricos: SOPORTE (existe), DESARROLLO (existe pero NO genérico)
 * - Tipo NO genérico: ESPECIAL (crear)
 * - Asignación: CLI001 → ESPECIAL (crear)
 * - Empleados: JPEREZ, MGARCIA (existen)
 * 
 * @see TR-028(MH)-carga-de-tarea-diaria.md
 */
class TestTasksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener tipo de cliente para crear CLI002
        $tipoClienteId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->where('code', 'CORP')->value('id');
        if (!$tipoClienteId) {
            $tipoClienteId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->first()?->id;
        }

        // Crear cliente CLI002 (activo) si no existe
        $cli002Exists = DB::table('PQ_PARTES_CLIENTES')
            ->where('code', 'CLI002')
            ->exists();

        if (!$cli002Exists && $tipoClienteId) {
            // Verificar si existe el usuario CLI002 en USERS
            $cli002UserId = DB::table('USERS')->where('code', 'CLI002')->value('id');
            
            if (!$cli002UserId) {
                // Crear usuario CLI002 en USERS si no existe
                DB::table('USERS')->insert([
                    'code' => 'CLI002',
                    'password_hash' => bcrypt('cliente002'),
                    'activo' => true,
                    'inhabilitado' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $cli002UserId = DB::table('USERS')->where('code', 'CLI002')->value('id');
            }

            // Crear cliente CLI002
            DB::table('PQ_PARTES_CLIENTES')->insert([
                'user_id' => $cli002UserId,
                'code' => 'CLI002',
                'nombre' => 'Corporación XYZ',
                'email' => 'contacto@corporacionxyz.com',
                'tipo_cliente_id' => $tipoClienteId,
                'activo' => true,
                'inhabilitado' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Crear tipo de tarea ESPECIAL (NO genérico) si no existe
        $especialExists = DB::table('PQ_PARTES_TIPOS_TAREA')
            ->where('code', 'ESPECIAL')
            ->exists();

        if (!$especialExists) {
            DB::table('PQ_PARTES_TIPOS_TAREA')->insert([
                'code' => 'ESPECIAL',
                'descripcion' => 'Tarea Especial para Cliente',
                'is_generico' => false,
                'is_default' => false,
                'activo' => true,
                'inhabilitado' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Crear asignación ClienteTipoTarea (CLI001 → ESPECIAL) si no existe
        $cli001Id = DB::table('PQ_PARTES_CLIENTES')->where('code', 'CLI001')->value('id');
        $especialId = DB::table('PQ_PARTES_TIPOS_TAREA')->where('code', 'ESPECIAL')->value('id');

        if ($cli001Id && $especialId) {
            $asignacionExists = DB::table('PQ_PARTES_CLIENTE_TIPO_TAREA')
                ->where('cliente_id', $cli001Id)
                ->where('tipo_tarea_id', $especialId)
                ->exists();

            if (!$asignacionExists) {
                DB::table('PQ_PARTES_CLIENTE_TIPO_TAREA')->insert([
                    'cliente_id' => $cli001Id,
                    'tipo_tarea_id' => $especialId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Asegurar que DESARROLLO sea genérico para los tests (si existe)
        $desarrolloId = DB::table('PQ_PARTES_TIPOS_TAREA')->where('code', 'DESARROLLO')->value('id');
        if ($desarrolloId) {
            // Actualizar DESARROLLO para que sea genérico (necesario para tests)
            DB::table('PQ_PARTES_TIPOS_TAREA')
                ->where('id', $desarrolloId)
                ->update([
                    'is_generico' => true,
                    'updated_at' => now(),
                ]);
        }
    }
}
