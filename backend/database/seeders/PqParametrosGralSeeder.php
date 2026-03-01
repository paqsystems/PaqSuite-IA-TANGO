<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seed de parámetros generales (Company DB).
 * Ejemplo para módulo PartesProduccion.
 *
 * @see docs/04-tareas/000-Generalidades/TR-007-Parametros-generales.md
 */
class PqParametrosGralSeeder extends Seeder
{
    public function run(): void
    {
        $connection = 'company';

        if (! \Illuminate\Support\Facades\Schema::connection($connection)->hasTable('PQ_PARAMETROS_GRAL')) {
            $this->command->warn('Tabla PQ_PARAMETROS_GRAL no existe. Ejecute: php artisan migrate --database=company');

            return;
        }

        $params = [
            ['PartesProduccion', 'duracion_minima_minutos', 'I', null, null, 15, null, null, null],
            ['PartesProduccion', 'duracion_maxima_horas', 'N', null, null, null, null, null, 24.0],
            ['PartesProduccion', 'descripcion_obligatoria', 'B', null, null, null, null, 1, null],
        ];

        foreach ($params as $row) {
            $exists = DB::connection($connection)
                ->table('PQ_PARAMETROS_GRAL')
                ->where('Programa', $row[0])
                ->where('Clave', $row[1])
                ->exists();

            if (!$exists) {
                DB::connection($connection)->table('PQ_PARAMETROS_GRAL')->insert([
                    'Programa' => $row[0],
                    'Clave' => $row[1],
                    'tipo_valor' => $row[2],
                    'Valor_String' => $row[3],
                    'Valor_Text' => $row[4],
                    'Valor_Int' => $row[5],
                    'Valor_DateTime' => $row[6],
                    'Valor_Bool' => $row[7],
                    'Valor_Decimal' => $row[8],
                ]);
            }
        }
    }
}
