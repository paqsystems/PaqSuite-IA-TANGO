<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder: UserSeeder
 * 
 * Crea los usuarios mínimos en la tabla USERS para testing.
 * Esta tabla es la de autenticación centralizada (sin prefijo PQ_PARTES_).
 * 
 * Usuarios creados:
 * - ADMIN: Usuario supervisor (password: admin123)
 * - CLI001: Usuario cliente (password: cliente123)
 * 
 * @see TR-00(MH)-Generacion-base-datos-inicial.md
 */
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            ['code' => 'ADMIN', 'password' => 'admin123'],
            ['code' => 'CLI001', 'password' => 'cliente123'],
            ['code' => 'EMP001', 'password' => 'empleado123'],
        ];

        foreach ($users as $user) {
            $exists = DB::table('USERS')
                ->where('code', $user['code'])
                ->exists();
            
            if (!$exists) {
                DB::table('USERS')->insert([
                    'code' => $user['code'],
                    'password_hash' => Hash::make($user['password']),
                    'activo' => true,
                    'inhabilitado' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
