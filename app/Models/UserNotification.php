<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'notification_type',
        'read_at',
        'hash'
    ];

    protected $casts = [
        'read_at' => 'datetime'
    ];

    /**
     * Relación con el usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para notificaciones no leídas
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope para notificaciones leídas
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Marcar como leída
     */
    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    /**
     * Verificar si está leída
     */
    public function isRead()
    {
        return $this->read_at !== null;
    }

    /**
     * Verificar si el hash coincide (para detectar cambios en la notificación)
     */
    public function isStillValid($currentHash)
    {
        return $this->hash === $currentHash;
    }
}

