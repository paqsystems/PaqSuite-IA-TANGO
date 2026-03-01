<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PqEmpresaSeeder extends Seeder
{
    public function run(): void
    {
        $usePqSchema = Schema::hasColumn('pq_empresa', 'IDEmpresa');
        $nombreCol = $usePqSchema ? 'NombreEmpresa' : 'nombre_empresa';
        $nombreBdCol = $usePqSchema ? 'NombreBD' : 'nombre_bd';
        $habilitaCol = $usePqSchema ? 'Habilita' : 'habilita';

        $exists = DB::table('pq_empresa')->where($nombreCol, 'Empresa Desarrollo')->exists();
        if (!$exists) {
            $data = [
                $nombreCol => 'Empresa Desarrollo',
                $nombreBdCol => config('database.connections.mysql.database', env('DB_DATABASE', 'empresa_desarrollo')),
                $habilitaCol => 1,
                'imagen' => null,
                'theme' => 'default',
            ];
            if (!$usePqSchema && Schema::hasColumn('pq_empresa', 'created_at')) {
                $data['created_at'] = now();
                $data['updated_at'] = now();
            }
            DB::table('pq_empresa')->insert($data);
        }
    }
}
