<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\PasswordExpirationWarning;
use Illuminate\Console\Command;

class NotifyPasswordExpiration extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'security:notify-password-expiration
                            {--days=7 : Días de anticipación para notificar}
                            {--dry-run : No enviar notificaciones, solo mostrar}';

    /**
     * The console command description.
     */
    protected $description = 'Notificar a usuarios con contraseña por expirar';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $daysToNotify = $this->option('days');
        $dryRun = $this->option('dry-run');

        $users = User::where('status', true)
            ->whereNotNull('password_expires_at')
            ->get();

        $notified = 0;
        $alreadyNotified = [];

        foreach ($users as $user) {
            $daysRemaining = now()->diffInDays($user->password_expires_at, false);

            if ($daysRemaining <= $daysToNotify && $daysRemaining > 0) {
                // Check if already notified recently (within last day)
                if ($user->last_password_notification && $user->last_password_notification->isAfter(now()->subDay())) {
                    $alreadyNotified[] = $user->email;
                    continue;
                }

                if ($dryRun) {
                    $this->info("[DRY RUN] Would notify: {$user->email} ({$daysRemaining} days)");
                } else {
                    $user->notify(new PasswordExpirationWarning($daysRemaining));
                    $user->last_password_notification = now();
                    $user->save();
                    $notified++;
                    $this->info("Notificación enviada a: {$user->email}");
                }
            }
        }

        if ($dryRun) {
            $this->info("\n[DRY RUN] Total que serían notificados: {$notified}");
        } else {
            $this->info("\nTotal de notificaciones enviadas: {$notified}");
        }

        if (!empty($alreadyNotified)) {
            $this->warn("Usuarios ya notificados recientemente: " . count($alreadyNotified));
        }

        return Command::SUCCESS;
    }
}

