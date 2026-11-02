<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'permission_index',
            'permission_create',
            'permission_edit',
            'permission_destroy',

            'role_index',
            'role_create',
            'role_edit',
            'role_destroy',

            'user_index',
            'user_create',
            'user_edit',
            'user_destroy',

            'bienes_index',
            'bienes_create',
            'bienes_edit',
            'bienes_destroy',

            'documentos_index',
            'documentos_create',
            'documentos_edit',
            'documentos_destroy',

            'categorias_index',
            'categorias_create',
            'categorias_edit',
            'categorias_destroy',


            'color_index',
            'color_create',
            'color_edit',
            'color_destroy',

            'condicion_index',
            'condicion_create',
            'condicion_edit',
            'condicion_destroy',

            'estado_uso_index',
            'estado_uso_create',
            'estado_uso_edit',
            'estado_uso_destroy',

            'forma_adquisicion_index',
            'forma_adquisicion_create',
            'forma_adquisicion_edit',
            'forma_adquisicion_destroy',



            'unidad_adminis_index',
            'unidad_adminis_create',
            'unidad_adminis_edit',
            'unidad_adminis_destroy',

            'usuario_de_uso_index',
            'usuario_de_uso_create',
            'usuario_de_uso_edit',
            'usuario_de_uso_destroy',

            'usuario_administrativo_index',
            'usuario_administrativo_create',
            'usuario_administrativo_edit',
            'usuario_administrativo_destroy',

            'salidaofic_index',
            'salidaofic_create',
            'salidaofic_edit',
            'salidaofic_destroy',

            // Permisos de inventario impresoras
            'inventarioimpre_index',
            'inventarioimpre_create',
            'inventarioimpre_edit',
            'inventarioimpre_destroy',

            // Permisos de inventario oficina
            'inventarioofic_index',
            'inventarioofic_create',
            'inventarioofic_edit',
            'inventarioofic_destroy',

        ];
        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::firstOrCreate([
                'name'=>$permission
            ]);
        }
    }
}
