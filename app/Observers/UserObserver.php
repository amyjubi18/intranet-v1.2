<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PasswordExpirationWarning;

class UserObserver
{
    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Check if password was changed and update password_expiration
        if ($user->isDirty('password')) {
            $user->password_changed_at = now();
            $user->password_expires_at = now()->addDays(config('security.password.expiration_days', 90));
            $user->saveQuietly();
        }
    }

    /**
     * Handle the User "login" event.
     */
    public function login(User $user, string $provider): void
    {
        // Check password expiration on login
        if ($user->isPasswordExpired()) {
            // Optionally force password change or just notify
            // This will be handled by a middleware or notification
        }
    }

    /**
     * Handle the User "logout" event.
     */
    public function logout(User $user, string $provider): void
    {
        // Clear session token on logout
        $user->session_token = null;
        $user->saveQuietly();
    }
}

