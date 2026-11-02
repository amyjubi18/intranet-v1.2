<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Security Commands
Artisan::command('security:check-expired-passwords', function () {
    $this->call('security:check-expired-passwords', ['--notify' => true]);
})->purpose('Check for users with expired passwords');

// Password expiration notification command
Artisan::command('security:notify-password-expiration', function () {
    $this->info('Notificando usuarios con contraseña por expirar...');
})->purpose('Notify users with password about to expire');
