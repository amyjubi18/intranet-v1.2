<?php

namespace Database\Seeders;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleHasPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Admin
        $admin_permissions = Permission::all();
        Role::findOrFail(1)->permissions()->sync($admin_permissions->pluck('id'));

        //Gtic
        $gtic_permissions = $admin_permissions->filter(function($permission){
            return substr($permission->name, 0, 11) != 'documentos_'  && substr($permission->name,0,11 ) != 'categorias_';
        });
        Role::findOrFail(2)->permissions()->sync($gtic_permissions);
        //Gerente-Admin
        $gerente_admin_permissions = $admin_permissions->filter(function($permission){
            return substr($permission->name, 0, 5) != 'user_' && substr($permission->name,0,5 ) != 'role_' && substr($permission->name,0,11 ) != 'permission_' && substr($permission->name,0,11 ) != 'documentos_' && substr($permission->name,0,6 ) != 'color_'  && substr($permission->name,0,7 ) != 'bienes_' && substr($permission->name,0,10 ) != 'condicion_' && substr($permission->name,0,11 ) != 'categorias_' && substr($permission->name,0,15 ) != 'usuario_de_uso_' && substr($permission->name,0,23 ) != 'usuario_administrativo_' && substr($permission->name,0,16 ) != 'forma_adquisicion_' && substr($permission->name,0,14 ) != 'sub_categoria_' && substr($permission->name,0,15 ) != 'unidad_adminis_' ;
        });
        Role::findOrFail(3)->permissions()->sync($gerente_admin_permissions);

        //Administracion
        $administracion_permissions = $admin_permissions->filter(function($permission){
            return substr($permission->name, 0, 5) != 'user_' && substr($permission->name,0,5 ) != 'role_' && substr($permission->name,0,11 ) != 'permission_' && substr($permission->name,0,11 ) != 'categorias_';
        });
        Role::findOrFail(4)->permissions()->sync($administracion_permissions);

         //Bienes Publicos
         $bienes_permissions = $admin_permissions->filter(function($permission){
            return substr($permission->name, 0, 5) != 'user_' && substr($permission->name,0,5 ) != 'role_' && substr($permission->name,0,11 ) != 'permission_'&& substr($permission->name,0,6 ) != 'color_'  && substr($permission->name,0,7 ) != 'bienes_' && substr($permission->name,0,10 ) != 'condicion_' && substr($permission->name,0,15 ) != 'usuario_de_uso_' && substr($permission->name,0,23 ) != 'usuario_administrativo_' && substr($permission->name,0,16 ) != 'forma_adquisicion_' && substr($permission->name,0,14 ) != 'sub_categoria_' && substr($permission->name,0,15 ) != 'unidad_adminis_';
        });
        Role::findOrFail(5)->permissions()->sync($bienes_permissions);

    }
}
