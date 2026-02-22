<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Models\Usuario;
use App\Models\Cliente;
use App\Services\UserProfileService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Tests Unitarios: UserProfileService
 * 
 * Tests del servicio de perfil de usuario cubriendo todos los casos de uso.
 * Usa DatabaseTransactions para mejor rendimiento con SQL Server remoto.
 * 
 * @see TR-006(MH)-visualización-de-perfil-de-usuario.md
 */
class UserProfileServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected UserProfileService $profileService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->profileService = new UserProfileService();
        $this->seedTestUsers();
    }

    /**
     * Crear usuarios de prueba para los tests
     */
    protected function seedTestUsers(): void
    {
        // Limpiar usuarios existentes
        $testCodes = ['JPEREZ', 'MGARCIA', 'CLI001', 'SINPERFIL'];
        
        $usuarioIds = DB::table('PQ_PARTES_USUARIOS')->whereIn('code', $testCodes)->pluck('id');
        if ($usuarioIds->isNotEmpty()) {
            DB::table('PQ_PARTES_REGISTRO_TAREA')->whereIn('usuario_id', $usuarioIds)->delete();
        }
        DB::table('PQ_PARTES_USUARIOS')->whereIn('code', $testCodes)->delete();
        DB::table('PQ_PARTES_CLIENTES')->whereIn('code', $testCodes)->delete();
        
        $userIds = DB::table('USERS')->whereIn('code', $testCodes)->pluck('id');
        if ($userIds->isNotEmpty()) {
            DB::table('personal_access_tokens')
                ->where('tokenable_type', 'App\\Models\\User')
                ->whereIn('tokenable_id', $userIds)
                ->delete();
        }
        
        DB::table('USERS')->whereIn('code', $testCodes)->delete();

        // Usuario empleado normal
        DB::table('USERS')->insert([
            'code' => 'JPEREZ',
            'password_hash' => Hash::make('password123'),
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $jperezId = DB::table('USERS')->where('code', 'JPEREZ')->value('id');

        DB::table('PQ_PARTES_USUARIOS')->insert([
            'user_id' => $jperezId,
            'code' => 'JPEREZ',
            'nombre' => 'Juan Pérez',
            'email' => 'juan.perez@ejemplo.com',
            'supervisor' => false,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Usuario empleado supervisor
        DB::table('USERS')->insert([
            'code' => 'MGARCIA',
            'password_hash' => Hash::make('password456'),
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $mgarciaId = DB::table('USERS')->where('code', 'MGARCIA')->value('id');

        DB::table('PQ_PARTES_USUARIOS')->insert([
            'user_id' => $mgarciaId,
            'code' => 'MGARCIA',
            'nombre' => 'María García',
            'email' => 'maria.garcia@ejemplo.com',
            'supervisor' => true,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Usuario cliente
        DB::table('USERS')->insert([
            'code' => 'CLI001',
            'password_hash' => Hash::make('cliente123'),
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cli001Id = DB::table('USERS')->where('code', 'CLI001')->value('id');

        $tipoClienteId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->where('code', 'CORP')->value('id');
        if (!$tipoClienteId) {
            $tipoClienteId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->first()?->id;
        }

        if ($tipoClienteId) {
            DB::table('PQ_PARTES_CLIENTES')->insert([
                'user_id' => $cli001Id,
                'code' => 'CLI001',
                'nombre' => 'Empresa ABC S.A.',
                'email' => 'contacto@empresaabc.com',
                'tipo_cliente_id' => $tipoClienteId,
                'activo' => true,
                'inhabilitado' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Usuario sin perfil
        DB::table('USERS')->insert([
            'code' => 'SINPERFIL',
            'password_hash' => Hash::make('sinperfil123'),
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** @test */
    public function getProfile_empleado_normal_retorna_datos_correctos()
    {
        $user = User::where('code', 'JPEREZ')->first();
        $profile = $this->profileService->getProfile($user);

        $this->assertEquals('JPEREZ', $profile['user_code']);
        $this->assertEquals('Juan Pérez', $profile['nombre']);
        $this->assertEquals('juan.perez@ejemplo.com', $profile['email']);
        $this->assertEquals('usuario', $profile['tipo_usuario']);
        $this->assertFalse($profile['es_supervisor']);
        $this->assertNotNull($profile['created_at']);
    }

    /** @test */
    public function getProfile_empleado_supervisor_retorna_es_supervisor_true()
    {
        $user = User::where('code', 'MGARCIA')->first();
        $profile = $this->profileService->getProfile($user);

        $this->assertEquals('MGARCIA', $profile['user_code']);
        $this->assertEquals('María García', $profile['nombre']);
        $this->assertEquals('usuario', $profile['tipo_usuario']);
        $this->assertTrue($profile['es_supervisor']);
    }

    /** @test */
    public function getProfile_cliente_retorna_datos_correctos()
    {
        $user = User::where('code', 'CLI001')->first();
        
        if (!$user) {
            $this->markTestSkipped('Cliente CLI001 no existe en la base de datos');
        }

        $profile = $this->profileService->getProfile($user);

        $this->assertEquals('CLI001', $profile['user_code']);
        $this->assertEquals('Empresa ABC S.A.', $profile['nombre']);
        $this->assertEquals('cliente', $profile['tipo_usuario']);
        $this->assertFalse($profile['es_supervisor']);
    }

    /** @test */
    public function getProfile_usuario_sin_perfil_retorna_perfil_minimo()
    {
        $user = User::where('code', 'SINPERFIL')->first();
        $profile = $this->profileService->getProfile($user);

        $this->assertEquals('SINPERFIL', $profile['user_code']);
        $this->assertEquals('SINPERFIL', $profile['nombre']); // Usa code como fallback
        $this->assertNull($profile['email']);
        $this->assertEquals('desconocido', $profile['tipo_usuario']);
        $this->assertFalse($profile['es_supervisor']);
    }

    /** @test */
    public function getProfile_empleado_sin_email_retorna_null()
    {
        // Crear empleado sin email
        DB::table('USERS')->insert([
            'code' => 'SINEMAIL',
            'password_hash' => Hash::make('password123'),
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sinEmailId = DB::table('USERS')->where('code', 'SINEMAIL')->value('id');

        DB::table('PQ_PARTES_USUARIOS')->insert([
            'user_id' => $sinEmailId,
            'code' => 'SINEMAIL',
            'nombre' => 'Usuario Sin Email',
            'email' => null,
            'supervisor' => false,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = User::where('code', 'SINEMAIL')->first();
        $profile = $this->profileService->getProfile($user);

        $this->assertNull($profile['email']);
    }

    /** @test */
    public function getProfile_retorna_todos_los_campos_requeridos()
    {
        $user = User::where('code', 'JPEREZ')->first();
        $profile = $this->profileService->getProfile($user);

        $this->assertArrayHasKey('user_code', $profile);
        $this->assertArrayHasKey('nombre', $profile);
        $this->assertArrayHasKey('email', $profile);
        $this->assertArrayHasKey('tipo_usuario', $profile);
        $this->assertArrayHasKey('es_supervisor', $profile);
        $this->assertArrayHasKey('created_at', $profile);
    }

    /** @test */
    public function getProfile_fecha_creacion_formato_iso8601()
    {
        $user = User::where('code', 'JPEREZ')->first();
        $profile = $this->profileService->getProfile($user);

        // Verificar que created_at está en formato ISO8601
        $this->assertIsString($profile['created_at']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $profile['created_at']);
    }

    /** @test */
    public function updateProfile_empleado_actualiza_nombre_y_email()
    {
        $user = User::where('code', 'JPEREZ')->first();
        $profile = $this->profileService->updateProfile($user, [
            'nombre' => 'Juan Pérez Actualizado',
            'email' => 'nuevo@ejemplo.com',
        ]);

        $this->assertEquals('JPEREZ', $profile['user_code']);
        $this->assertEquals('Juan Pérez Actualizado', $profile['nombre']);
        $this->assertEquals('nuevo@ejemplo.com', $profile['email']);

        $empleado = Usuario::where('user_id', $user->id)->first();
        $this->assertEquals('Juan Pérez Actualizado', $empleado->nombre);
        $this->assertEquals('nuevo@ejemplo.com', $empleado->email);
    }

    /** @test */
    public function updateProfile_cliente_actualiza_nombre_y_email()
    {
        $user = User::where('code', 'CLI001')->first();
        if (!$user) {
            $this->markTestSkipped('Cliente CLI001 no existe');
        }

        $profile = $this->profileService->updateProfile($user, [
            'nombre' => 'Empresa ABC Actualizada',
            'email' => 'nuevo@empresaabc.com',
        ]);

        $this->assertEquals('CLI001', $profile['user_code']);
        $this->assertEquals('Empresa ABC Actualizada', $profile['nombre']);
        $this->assertEquals('nuevo@empresaabc.com', $profile['email']);
    }

    /** @test */
    public function updateProfile_email_vacio_guarda_null()
    {
        $user = User::where('code', 'JPEREZ')->first();
        $this->profileService->updateProfile($user, [
            'nombre' => 'Juan Pérez',
            'email' => '',
        ]);

        $empleado = Usuario::where('user_id', $user->id)->first();
        $this->assertNull($empleado->email);
    }

    /** @test */
    public function updateProfile_usuario_sin_perfil_lanza_excepcion()
    {
        $user = User::where('code', 'SINPERFIL')->first();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('sin perfil');

        $this->profileService->updateProfile($user, [
            'nombre' => 'Cualquier Nombre',
            'email' => null,
        ]);
    }
}
