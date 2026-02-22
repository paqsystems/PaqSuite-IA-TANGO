<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder: ClienteSeeder
 * 
 * Crea los clientes mínimos en la tabla PQ_PARTES_CLIENTES para testing.
 * 
 * Clientes creados:
 * - Cliente Demo (con acceso al sistema, vinculado a CLI001 en USERS)
 * - Cliente Sin Acceso (sin acceso al sistema, sin user_id)
 * 
 * @see TR-00(MH)-Generacion-base-datos-inicial.md
 */
class ClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener IDs dinámicamente
        $cli001UserId = DB::table('USERS')->where('code', 'CLI001')->value('id');
        $corpTipoId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->where('code', 'CORP')->value('id');
        $pymeTipoId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->where('code', 'PYME')->value('id');

        $clientes = [
            ['user_id' => $cli001UserId, 'nombre' => 'Cliente Demo S.A.', 'tipo_cliente_id' => $corpTipoId, 'code' => 'CLI001', 'email' => 'cliente@demo.com'],
            ['user_id' => null, 'nombre' => 'Empresa PyME Ejemplo', 'tipo_cliente_id' => $pymeTipoId, 'code' => 'CLI002', 'email' => 'contacto@pyme-ejemplo.com'],
        ];

        foreach ($clientes as $cliente) {
            if ($cliente['tipo_cliente_id']) {
                $exists = DB::table('PQ_PARTES_CLIENTES')
                    ->where('code', $cliente['code'])
                    ->exists();
                
                if (!$exists) {
                    DB::table('PQ_PARTES_CLIENTES')->insert([
                        'user_id' => $cliente['user_id'],
                        'nombre' => $cliente['nombre'],
                        'tipo_cliente_id' => $cliente['tipo_cliente_id'],
                        'code' => $cliente['code'],
                        'email' => $cliente['email'],
                        'activo' => true,
                        'inhabilitado' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
