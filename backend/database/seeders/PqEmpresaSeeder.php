<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PqEmpresaSeeder extends Seeder
{
    public function run(): void
    {
        $exists = DB::table('pq_empresa')->where('nombre_empresa', 'Empresa Desarrollo')->exists();
        if (!$exists) {
            DB::table('pq_empresa')->insert([
                'nombre_empresa' => 'Empresa Desarrollo',
                'nombre_bd' => config('database.connections.mysql.database', env('DB_DATABASE', 'empresa_desarrollo')),
                'habilita' => 1,
                'imagen' => null,
                'theme' => 'default',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
