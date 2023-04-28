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

            'documentos_index',
            'documentos_create',
            'documentos_edit',
            'documentos_destroy',

            

            'categorias_index',
            'categorias_create',
            'categorias_edit',
            'categorias_destroy',
        ];
        foreach ($permissions as $permission) {
            Permission::create([
                'name'=>$permission
            ]);
        }
    }
}
