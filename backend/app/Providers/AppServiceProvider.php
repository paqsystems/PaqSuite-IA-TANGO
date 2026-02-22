<?php

namespace App\Providers;

use App\Models\PersonalAccessToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Usar modelo personalizado de PersonalAccessToken para SQL Server
        // @see .cursor/rules/20-sql-server-datetime-format.md
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        // Configurar formato de fecha YMD para SQL Server
        // Esto previene errores de "valor fuera de intervalo" con fechas
        // @see .cursor/rules/20-sql-server-datetime-format.md
        if (config('database.default') === 'sqlsrv') {
            DB::statement('SET DATEFORMAT ymd');
        }
    }
}
