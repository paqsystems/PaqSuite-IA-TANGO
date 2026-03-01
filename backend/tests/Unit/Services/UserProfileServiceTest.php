<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\UserProfileService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Tests Unitarios: UserProfileService
 *
 * Servicio simplificado: solo tabla USERS (name, email).
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

    protected function seedTestUsers(): void
    {
        $testCodes = ['JPEREZ', 'MGARCIA', 'CLI001', 'SINPERFIL', 'SINEMAIL'];

        $userIds = DB::table('USERS')->whereIn('codigo', $testCodes)->pluck('id');
        if ($userIds->isNotEmpty()) {
            DB::table('personal_access_tokens')
                ->where('tokenable_type', 'App\\Models\\User')
                ->whereIn('tokenable_id', $userIds)
                ->delete();
        }
        DB::table('USERS')->whereIn('codigo', $testCodes)->delete();

        DB::table('USERS')->insert([
            'codigo' => 'JPEREZ',
            'name_user' => 'Juan Pérez',
            'email' => 'juan.perez@ejemplo.com',
            'password_hash' => Hash::make('password123'),
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('USERS')->insert([
            'codigo' => 'MGARCIA',
            'name_user' => 'María García',
            'email' => 'maria.garcia@ejemplo.com',
            'password_hash' => Hash::make('password456'),
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('USERS')->insert([
            'codigo' => 'CLI001',
            'name_user' => 'Empresa ABC S.A.',
            'email' => 'contacto@empresaabc.com',
            'password_hash' => Hash::make('cliente123'),
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('USERS')->insert([
            'codigo' => 'SINPERFIL',
            'name_user' => null,
            'email' => null,
            'password_hash' => Hash::make('sinperfil123'),
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('USERS')->insert([
            'codigo' => 'SINEMAIL',
            'name_user' => 'Usuario Sin Email',
            'email' => null,
            'password_hash' => Hash::make('password123'),
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** @test */
    public function getProfile_empleado_normal_retorna_datos_correctos()
    {
        $user = User::where('codigo', 'JPEREZ')->first();
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
        $user = User::where('codigo', 'MGARCIA')->first();
        $profile = $this->profileService->getProfile($user);

        $this->assertEquals('MGARCIA', $profile['user_code']);
        $this->assertEquals('María García', $profile['nombre']);
        $this->assertEquals('usuario', $profile['tipo_usuario']);
        $this->assertFalse($profile['es_supervisor']);
    }

    /** @test */
    public function getProfile_cliente_retorna_datos_correctos()
    {
        $user = User::where('codigo', 'CLI001')->first();
        $profile = $this->profileService->getProfile($user);

        $this->assertEquals('CLI001', $profile['user_code']);
        $this->assertEquals('Empresa ABC S.A.', $profile['nombre']);
        $this->assertEquals('usuario', $profile['tipo_usuario']);
        $this->assertFalse($profile['es_supervisor']);
    }

    /** @test */
    public function getProfile_usuario_sin_perfil_retorna_perfil_minimo()
    {
        $user = User::where('codigo', 'SINPERFIL')->first();
        $profile = $this->profileService->getProfile($user);

        $this->assertEquals('SINPERFIL', $profile['user_code']);
        $this->assertEquals('SINPERFIL', $profile['nombre']);
        $this->assertNull($profile['email']);
        $this->assertEquals('usuario', $profile['tipo_usuario']);
        $this->assertFalse($profile['es_supervisor']);
    }

    /** @test */
    public function getProfile_empleado_sin_email_retorna_null()
    {
        $user = User::where('codigo', 'SINEMAIL')->first();
        $profile = $this->profileService->getProfile($user);

        $this->assertNull($profile['email']);
    }

    /** @test */
    public function getProfile_retorna_todos_los_campos_requeridos()
    {
        $user = User::where('codigo', 'JPEREZ')->first();
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
        $user = User::where('codigo', 'JPEREZ')->first();
        $profile = $this->profileService->getProfile($user);

        $this->assertIsString($profile['created_at']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $profile['created_at']);
    }

    /** @test */
    public function updateProfile_empleado_actualiza_nombre_y_email()
    {
        $user = User::where('codigo', 'JPEREZ')->first();
        $profile = $this->profileService->updateProfile($user, [
            'nombre' => 'Juan Pérez Actualizado',
            'email' => 'nuevo@ejemplo.com',
        ]);

        $this->assertEquals('JPEREZ', $profile['user_code']);
        $this->assertEquals('Juan Pérez Actualizado', $profile['nombre']);
        $this->assertEquals('nuevo@ejemplo.com', $profile['email']);

        $user->refresh();
        $this->assertEquals('Juan Pérez Actualizado', $user->name_user);
        $this->assertEquals('nuevo@ejemplo.com', $user->email);
    }

    /** @test */
    public function updateProfile_cliente_actualiza_nombre_y_email()
    {
        $user = User::where('codigo', 'CLI001')->first();
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
        $user = User::where('codigo', 'JPEREZ')->first();
        $this->profileService->updateProfile($user, [
            'nombre' => 'Juan Pérez',
            'email' => '',
        ]);

        $user->refresh();
        $this->assertNull($user->email);
    }

    /** @test */
    public function updateProfile_usuario_sin_perfil_actualiza_en_users()
    {
        $user = User::where('codigo', 'SINPERFIL')->first();
        $profile = $this->profileService->updateProfile($user, [
            'nombre' => 'Usuario Actualizado',
            'email' => 'nuevo@ejemplo.com',
        ]);

        $this->assertEquals('SINPERFIL', $profile['user_code']);
        $this->assertEquals('Usuario Actualizado', $profile['nombre']);
        $this->assertEquals('nuevo@ejemplo.com', $profile['email']);
    }
}
