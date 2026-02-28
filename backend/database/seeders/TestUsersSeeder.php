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
 * - SINPERFIL: Usuario sin name/email (para tests de perfil mínimo)
 *
 * @see TR-001(MH)-login-de-empleado.md
 */
class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        $testUsers = [
            [
                'code' => 'JPEREZ',
                'name' => 'Juan Pérez',
                'email' => 'juan.perez@ejemplo.com',
                'password' => 'password123',
                'activo' => true,
                'inhabilitado' => false,
            ],
            [
                'code' => 'MGARCIA',
                'name' => 'María García',
                'email' => 'maria.garcia@ejemplo.com',
                'password' => 'password456',
                'activo' => true,
                'inhabilitado' => false,
            ],
            [
                'code' => 'INACTIVO',
                'name' => 'Usuario Inactivo',
                'email' => 'inactivo@ejemplo.com',
                'password' => 'password789',
                'activo' => false,
                'inhabilitado' => false,
            ],
            [
                'code' => 'INHABILITADO',
                'name' => 'Usuario Inhabilitado',
                'email' => 'inhabilitado@ejemplo.com',
                'password' => 'password000',
                'activo' => true,
                'inhabilitado' => true,
            ],
            [
                'code' => 'PWUSER',
                'name' => 'Password User',
                'email' => 'pwuser@test.com',
                'password' => 'oldpass',
                'activo' => true,
                'inhabilitado' => false,
            ],
            [
                'code' => 'SINPERFIL',
                'name' => null,
                'email' => null,
                'password' => 'sinperfil123',
                'activo' => true,
                'inhabilitado' => false,
            ],
        ];

        foreach ($testUsers as $user) {
            $exists = DB::table('USERS')->where('code', $user['code'])->exists();
            if (!$exists) {
                DB::table('USERS')->insert([
                    'code' => $user['code'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'password_hash' => Hash::make($user['password']),
                    'activo' => $user['activo'],
                    'inhabilitado' => $user['inhabilitado'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
