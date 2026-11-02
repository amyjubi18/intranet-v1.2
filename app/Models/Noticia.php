<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Noticia extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'titulo',
        'contenido',
        'categoria',
        'imagen',
        'user_id',
        'publicada',
        'fecha_publicacion',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'publicada' => 'boolean',
        'fecha_publicacion' => 'datetime',
    ];

    /**
     * Get the user that owns the noticia.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

