<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder: TipoClienteSeeder
 * 
 * Crea los tipos de cliente mínimos para testing.
 * 
 * @see TR-00(MH)-Generacion-base-datos-inicial.md
 */
class TipoClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiposCliente = [
            ['code' => 'CORP', 'descripcion' => 'Corporativo'],
            ['code' => 'PYME', 'descripcion' => 'Pequeña y Mediana Empresa'],
        ];

        foreach ($tiposCliente as $tipo) {
            $exists = DB::table('PQ_PARTES_TIPOS_CLIENTE')
                ->where('code', $tipo['code'])
                ->exists();
            
            if (!$exists) {
                DB::table('PQ_PARTES_TIPOS_CLIENTE')->insert([
                    'code' => $tipo['code'],
                    'descripcion' => $tipo['descripcion'],
                    'activo' => true,
                    'inhabilitado' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
