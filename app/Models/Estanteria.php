<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estanteria extends Model
{
    use HasFactory;

    protected $table = 'estanterias';

    protected $fillable = [
        'nombre',
        'imagen',
        'user_id'
    ];

    /**
     * Relación con el usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con los documentos
     */
    public function documentos()
    {
        return $this->hasMany(Documento::class);
    }
}

