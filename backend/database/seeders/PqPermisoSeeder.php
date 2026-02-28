<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PqPermisoSeeder extends Seeder
{
    public function run(): void
    {
        $rol = DB::table('pq_rol')->where('nombre_rol', 'Supervisor')->first();
        $empresa = DB::table('pq_empresa')->where('nombre_empresa', 'Empresa Desarrollo')->first();

        if (!$rol || !$empresa) {
            return;
        }

        $usuarios = DB::table('USERS')->whereIn('code', ['ADMIN', 'EMP'])->get();

        foreach ($usuarios as $usuario) {
            $exists = DB::table('pq_permiso')
                ->where('id_rol', $rol->id)
                ->where('id_empresa', $empresa->id)
                ->where('id_usuario', $usuario->id)
                ->exists();

            if (!$exists) {
                DB::table('pq_permiso')->insert([
                    'id_rol' => $rol->id,
                    'id_empresa' => $empresa->id,
                    'id_usuario' => $usuario->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
