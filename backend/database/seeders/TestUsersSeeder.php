<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder: TestUsersSeeder
 * 
 * Crea usuarios de prueba para tests de autenticación.
 * Incluye empleados y clientes con diferentes estados para cubrir todos los casos de test.
 * 
 * Empleados creados:
 * - JPEREZ: Empleado activo normal (supervisor=false)
 * - MGARCIA: Empleado activo supervisor (supervisor=true)
 * - INACTIVO: Usuario inactivo en USERS (activo=false)
 * - INHABILITADO: Usuario inhabilitado en USERS (inhabilitado=true)
 * - USUINACTIVO: Usuario activo en USERS pero inactivo en PQ_PARTES_USUARIOS
 * 
 * Clientes creados:
 * - CLI001: Cliente activo con acceso al sistema
 * - CLIINACTIVO: Cliente activo en USERS pero inactivo en PQ_PARTES_CLIENTES
 * - SINPERFIL: Usuario en USERS sin perfil en ninguna tabla
 * 
 * @see TR-001(MH)-login-de-empleado.md
 * @see TR-002(SH)-login-de-cliente.md
 */
class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuarios de prueba en tabla USERS (empleados + clientes)
        $testUsers = [
            // Empleados
            [
                'code' => 'JPEREZ',
                'password' => 'password123',
                'activo' => true,
                'inhabilitado' => false,
            ],
            [
                'code' => 'MGARCIA',
                'password' => 'password456',
                'activo' => true,
                'inhabilitado' => false,
            ],
            [
                'code' => 'INACTIVO',
                'password' => 'password789',
                'activo' => false,
                'inhabilitado' => false,
            ],
            [
                'code' => 'INHABILITADO',
                'password' => 'password000',
                'activo' => true,
                'inhabilitado' => true,
            ],
            [
                'code' => 'USUINACTIVO',
                'password' => 'password111',
                'activo' => true,
                'inhabilitado' => false,
            ],
            // Clientes (TR-002)
            [
                'code' => 'CLI001',
                'password' => 'cliente123',
                'activo' => true,
                'inhabilitado' => false,
            ],
            [
                'code' => 'CLIINACTIVO',
                'password' => 'cliente456',
                'activo' => true,
                'inhabilitado' => false,
            ],
            [
                'code' => 'SINPERFIL',
                'password' => 'sinperfil123',
                'activo' => true,
                'inhabilitado' => false,
            ],
        ];

        // Insertar usuarios en USERS
        foreach ($testUsers as $user) {
            $exists = DB::table('USERS')
                ->where('code', $user['code'])
                ->exists();
            
            if (!$exists) {
                DB::table('USERS')->insert([
                    'code' => $user['code'],
                    'password_hash' => Hash::make($user['password']),
                    'activo' => $user['activo'],
                    'inhabilitado' => $user['inhabilitado'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Obtener IDs de los usuarios creados (empleados)
        $jperezId = DB::table('USERS')->where('code', 'JPEREZ')->value('id');
        $mgarciaId = DB::table('USERS')->where('code', 'MGARCIA')->value('id');
        $inactivoId = DB::table('USERS')->where('code', 'INACTIVO')->value('id');
        $inhabilitadoId = DB::table('USERS')->where('code', 'INHABILITADO')->value('id');
        $usuInactivoId = DB::table('USERS')->where('code', 'USUINACTIVO')->value('id');

        // Obtener IDs de los usuarios creados (clientes)
        $cli001Id = DB::table('USERS')->where('code', 'CLI001')->value('id');
        $cliInactivoId = DB::table('USERS')->where('code', 'CLIINACTIVO')->value('id');
        // SINPERFIL no tiene perfil en ninguna tabla

        // Empleados de prueba en PQ_PARTES_USUARIOS
        $testEmpleados = [
            [
                'user_id' => $jperezId,
                'code' => 'JPEREZ',
                'nombre' => 'Juan Pérez',
                'email' => 'juan.perez@ejemplo.com',
                'supervisor' => false,
                'activo' => true,
                'inhabilitado' => false,
            ],
            [
                'user_id' => $mgarciaId,
                'code' => 'MGARCIA',
                'nombre' => 'María García',
                'email' => 'maria.garcia@ejemplo.com',
                'supervisor' => true,
                'activo' => true,
                'inhabilitado' => false,
            ],
            [
                'user_id' => $inactivoId,
                'code' => 'INACTIVO',
                'nombre' => 'Usuario Inactivo',
                'email' => 'inactivo@ejemplo.com',
                'supervisor' => false,
                'activo' => false,
                'inhabilitado' => false,
            ],
            [
                'user_id' => $inhabilitadoId,
                'code' => 'INHABILITADO',
                'nombre' => 'Usuario Inhabilitado',
                'email' => 'inhabilitado@ejemplo.com',
                'supervisor' => false,
                'activo' => true,
                'inhabilitado' => true,
            ],
            [
                'user_id' => $usuInactivoId,
                'code' => 'USUINACTIVO',
                'nombre' => 'Usuario Inactivo en Empleados',
                'email' => 'usuinactivo@ejemplo.com',
                'supervisor' => false,
                'activo' => false,
                'inhabilitado' => false,
            ],
        ];

        // Insertar empleados en PQ_PARTES_USUARIOS
        foreach ($testEmpleados as $empleado) {
            if ($empleado['user_id']) {
                $exists = DB::table('PQ_PARTES_USUARIOS')
                    ->where('code', $empleado['code'])
                    ->exists();
                
                if (!$exists) {
                    DB::table('PQ_PARTES_USUARIOS')->insert([
                        'user_id' => $empleado['user_id'],
                        'code' => $empleado['code'],
                        'nombre' => $empleado['nombre'],
                        'email' => $empleado['email'],
                        'supervisor' => $empleado['supervisor'],
                        'activo' => $empleado['activo'],
                        'inhabilitado' => $empleado['inhabilitado'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Obtener tipo de cliente para los clientes de prueba
        $tipoClienteId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->where('code', 'CORP')->value('id');
        
        // Fallback si no existe el tipo CORP
        if (!$tipoClienteId) {
            $tipoClienteId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->first()?->id;
        }

        // Clientes de prueba en PQ_PARTES_CLIENTES (TR-002)
        if ($tipoClienteId) {
            $testClientes = [
                [
                    'user_id' => $cli001Id,
                    'code' => 'CLI001',
                    'nombre' => 'Empresa ABC S.A.',
                    'email' => 'contacto@empresaabc.com',
                    'tipo_cliente_id' => $tipoClienteId,
                    'activo' => true,
                    'inhabilitado' => false,
                ],
                [
                    'user_id' => $cliInactivoId,
                    'code' => 'CLIINACTIVO',
                    'nombre' => 'Cliente Inactivo S.R.L.',
                    'email' => 'contacto@clienteinactivo.com',
                    'tipo_cliente_id' => $tipoClienteId,
                    'activo' => false,
                    'inhabilitado' => false,
                ],
            ];

            // Insertar clientes en PQ_PARTES_CLIENTES
            foreach ($testClientes as $cliente) {
                if ($cliente['user_id']) {
                    $exists = DB::table('PQ_PARTES_CLIENTES')
                        ->where('code', $cliente['code'])
                        ->exists();
                    
                    if (!$exists) {
                        DB::table('PQ_PARTES_CLIENTES')->insert([
                            'user_id' => $cliente['user_id'],
                            'code' => $cliente['code'],
                            'nombre' => $cliente['nombre'],
                            'email' => $cliente['email'],
                            'tipo_cliente_id' => $cliente['tipo_cliente_id'],
                            'activo' => $cliente['activo'],
                            'inhabilitado' => $cliente['inhabilitado'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }
}
