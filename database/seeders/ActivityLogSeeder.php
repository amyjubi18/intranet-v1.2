<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class ActivityLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear permisos para el registro de actividades
        Permission::firstOrCreate(['name' => 'activity_log_index']);
        Permission::firstOrCreate(['name' => 'activity_log_show']);
    }
}
