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
            ['code' => 'ADMIN', 'name' => 'Administrador', 'email' => 'admin@ejemplo.com', 'password' => 'admin123'],
        ];

        foreach ($users as $user) {
            $exists = DB::table('USERS')->where('code', $user['code'])->exists();
            if (!$exists) {
                DB::table('USERS')->insert([
                    'code' => $user['code'],
                    'name' => $user['name'],
                    'email' => $user['email'],
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
