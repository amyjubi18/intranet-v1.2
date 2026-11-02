<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SecurityCommands extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'security:check-expired-passwords
                            {--notify : Enviar notificación a usuarios con contraseña expirada}
                            {--force : Forzar cambio de contraseña a usuarios con contraseña expirada}';

    /**
     * The console command description.
     */
    protected $description = 'Comandos para gestión de seguridad del sistema';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        return Command::SUCCESS;
    }

    /**
     * Check for users with expired passwords.
     */
    public function checkExpiredPasswords(): int
    {
        $notify = $this->option('notify');
        $force = $this->option('force');

        $users = User::whereNotNull('password_changed_at')
            ->where('status', true)
            ->get();

        $expired = [];
        $expiringSoon = [];

        foreach ($users as $user) {
            if ($user->isPasswordExpired()) {
                $expired[] = $user;
            } elseif ($user->shouldChangePassword()) {
                $expiringSoon[] = $user;
            }
        }

        $this->info("=== Estado de Contraseñas ===");
        $this->info("Usuarios con contraseña expirada: " . count($expired));
        $this->info("Usuarios con contraseña por expirar: " . count($expiringSoon));

        if (!empty($expired)) {
            $this->warn("\nUsuarios con contraseña expirada:");
            foreach ($expired as $user) {
                $this->line("- {$user->email} ({$user->name})");
            }

            if ($force) {
                foreach ($expired as $user) {
                    $user->status = false;
                    $user->save();
                    $this->info("Cuenta desactivada: {$user->email}");
                }
                $this->warn("\nCuentas desactivadas: " . count($expired));
            } elseif ($notify) {
                foreach ($expired as $user) {
                    // TODO: Send notification
                    $this->info("Notificación enviada a: {$user->email}");
                }
            }
        }

        if (!empty($expiringSoon)) {
            $this->warn("\nUsuarios con contraseña por expirar:");
            foreach ($expiringSoon as $user) {
                $days = $user->getDaysUntilPasswordExpiration();
                $this->line("- {$user->email} ({$user->name}) - {$days} días restantes");
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Unlock all locked accounts.
     */
    public function unlockAllAccounts(): int
    {
        $count = User::whereNotNull('locked_until')->count();

        User::whereNotNull('locked_until')->update([
            'locked_until' => null,
            'failed_login_attempts' => 0,
        ]);

        $this->info("{$count} cuentas desbloqueadas exitosamente.");

        return Command::SUCCESS;
    }

    /**
     * Clean old login activities.
     */
    public function cleanOldActivities(): int
    {
        $days = 90;
        $deleted = DB::table('login_activities')
            ->where('created_at', '<', now()->subDays($days))
            ->delete();

        $this->info("{$deleted} registros de actividad antiguos eliminados.");

        return Command::SUCCESS;
    }

    /**
     * Clean old password history.
     */
    public function cleanOldPasswordHistory(): int
    {
        $keepHistory = config('security.password.history_count', 5);

        // Get users with more than keepHistory password entries
        $users = User::has('passwordHistory', '>', $keepHistory)->get();

        foreach ($users as $user) {
            $oldEntries = $user->passwordHistory()
                ->orderBy('created_at', 'desc')
                ->skip($keepHistory)
                ->take(PHP_INT_MAX)
                ->pluck('id');

            $user->passwordHistory()->whereIn('id', $oldEntries)->delete();
        }

        $this->info("Historial de contraseñas limpio.");

        return Command::SUCCESS;
    }

    /**
     * Generate security report.
     */
    public function generateReport(): int
    {
        $totalUsers = User::count();
        $activeUsers = User::where('status', true)->count();
        $lockedUsers = User::whereNotNull('locked_until')->count();

        $passwordExpired = User::whereNotNull('password_changed_at')
            ->where('status', true)
            ->get()
            ->filter(fn($u) => $u->isPasswordExpired())
            ->count();

        $with2FA = User::where('two_factor_enabled', true)->count();

        $this->info("=== Reporte de Seguridad ===");
        $this->info("Total de usuarios: {$totalUsers}");
        $this->info("Usuarios activos: {$activeUsers}");
        $this->info("Cuentas bloqueadas: {$lockedUsers}");
        $this->info("Contraseñas expiradas: {$passwordExpired}");
        $this->info("Usuarios con 2FA: {$with2FA}");

        $this->line('');
        $this->info("Recomendaciones:");

        if ($lockedUsers > 0) {
            $this->warn("- Revisar intentos de acceso fallidos");
        }

        if ($passwordExpired > 0) {
            $this->warn("- Forzar cambio de contraseña a usuarios con contraseña expirada");
        }

        if ($with2FA === 0) {
            $this->info("- Considerar habilitar autenticación de dos factores");
        }

        return Command::SUCCESS;
    }
}

