<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder: TipoTareaSeeder
 * 
 * Crea los tipos de tarea mínimos para testing.
 * Incluye un tipo de tarea genérico con is_default=true.
 * 
 * Regla de negocio: Solo puede existir un TipoTarea con is_default=true.
 * Regla de negocio: Si is_default=true, entonces is_generico=true.
 * 
 * @see TR-00(MH)-Generacion-base-datos-inicial.md
 */
class TipoTareaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiposTarea = [
            ['code' => 'GENERAL', 'descripcion' => 'Tarea General', 'is_generico' => true, 'is_default' => true],
            ['code' => 'SOPORTE', 'descripcion' => 'Soporte Técnico', 'is_generico' => true, 'is_default' => false],
            ['code' => 'DESARROLLO', 'descripcion' => 'Desarrollo de Software', 'is_generico' => false, 'is_default' => false],
        ];

        foreach ($tiposTarea as $tipo) {
            $exists = DB::table('PQ_PARTES_TIPOS_TAREA')
                ->where('code', $tipo['code'])
                ->exists();
            
            if (!$exists) {
                DB::table('PQ_PARTES_TIPOS_TAREA')->insert([
                    'code' => $tipo['code'],
                    'descripcion' => $tipo['descripcion'],
                    'is_generico' => $tipo['is_generico'],
                    'is_default' => $tipo['is_default'],
                    'activo' => true,
                    'inhabilitado' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
