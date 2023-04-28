<?php

namespace App\Providers;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

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
       /*  Carbon::setLocale('es');
        setLocale(LC_TIME, 'es_ES');
        Carbon::setUtf8(true); */
    }
}
