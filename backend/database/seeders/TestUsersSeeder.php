<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder: TestUsersSeeder
 *
 * Crea usuarios de prueba para tests de autenticación (solo tabla USERS).
 *
 * Usuarios creados:
 * - JPEREZ: Usuario activo normal
 * - MGARCIA: Usuario activo
 * - INACTIVO: Usuario inactivo (activo=false)
 * - INHABILITADO: Usuario inhabilitado (inhabilitado=true)
 * - PWUSER: Usuario para tests de recuperación de contraseña
 * - SINPERFIL: Usuario sin name_user/email (para tests de perfil mínimo)
 *
 * @see TR-001(MH)-login-de-empleado.md
 */
class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        $testUsers = [
            [
                'codigo' => 'JPEREZ',
                'name_user' => 'Juan Pérez',
                'email' => 'juan.perez@ejemplo.com',
                'password' => 'password123',
                'activo' => true,
                'inhabilitado' => false,
            ],
            [
                'codigo' => 'MGARCIA',
                'name_user' => 'María García',
                'email' => 'maria.garcia@ejemplo.com',
                'password' => 'password456',
                'activo' => true,
                'inhabilitado' => false,
            ],
            [
                'codigo' => 'INACTIVO',
                'name_user' => 'Usuario Inactivo',
                'email' => 'inactivo@ejemplo.com',
                'password' => 'password789',
                'activo' => false,
                'inhabilitado' => false,
            ],
            [
                'codigo' => 'INHABILITADO',
                'name_user' => 'Usuario Inhabilitado',
                'email' => 'inhabilitado@ejemplo.com',
                'password' => 'password000',
                'activo' => true,
                'inhabilitado' => true,
            ],
            [
                'codigo' => 'PWUSER',
                'name_user' => 'Password User',
                'email' => 'pwuser@test.com',
                'password' => 'oldpass',
                'activo' => true,
                'inhabilitado' => false,
            ],
            [
                'codigo' => 'SINPERFIL',
                'name_user' => null,
                'email' => null,
                'password' => 'sinperfil123',
                'activo' => true,
                'inhabilitado' => false,
            ],
        ];

        foreach ($testUsers as $user) {
            $exists = DB::table('USERS')->where('codigo', $user['codigo'])->exists();
            if (!$exists) {
                DB::table('USERS')->insert([
                    'codigo' => $user['codigo'],
                    'name_user' => $user['name_user'],
                    'email' => $user['email'],
                    'password_hash' => Hash::make($user['password']),
                    'activo' => $user['activo'],
                    'inhabilitado' => $user['inhabilitado'],
                    'created_at' => DB::raw('GETDATE()'),
                    'updated_at' => DB::raw('GETDATE()'),
                ]);
            }
        }
    }
}
