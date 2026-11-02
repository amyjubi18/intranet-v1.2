<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'device',
        'location',
        'status',
        'failure_reason',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the user that owns the login activity.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for successful logins.
     */
    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope for failed logins.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for recent activity.
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Check if this was from a new device/location.
     */
    public function isNewDevice(): bool
    {
        if (!$this->user) {
            return true;
        }

        $recentActivity = static::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->success()
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return $recentActivity === 0;
    }
}

