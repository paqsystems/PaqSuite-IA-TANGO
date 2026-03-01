<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Crear tabla PQ_PARAMETROS_GRAL (Company DB)
 *
 * Parámetros generales por módulo. Cada empresa tiene su instancia.
 *
 * @see docs/04-tareas/000-Generalidades/TR-007-Parametros-generales.md
 * @see docs/modelo-datos/md-empresas/pq-parametros-gral.md
 *
 * Ejecutar por empresa: php artisan migrate --database=company
 */
return new class extends Migration
{
    protected $connection = 'company';

    public function up(): void
    {
        Schema::connection($this->connection)->create('PQ_PARAMETROS_GRAL', function (Blueprint $table) {
            $table->string('Programa', 50)->comment('Clave del módulo');
            $table->string('Clave', 50)->comment('Clave del parámetro');
            $table->char('tipo_valor', 1)->nullable()->comment('S=String, T=Text, I=Int, D=DateTime, B=Bool, N=Decimal');
            $table->string('Valor_String', 255)->nullable();
            $table->text('Valor_Text')->nullable();
            $table->integer('Valor_Int')->nullable();
            $table->dateTime('Valor_DateTime')->nullable();
            $table->boolean('Valor_Bool')->nullable();
            $table->decimal('Valor_Decimal', 24, 6)->nullable();

            $table->primary(['Programa', 'Clave']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('PQ_PARAMETROS_GRAL');
    }
};
