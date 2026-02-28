<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder: PqMenuSeeder
 *
 * Sincroniza la tabla pq_menus desde el JSON versionado (fuente de verdad).
 * Idempotente: ejecutar múltiples veces no duplica registros.
 * Agnóstico de BD: funciona con MySQL y SQL Server.
 *
 * Fuente: docs/backend/seed/PQ_MENUS/PQ_MENUS.seed.v2.json
 *
 * @see docs/03-historias-usuario/Historia_PQ_MENUS_seed.md
 * @see docs/03-historias-usuario/001-Seguridad/HU-015-menu-sistema.md
 */
class PqMenuSeeder extends Seeder
{
    protected string $jsonPath;

    public function __construct()
    {
        $this->jsonPath = dirname(base_path()) . '/docs/backend/seed/PQ_MENUS/PQ_MENUS.seed.v2.json';
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!file_exists($this->jsonPath)) {
            $this->command->warn("Archivo seed no encontrado: {$this->jsonPath}");
            return;
        }

        $json = file_get_contents($this->jsonPath);
        $items = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($items)) {
            throw new \RuntimeException('Error al parsear PQ_MENUS.seed.v2.json: ' . json_last_error_msg());
        }

        foreach ($items as $item) {
            $this->upsertItem($item);
        }

        $this->command->info('PqMenuSeeder: ' . count($items) . ' registros sincronizados.');
    }

    protected function upsertItem(array $item): void
    {
        $id = (int) $item['id'];
        $text = $item['text'] ?? '';
        $expanded = (bool) ($item['expanded'] ?? false);
        $idparent = (int) ($item['parent'] ?? 0);
        $orden = (int) ($item['order'] ?? 0);
        $procedimiento = $item['procedimiento'] ?? null;
        $enabled = (bool) ($item['enabled'] ?? false);
        $routeName = $item['routeName'] ?? null;

        $tipo = $item['tipo'] ?? ($procedimiento ? 'ABM' : 'INF');

        $data = [
            'text' => $text,
            'expanded' => $expanded,
            'idparent' => $idparent,
            'orden' => $orden,
            'tipo' => $tipo,
            'procedimiento' => $procedimiento,
            'enabled' => $enabled,
            'routeName' => $routeName,
        ];

        $exists = DB::table('pq_menus')->where('id', $id)->exists();

        if ($exists) {
            DB::table('pq_menus')->where('id', $id)->update($data);
        } else {
            DB::table('pq_menus')->insert(array_merge(['id' => $id], $data));
        }
    }
}
