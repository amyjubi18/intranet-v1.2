<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Notifications\ResetPassword;
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
        Gate::define('oga', function ($user) {
            $roles = $user->roles->pluck('name')->map(fn($r) => strtolower($r))->toArray();
            $adminRoles = ['admin', 'super-admin', 'gerente-admin', 'administración', 'administracion', 'bienes-publicos', 'gerente', 'coordinacion'];
            if (count(array_intersect($roles, $adminRoles)) > 0) {
                return true;
            }
            $userPerms = $user->getAllPermissions()->pluck('name')->toArray();
            $ogaPerms = [
                'oga', 'bienes_index', 'documentos_index', 'inventarioofic_index',
                'salidaofic_index', 'almacen_inv_index', 'dist_almac_index',
                'salidaimpre_index', 'cartucho_index', 'impresora_index', 'categorias_index', 'estanterias_index'
            ];
            return count(array_intersect($userPerms, $ogaPerms)) > 0;
        });

        // 3. Gate de Módulo Gestión Humana (OGH)
        Gate::define('ogh', function ($user) {
            $roles = $user->roles->pluck('name')->map(fn($r) => strtolower($r))->toArray();
            $ghRoles = ['admin', 'super-admin', 'gerente_ogh', 'gerente-ogh', 'coordinacion_ogh', 'coordinacion'];
            if (count(array_intersect($roles, $ghRoles)) > 0) {
                return true;
            }
            $userPerms = $user->getAllPermissions()->pluck('name')->toArray();
            $ghPerms = ['ogh', 'area_adscrita_index', 'tipo_documento_index', 'recibos_pago', 'constancias_trabajo'];
            return count(array_intersect($userPerms, $ghPerms)) > 0;
        });

        // 4. Gate de Módulo GTIC
        Gate::define('gtic', function ($user) {
            $roles = $user->roles->pluck('name')->map(fn($r) => strtolower($r))->toArray();
            $gticRoles = ['admin', 'super-admin', 'gtic'];
            if (count(array_intersect($roles, $gticRoles)) > 0) {
                return true;
            }
            $userPerms = $user->getAllPermissions()->pluck('name')->toArray();
            $gticPerms = ['gtic', 'user_index', 'role_index', 'permission_index', 'activity_log_index'];
            return count(array_intersect($userPerms, $gticPerms)) > 0;
        });

        // 5. Gates de soporte para submódulos de OGA (evita que un usuario con permisos secundarios quede bloqueado)
        Gate::define('documentos', function ($user) {
            $roles = $user->roles->pluck('name')->map(fn($r) => strtolower($r))->toArray();
            if (count(array_intersect($roles, ['admin', 'super-admin', 'gerente-admin', 'administración', 'administracion'])) > 0) {
                return true;
            }
            $userPerms = $user->getAllPermissions()->pluck('name')->toArray();
            return count(array_intersect($userPerms, ['documentos', 'documentos_index', 'categorias_index', 'estanterias_index'])) > 0;
        });

        Gate::define('bienes', function ($user) {
            $roles = $user->roles->pluck('name')->map(fn($r) => strtolower($r))->toArray();
            if (count(array_intersect($roles, ['admin', 'super-admin', 'gerente-admin', 'administración', 'administracion', 'bienes-publicos'])) > 0) {
                return true;
            }
            $userPerms = $user->getAllPermissions()->pluck('name')->toArray();
            return count(array_intersect($userPerms, ['bienes', 'bienes_index', 'unidad_adminis_index', 'forma_adquisicion_index', 'color_index', 'condicion_index'])) > 0;
        });

        Gate::define('inv-oficina', function ($user) {
            $roles = $user->roles->pluck('name')->map(fn($r) => strtolower($r))->toArray();
            if (count(array_intersect($roles, ['admin', 'super-admin', 'gerente-admin', 'administración', 'administracion'])) > 0) {
                return true;
            }
            $userPerms = $user->getAllPermissions()->pluck('name')->toArray();
            return count(array_intersect($userPerms, ['inv-oficina', 'inventarioofic_index', 'salidaofic_index', 'articulo_index', 'proveedor_index'])) > 0;
        });

        Gate::define('inv-consumibles', function ($user) {
            $roles = $user->roles->pluck('name')->map(fn($r) => strtolower($r))->toArray();
            if (count(array_intersect($roles, ['admin', 'super-admin', 'gerente-admin', 'administración', 'administracion'])) > 0) {
                return true;
            }
            $userPerms = $user->getAllPermissions()->pluck('name')->toArray();
            return count(array_intersect($userPerms, ['inv-consumibles', 'almacen_inv_index', 'dist_almac_index', 'salidaimpre_index', 'cartucho_index', 'impresora_index'])) > 0;
        });
    }
}
