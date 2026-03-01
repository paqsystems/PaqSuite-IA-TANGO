<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\EmpresaController;
use App\Http\Controllers\Api\V1\UserProfileController;
use App\Http\Controllers\Api\V1\UserPreferencesController;
use App\Http\Controllers\Api\V1\GridLayoutController;
use App\Http\Controllers\Api\V1\ParametrosGralController;
use App\Http\Controllers\Api\V1\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\V1\Admin\EmpresaAdminController;
use App\Http\Controllers\Api\V1\Admin\RolController as AdminRolController;
use App\Http\Controllers\Api\V1\Admin\PermisoController as AdminPermisoController;
use App\Http\Controllers\Api\V1\Admin\GrupoEmpresarioController;
use App\Http\Controllers\Api\V1\Admin\RolAtributoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->name('api.v1.auth.login');
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('api.v1.auth.forgotPassword');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('api.v1.auth.resetPassword');
    });

    Route::middleware(['auth:sanctum', 'company', 'company.connection'])->group(function () {
        Route::get('/empresas', [EmpresaController::class, 'index'])->name('api.v1.empresas.index');

        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
        Route::post('/auth/change-password', [AuthController::class, 'changePassword'])->name('api.v1.auth.changePassword');

        Route::get('/user', function (Request $request) {
            return $request->user();
        })->name('api.v1.user');

        Route::get('/user/profile', [UserProfileController::class, 'show'])->name('api.v1.user.profile');
        Route::put('/user/profile', [UserProfileController::class, 'update'])->name('api.v1.user.profile.update');
        Route::get('/user/preferences', [UserPreferencesController::class, 'show'])->name('api.v1.user.preferences');
        Route::put('/user/preferences', [UserPreferencesController::class, 'update'])->name('api.v1.user.preferences.update');

        Route::get('/grid-layouts', [GridLayoutController::class, 'index'])->name('api.v1.grid-layouts.index');
        Route::get('/grid-layouts/last-used', [GridLayoutController::class, 'lastUsed'])->name('api.v1.grid-layouts.last-used');
        Route::post('/grid-layouts', [GridLayoutController::class, 'store'])->name('api.v1.grid-layouts.store');
        Route::put('/grid-layouts/{id}', [GridLayoutController::class, 'update'])->name('api.v1.grid-layouts.update');
        Route::delete('/grid-layouts/{id}', [GridLayoutController::class, 'destroy'])->name('api.v1.grid-layouts.destroy');
        Route::post('/grid-layouts/{id}/use', [GridLayoutController::class, 'markAsUsed'])->name('api.v1.grid-layouts.mark-used');

        Route::get('/parametros-gral', [ParametrosGralController::class, 'index'])->name('api.v1.parametros-gral.index');
        Route::put('/parametros-gral/{programa}/{clave}', [ParametrosGralController::class, 'update'])->name('api.v1.parametros-gral.update');

        Route::middleware('admin')->prefix('admin')->group(function () {
            Route::get('/users', [AdminUserController::class, 'index'])->name('api.v1.admin.users.index');
            Route::get('/users/{id}', [AdminUserController::class, 'show'])->name('api.v1.admin.users.show');
            Route::post('/users', [AdminUserController::class, 'store'])->name('api.v1.admin.users.store');
            Route::put('/users/{id}', [AdminUserController::class, 'update'])->name('api.v1.admin.users.update');
            Route::put('/users/{id}/inhabilitar', [AdminUserController::class, 'inhabilitar'])->name('api.v1.admin.users.inhabilitar');

            Route::get('/empresas', [EmpresaAdminController::class, 'index'])->name('api.v1.admin.empresas.index');
            Route::get('/empresas/{id}', [EmpresaAdminController::class, 'show'])->name('api.v1.admin.empresas.show');
            Route::post('/empresas', [EmpresaAdminController::class, 'store'])->name('api.v1.admin.empresas.store');
            Route::put('/empresas/{id}', [EmpresaAdminController::class, 'update'])->name('api.v1.admin.empresas.update');

            Route::get('/grupos-empresarios', [GrupoEmpresarioController::class, 'index'])->name('api.v1.admin.grupos-empresarios.index');
            Route::post('/grupos-empresarios', [GrupoEmpresarioController::class, 'store'])->name('api.v1.admin.grupos-empresarios.store');
            Route::get('/grupos-empresarios/{id}', [GrupoEmpresarioController::class, 'show'])->name('api.v1.admin.grupos-empresarios.show');
            Route::put('/grupos-empresarios/{id}', [GrupoEmpresarioController::class, 'update'])->name('api.v1.admin.grupos-empresarios.update');
            Route::delete('/grupos-empresarios/{id}', [GrupoEmpresarioController::class, 'destroy'])->name('api.v1.admin.grupos-empresarios.destroy');

            Route::get('/roles', [AdminRolController::class, 'index'])->name('api.v1.admin.roles.index');
            Route::get('/roles/{id}', [AdminRolController::class, 'show'])->name('api.v1.admin.roles.show');
            Route::get('/roles/{id}/atributos', [RolAtributoController::class, 'index'])->name('api.v1.admin.roles.atributos.index');
            Route::put('/roles/{id}/atributos', [RolAtributoController::class, 'update'])->name('api.v1.admin.roles.atributos.update');
            Route::post('/roles', [AdminRolController::class, 'store'])->name('api.v1.admin.roles.store');
            Route::put('/roles/{id}', [AdminRolController::class, 'update'])->name('api.v1.admin.roles.update');
            Route::delete('/roles/{id}', [AdminRolController::class, 'destroy'])->name('api.v1.admin.roles.destroy');

            Route::get('/permisos', [AdminPermisoController::class, 'index'])->name('api.v1.admin.permisos.index');
            Route::post('/permisos', [AdminPermisoController::class, 'store'])->name('api.v1.admin.permisos.store');
            Route::put('/permisos/{id}', [AdminPermisoController::class, 'update'])->name('api.v1.admin.permisos.update');
            Route::delete('/permisos/{id}', [AdminPermisoController::class, 'destroy'])->name('api.v1.admin.permisos.destroy');
        });
    });
});
