<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['codigo' => 'ADMIN', 'name_user' => 'Administrador', 'email' => 'admin@ejemplo.com', 'password' => 'admin123'],
            ['codigo' => 'EMP', 'name_user' => 'Usuario Operativo', 'email' => 'emp@ejemplo.com', 'password' => 'emple123'],
        ];

        foreach ($users as $user) {
            $exists = DB::table('USERS')->where('codigo', $user['codigo'])->exists();
            if (!$exists) {
                DB::table('USERS')->insert([
                    'codigo' => $user['codigo'],
                    'name_user' => $user['name_user'],
                    'email' => $user['email'],
                    'password_hash' => Hash::make($user['password']),
                    'activo' => true,
                    'inhabilitado' => false,
                    'created_at' => DB::raw('GETDATE()'),
                    'updated_at' => DB::raw('GETDATE()'),
                ]);
            }
        }
    }
}
