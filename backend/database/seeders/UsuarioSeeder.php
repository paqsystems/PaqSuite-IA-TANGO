<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder: UsuarioSeeder
 * 
 * Crea los empleados mínimos en la tabla PQ_PARTES_USUARIOS para testing.
 * 
 * Usuarios creados:
 * - Administrador (supervisor=true, vinculado a ADMIN en USERS)
 * - Empleado Demo (supervisor=false, vinculado a EMP001 en USERS)
 * 
 * @see TR-00(MH)-Generacion-base-datos-inicial.md
 */
class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener IDs dinámicamente
        $adminUserId = DB::table('USERS')->where('code', 'ADMIN')->value('id');
        $emp001UserId = DB::table('USERS')->where('code', 'EMP001')->value('id');

        $usuarios = [
            ['user_id' => $adminUserId, 'code' => 'ADMIN', 'nombre' => 'Administrador del Sistema', 'email' => 'admin@paqsuite.local', 'supervisor' => true],
            ['user_id' => $emp001UserId, 'code' => 'EMP001', 'nombre' => 'Empleado Demo', 'email' => 'empleado@paqsuite.local', 'supervisor' => false],
        ];

        foreach ($usuarios as $usuario) {
            if ($usuario['user_id']) {
                $exists = DB::table('PQ_PARTES_USUARIOS')
                    ->where('code', $usuario['code'])
                    ->exists();
                
                if (!$exists) {
                    DB::table('PQ_PARTES_USUARIOS')->insert([
                        'user_id' => $usuario['user_id'],
                        'code' => $usuario['code'],
                        'nombre' => $usuario['nombre'],
                        'email' => $usuario['email'],
                        'supervisor' => $usuario['supervisor'],
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
