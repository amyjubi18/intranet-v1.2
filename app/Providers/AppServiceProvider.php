<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\User;
use App\Observers\UserObserver;

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
        // Registrar el ViewComposer para notificaciones
        View::composer('layouts.app', \App\Http\View\Composers\NotificationComposer::class);

        // Registrar observers de modelos para seguridad
        User::observe(UserObserver::class);

       /*  Carbon::setLocale('es');
        setLocale(LC_TIME, 'es_ES');
        Carbon::setUtf8(true); */
    }
}
