<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PqPermisoSeeder extends Seeder
{
    public function run(): void
    {
        $rolNombreCol = Schema::hasColumn('pq_rol', 'NombreRol') ? 'NombreRol' : 'nombre_rol';
        $rolKeyCol = Schema::hasColumn('pq_rol', 'IDRol') ? 'IDRol' : 'id';
        $empresaNombreCol = Schema::hasColumn('pq_empresa', 'NombreEmpresa') ? 'NombreEmpresa' : 'nombre_empresa';
        $empresaKeyCol = Schema::hasColumn('pq_empresa', 'IDEmpresa') ? 'IDEmpresa' : 'id';
        $permisoRolCol = Schema::hasColumn('pq_permiso', 'IDRol') ? 'IDRol' : 'id_rol';
        $permisoEmpresaCol = Schema::hasColumn('pq_permiso', 'IDEmpresa') ? 'IDEmpresa' : 'id_empresa';
        $permisoUsuarioCol = Schema::hasColumn('pq_permiso', 'IDUsuario') ? 'IDUsuario' : 'id_usuario';

        $rol = DB::table('pq_rol')->where($rolNombreCol, 'Supervisor')->first();
        $empresa = DB::table('pq_empresa')->where($empresaNombreCol, 'Empresa Desarrollo')->first();

        if (!$rol || !$empresa) {
            return;
        }

        $rolId = $rol->{$rolKeyCol};
        $empresaId = $empresa->{$empresaKeyCol};

        $usuarios = DB::table('USERS')->whereIn('codigo', ['ADMIN', 'EMP'])->get();

        foreach ($usuarios as $usuario) {
            $exists = DB::table('pq_permiso')
                ->where($permisoRolCol, $rolId)
                ->where($permisoEmpresaCol, $empresaId)
                ->where($permisoUsuarioCol, $usuario->id)
                ->exists();

            if (!$exists) {
                $data = [
                    $permisoRolCol => $rolId,
                    $permisoEmpresaCol => $empresaId,
                    $permisoUsuarioCol => $usuario->id,
                ];
                if (Schema::hasColumn('pq_permiso', 'created_at')) {
                    $data['created_at'] = now();
                    $data['updated_at'] = now();
                }
                DB::table('pq_permiso')->insert($data);
            }
        }
    }
}
