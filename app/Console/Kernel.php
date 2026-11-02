<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Security: Notificar usuarios con contraseña por expirar
        $schedule->command('security:notify-password-expiration')
                 ->dailyAt('09:00')
                 ->withoutOverlapping()
                 ->runInBackground();

        // Security: Verificar contraseñas expiradas
        $schedule->command('security:check-expired-passwords')
                 ->dailyAt('08:00')
                 ->withoutOverlapping();

        // Security: Limpiar actividad antigua
        $schedule->command('security:clean-activity')
                 ->weekly()
                 ->sundays()
                 ->at('03:00')
                 ->withoutOverlapping();

        // Security: Desbloquear cuentas expiradas
        $schedule->command('security:unlock-accounts')
                 ->daily()
                 ->at('00:00')
                 ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
