<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // 1. Super-Admin / Admin Bypass: Administradores tienen acceso a todos los módulos y opciones
        Gate::before(function ($user, $ability) {
            $roles = $user->roles->pluck('name')->map(fn($r) => strtolower($r))->toArray();
            if (in_array('admin', $roles) || in_array('super-admin', $roles)) {
                return true;
            }
            return null; // Deja que las reglas normales de Spatie continúen
        });

        // 2. Gate de Módulo Administración (OGA)
        // Se evalúa dinámicamente según permisos en BD (sin nombres de roles hardcodeados)
        Gate::define('oga', function ($user) {
            $userPerms = $user->getAllPermissions()->pluck('name')->toArray();
            if (in_array('oga', $userPerms)) {
                return true;
            }
            // Permisos específicos que pertenecen exclusivamente al módulo Administración
            $ogaSubPerms = [
                'bienes', 'bienes_index', 'inv-oficina', 'inventarioofic_index',
                'salidaofic_index', 'inv-consumibles', 'cartucho_index', 'salidaimpre_index',
                'categorias_index', 'estanterias_index'
            ];
            return count(array_intersect($userPerms, $ogaSubPerms)) > 0;
        });

        // 3. Gate de Módulo Gestión Humana (OGH)
        // Se evalúa dinámicamente según permisos en BD (sin nombres de roles hardcodeados)
        Gate::define('ogh', function ($user) {
            $userPerms = $user->getAllPermissions()->pluck('name')->toArray();
            if (in_array('ogh', $userPerms)) {
                return true;
            }
            $ghSubPerms = [
                'area_adscrita_index', 'tipo_documento_index', 'digitalizaciones_index',
                'recibos_pago', 'constancias_trabajo'
            ];
            return count(array_intersect($userPerms, $ghSubPerms)) > 0;
        });

        // 4. Gate de Módulo GTIC
        // Se evalúa dinámicamente según permisos en BD (sin nombres de roles hardcodeados)
        Gate::define('gtic', function ($user) {
            $userPerms = $user->getAllPermissions()->pluck('name')->toArray();
            if (in_array('gtic', $userPerms)) {
                return true;
            }
            $gticSubPerms = [
                'user_index', 'role_index', 'permission_index', 'activity_log_index', 'tecnologia'
            ];
            return count(array_intersect($userPerms, $gticSubPerms)) > 0;
        });

        // 5. Gates de soporte para submódulos de Administración (evaluación dinámica por permisos)
        Gate::define('documentos', function ($user) {
            $userPerms = $user->getAllPermissions()->pluck('name')->toArray();
            return count(array_intersect($userPerms, ['documentos', 'documentos_index', 'categorias_index', 'estanterias_index'])) > 0;
        });

        Gate::define('bienes', function ($user) {
            $userPerms = $user->getAllPermissions()->pluck('name')->toArray();
            return count(array_intersect($userPerms, [
                'bienes', 'bienes_index', 'unidad_adminis_index', 'forma_adquisicion_index',
                'color_index', 'condicion_index', 'estado_uso_index', 'usuario_de_uso_index', 'usuario_administrativo_index'
            ])) > 0;
        });

        Gate::define('inv-oficina', function ($user) {
            $userPerms = $user->getAllPermissions()->pluck('name')->toArray();
            return count(array_intersect($userPerms, [
                'inv-oficina', 'inventarioofic_index', 'salidaofic_index', 'articulo_index',
                'proveedor_index', 'unidad_medida_index', 'area_index', 'gerencia_index', 'ubicacion_index'
            ])) > 0;
        });

        Gate::define('inv-consumibles', function ($user) {
            $userPerms = $user->getAllPermissions()->pluck('name')->toArray();
            return count(array_intersect($userPerms, [
                'inv-consumibles', 'cartucho_index', 'colores_index', 'impresora_index',
                'fabricante_index', 'dist_almac_index', 'salidaimpre_index', 'inventarioimpre_index'
            ])) > 0;
        });
    }
}
