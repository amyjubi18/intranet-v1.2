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
            return substr($permission->name, 0, 5) != 'user_' && substr($permission->name,0,5 ) != 'role_' && substr($permission->name,0,11 ) != 'permission_' && substr($permission->name,0,11 ) != 'documentos_';
        });
        Role::findOrFail(3)->permissions()->sync($gerente_admin_permissions);

        //Administracion
        $administracion_permissions = $admin_permissions->filter(function($permission){
            return substr($permission->name, 0, 5) != 'user_' && substr($permission->name,0,5 ) != 'role_' && substr($permission->name,0,11 ) != 'permission_' && substr($permission->name,0,11 ) != 'categorias_';
        });
        Role::findOrFail(4)->permissions()->sync($administracion_permissions);

    }
}
