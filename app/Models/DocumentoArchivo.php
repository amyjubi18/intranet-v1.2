<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentoArchivo extends Model
{
    use HasFactory;

    protected $table = 'documento_archivos';

    protected $fillable = [
        'documento_id',
        'nombre_archivo',
        'ruta',
        'categoria_id',
        'user_id'
    ];

    /**
     * Relación con el documento principal
     */
    public function documento()
    {
        return $this->belongsTo(Documento::class);
    }

    /**
     * Relación con la categoría
     */
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    /**
     * Relación con el usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

