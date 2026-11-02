<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the user that owns the password history.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

