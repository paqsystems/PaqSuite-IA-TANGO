<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PqRolSeeder extends Seeder
{
    public function run(): void
    {
        $exists = DB::table('pq_rol')->where('nombre_rol', 'Supervisor')->exists();
        if (!$exists) {
            DB::table('pq_rol')->insert([
                'nombre_rol' => 'Supervisor',
                'descripcion_rol' => 'Rol con acceso total al sistema',
                'acceso_total' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
