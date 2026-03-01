<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Tests de Integración: GET /api/v1/admin/grupos-empresarios
 *
 * @see docs/04-tareas/002-GruposEmpresarios/TR-001-listado-grupos-empresarios.md
 */
class GrupoEmpresarioControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        if (Schema::hasTable('pq_grupo_empresario')) {
            $this->seedAdminUserAndCompany();
        }
    }

    protected function seedAdminUserAndCompany(): void
    {
        if (!Schema::hasTable('pq_empresa') || !Schema::hasTable('pq_rol') || !Schema::hasTable('pq_permiso')) {
            return;
        }

        $adminCode = 'ADM_GRUPOS';
        $user = User::where('codigo', $adminCode)->first();
        if (!$user) {
            $userId = DB::table('USERS')->insertGetId([
                'codigo' => $adminCode,
                'name_user' => 'Admin Grupos',
                'email' => 'admin.grupos@test.com',
                'password_hash' => Hash::make('password123'),
                'activo' => true,
                'inhabilitado' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $user = User::find($userId);
        }

        $empresa = DB::table('pq_empresa')->first();
        if (!$empresa) {
            $usePqSchema = Schema::hasColumn('pq_empresa', 'IDEmpresa');
            if ($usePqSchema) {
                DB::table('pq_empresa')->insert([
                    'NombreEmpresa' => 'Empresa Test',
                    'NombreBD' => 'test_db',
                    'Habilita' => 1,
                    'theme' => 'default',
                ]);
            } else {
                DB::table('pq_empresa')->insert([
                    'nombre_empresa' => 'Empresa Test',
                    'nombre_bd' => 'test_db',
                    'habilita' => 1,
                    'theme' => 'default',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $empresa = DB::table('pq_empresa')->first();
        }

        $empresaId = $empresa->IDEmpresa ?? $empresa->id ?? null;
        if (!$empresaId) {
            return;
        }

        $rol = DB::table('pq_rol')->where('acceso_total', true)->first();
        if (!$rol) {
            $rolId = DB::table('pq_rol')->insertGetId([
                'nombre_rol' => 'ADMIN',
                'descripcion_rol' => 'Administrador',
                'acceso_total' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $rol = DB::table('pq_rol')->where('id', $rolId)->first();
        }

        $permisoExists = DB::table('pq_permiso')
            ->where('id_usuario', $user->id)
            ->where('id_rol', $rol->id)
            ->where('id_empresa', $empresaId)
            ->exists();
        if (!$permisoExists) {
            DB::table('pq_permiso')->insert([
                'id_usuario' => $user->id,
                'id_rol' => $rol->id,
                'id_empresa' => $empresaId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /** @test */
    public function get_grupos_empresarios_admin_retorna_200_con_items(): void
    {
        if (!Schema::hasTable('pq_grupo_empresario')) {
            $this->markTestSkipped('Tabla pq_grupo_empresario no existe (migraciones no ejecutadas)');
        }

        $user = User::where('codigo', 'ADM_GRUPOS')->first();
        if (!$user) {
            $this->markTestSkipped('Usuario admin no configurado');
        }

        Sanctum::actingAs($user);
        $empresa = DB::table('pq_empresa')->first();
        $empresaId = $empresa->IDEmpresa ?? $empresa->id ?? 1;
        $headers = ['X-Company-Id' => (string) $empresaId];

        $response = $this->getJson('/api/v1/admin/grupos-empresarios', $headers);

        $response->assertStatus(200)
            ->assertJson([
                'error' => 0,
                'respuesta' => 'OK',
            ])
            ->assertJsonStructure([
                'resultado' => [
                    'items',
                    'page',
                    'page_size',
                    'total',
                    'total_pages',
                ],
            ]);
    }

    /** @test */
    public function get_grupos_empresarios_sin_auth_retorna_401(): void
    {
        $response = $this->getJson('/api/v1/admin/grupos-empresarios');

        $response->assertStatus(401);
    }

    /** @test */
    public function post_grupos_empresarios_admin_crea_grupo_con_empresas(): void
    {
        if (!Schema::hasTable('pq_grupo_empresario')) {
            $this->markTestSkipped('Tabla pq_grupo_empresario no existe');
        }

        $user = User::where('codigo', 'ADM_GRUPOS')->first();
        if (!$user) {
            $this->markTestSkipped('Usuario admin no configurado');
        }

        $empresa = DB::table('pq_empresa')->first();
        if (!$empresa) {
            $this->markTestSkipped('No hay empresas en la base');
        }

        $empresaId = $empresa->IDEmpresa ?? $empresa->id ?? null;
        Sanctum::actingAs($user);
        $headers = ['X-Company-Id' => (string) $empresaId];

        $response = $this->postJson('/api/v1/admin/grupos-empresarios', [
            'descripcion' => 'Grupo Test',
            'empresaIds' => [(int) $empresaId],
        ], $headers);

        $response->assertStatus(201)
            ->assertJson([
                'error' => 0,
                'respuesta' => 'Grupo empresario creado',
            ])
            ->assertJsonPath('resultado.descripcion', 'Grupo Test');
        $this->assertSame(1, (int) $response->json('resultado.cantidadEmpresas'));
    }

    /** @test */
    public function post_grupos_empresarios_sin_empresas_retorna_422(): void
    {
        if (!Schema::hasTable('pq_grupo_empresario')) {
            $this->markTestSkipped('Tabla pq_grupo_empresario no existe');
        }

        $user = User::where('codigo', 'ADM_GRUPOS')->first();
        if (!$user) {
            $this->markTestSkipped('Usuario admin no configurado');
        }

        $empresa = DB::table('pq_empresa')->first();
        $empresaId = $empresa ? ($empresa->IDEmpresa ?? $empresa->id) : 1;
        Sanctum::actingAs($user);
        $headers = ['X-Company-Id' => (string) $empresaId];

        $response = $this->postJson('/api/v1/admin/grupos-empresarios', [
            'descripcion' => 'Grupo Sin Empresas',
            'empresaIds' => [],
        ], $headers);

        $response->assertStatus(422);
    }
}
