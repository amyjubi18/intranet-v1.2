<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use App\Models\PasswordHistory;
use App\Models\LoginActivity;

class User extends Authenticatable
{


    use HasApiTokens, HasFactory, Notifiable;
     use HasRoles;
     use LogsActivity;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'lastname',
        'username',
        'email',
        'password',
        'cedula',
        'fecha_nacimiento',
        'departamento',
        'profile_photo',
        'status',
        'is_active',

        // Security fields
        'password_changed_at',
        'password_expires_at',
        'session_token',
        'locked_until',
        'failed_login_attempts',
        'two_factor_secret',
        'two_factor_enabled',
        'two_factor_confirmed_at',
        'last_login_ip',
        'last_login_at',
        'last_login_device',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'fecha_nacimiento' => 'date',
        'status' => 'boolean',
        'is_active' => 'boolean',
        'password_changed_at' => 'datetime',
        'password_expires_at' => 'datetime',
        'locked_until' => 'datetime',
        'two_factor_enabled' => 'boolean',
        'two_factor_confirmed_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    // ==================== Security Methods ====================

    /**
     * Check if the account is locked.
     */
    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    /**
     * Lock the account for a given duration.
     */
    public function lock(int $minutes = 30): void
    {
        $this->locked_until = now()->addMinutes($minutes);
        $this->failed_login_attempts = 0;
        $this->save();
    }

    /**
     * Unlock the account.
     */
    public function unlock(): void
    {
        $this->locked_until = null;
        $this->failed_login_attempts = 0;
        $this->save();
    }

    /**
     * Increment failed login attempts.
     */
    public function incrementFailedLoginAttempts(): void
    {
        $this->increment('failed_login_attempts');

        $maxAttempts = config('security.lockout.max_attempts', 5);
        if ($this->failed_login_attempts >= $maxAttempts) {
            $this->lock(config('security.lockout.duration_minutes', 30));
        }
    }

    /**
     * Reset failed login attempts.
     */
    public function resetFailedLoginAttempts(): void
    {
        $this->failed_login_attempts = 0;
        $this->save();
    }

    /**
     * Check if password has expired.
     */
    public function isPasswordExpired(): bool
    {
        $expirationDays = config('security.password.expiration_days', 90);
        if (!$expirationDays || !$this->password_changed_at) {
            return false;
        }

        return $this->password_changed_at->addDays($expirationDays)->isPast();
    }

    /**
     * Update password and save history.
     */
    public function updatePassword(string $newPassword): bool
    {
        // Save old password to history
        $historyCount = config('security.password.history_count', 5);
        $this->passwordHistory()->create([
            'password' => $this->password,
        ]);

        // Keep only last N passwords
        $this->passwordHistory()
            ->orderBy('created_at', 'desc')
            ->skip($historyCount)
            ->take(PHP_INT_MAX)
            ->delete();

        // Update password
        $this->password = Hash::make($newPassword);
        $this->password_changed_at = now();
        $this->password_expires_at = now()->addDays(config('security.password.expiration_days', 90));

        return $this->save();
    }

    /**
     * Record successful login.
     */
    public function recordLogin(string $ip, string $userAgent): void
    {
        $device = $this->parseDevice($userAgent);
        $location = $this->getLocationFromIp($ip);

        $this->update([
            'last_login_ip' => $ip,
            'last_login_at' => now(),
            'last_login_device' => $device,
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);

        // Log login activity
        LoginActivity::create([
            'user_id' => $this->id,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'device' => $device,
            'location' => $location,
            'status' => 'success',
        ]);
    }

    /**
     * Record failed login attempt.
     */
    public function recordFailedLogin(string $ip, string $userAgent, string $reason = null): void
    {
        $this->incrementFailedLoginAttempts();

        LoginActivity::create([
            'user_id' => $this->id,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'device' => $this->parseDevice($userAgent),
            'status' => 'failed',
            'failure_reason' => $reason,
        ]);
    }

    /**
     * Check if this is a new device login.
     */
    public function isNewDeviceLogin(string $ip, string $userAgent): bool
    {
        $recentLogins = LoginActivity::where('user_id', $this->id)
            ->success()
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return $recentLogins === 0;
    }

    /**
     * Parse device info from user agent.
     */
    protected function parseDevice(string $userAgent): string
    {
        if (preg_match('/(mobile|tablet|android|iphone|ipad)/i', $userAgent, $matches)) {
            return ucfirst(strtolower($matches[1]));
        }

        return 'Desktop';
    }

    /**
     * Get location from IP (placeholder - implement with actual service).
     */
    protected function getLocationFromIp(string $ip): string
    {
        // TODO: Implement with IP geolocation service
        return 'Unknown';
    }

    /**
     * Check if user should change password.
     */
    public function shouldChangePassword(): bool
    {
        if (!$this->password_expires_at) {
            return false;
        }

        $warningDays = 7;
        return $this->password_expires_at->subDays($warningDays)->isPast();
    }

    /**
     * Get days until password expires.
     */
    public function getDaysUntilPasswordExpiration(): ?int
    {
        if (!$this->password_expires_at) {
            return null;
        }

        return now()->diffInDays($this->password_expires_at, false);
    }

    // ==================== Relationships ====================

    /**
     * Password history relationship.
     */
    public function passwordHistory()
    {
        return $this->hasMany(PasswordHistory::class);
    }

    /**
     * Login activities relationship.
     */
    public function loginActivities()
    {
        return $this->hasMany(LoginActivity::class);
    }

    /**
     * Get recent login activities.
     */
    public function recentLoginActivities(int $days = 30)
    {
        return $this->loginActivities()
            ->where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at', 'desc');
    }

    // ==================== Existing Methods ====================

    public function getFullNameAttribute()
    {
        return $this->name . ' ' . $this->username;
    }

    /**
     * Verifica si el perfil del usuario está incompleto
     */
    public function getPerfilIncompletoAttribute()
    {
        $camposRequeridos = ['lastname', 'cedula', 'fecha_nacimiento', 'departamento'];
        $camposFaltantes = [];

        foreach ($camposRequeridos as $campo) {
            if (empty($this->{$campo})) {
                $camposFaltantes[] = $campo;
            }
        }

        return [
            'incompleto' => !empty($camposFaltantes),
            'campos' => $camposFaltantes,
            'total' => count($camposFaltantes)
        ];
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucfirst($value);
    }
    public function documentos()
    {
        return $this->hasMany(Documento::class);
    }
    public function digitalizaciones()
    {
        return $this->hasMany(Digitalizacione::class);
    }
    /* public function bienes()
    {
        return $this->hasMany(Bienes::class);
    } */

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // ==================== Birthday Methods ====================

    /**
     * Get users with birthday in the current month
     */
    public static function getBirthdaysForCurrentMonth()
    {
        $currentMonth = now()->month;

        return self::whereNotNull('fecha_nacimiento')
            ->whereMonth('fecha_nacimiento', $currentMonth)
            ->where('is_active', true)
            ->orderByRaw('DAY(fecha_nacimiento)')
            ->get();
    }

    /**
     * Check if user has birthday this month
     */
    public function hasBirthdayThisMonth(): bool
    {
        if (!$this->fecha_nacimiento) {
            return false;
        }
        return $this->fecha_nacimiento->month === now()->month;
    }

    /**
     * Get birthday display date (day and month name)
     */
    public function getBirthdayDisplayDateAttribute(): string
    {
        if (!$this->fecha_nacimiento) {
            return '';
        }

        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        return $this->fecha_nacimiento->day . ' de ' . $meses[$this->fecha_nacimiento->month];
    }

    /**
     * Get birthday day of month
     */
    public function getBirthdayDayAttribute(): int
    {
        return $this->fecha_nacimiento ? $this->fecha_nacimiento->day : 0;
    }

    /**
     * Get all birthdays for calendar (all months)
     */
    public static function getAllBirthdaysForCalendar()
    {
        return self::whereNotNull('fecha_nacimiento')
            ->where('is_active', true)
            ->select('id', 'name', 'lastname', 'departamento', 'fecha_nacimiento')
            ->get()
            ->groupBy(function($user) {
                return $user->fecha_nacimiento->month;
            });
    }

    /**
     * Get initials for avatar
     */
    public function getInitialsAttribute(): string
    {
        $name = substr($this->name, 0, 1);
        $lastname = substr($this->lastname ?? '', 0, 1);
        return strtoupper($name . $lastname);
    }

    /**
     * Get days until next birthday
     */
    public function getDaysUntilBirthdayAttribute(): ?int
    {
        if (!$this->fecha_nacimiento) {
            return null;
        }

        $birthday = now()->year . '-' .
                    str_pad($this->fecha_nacimiento->month, 2, '0', STR_PAD_LEFT) . '-' .
                    str_pad($this->fecha_nacimiento->day, 2, '0', STR_PAD_LEFT);

        $birthdayDate = now()->parse($birthday);

        if ($birthdayDate->isPast()) {
            $birthdayDate = $birthdayDate->addYear();
        }

        return now()->diffInDays($birthdayDate, false);
    }
}
