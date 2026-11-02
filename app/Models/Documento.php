<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    use HasFactory;
    protected $table ='documentos';
    protected $fillable = ['name', 'description', 'documento', 'fecha', 'user_id', 'estanteria_id', 'categoria_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con los archivos del documento
     */
    public function archivos()
    {
        return $this->hasMany(DocumentoArchivo::class);
    }

    /**
     * Relación con la estantería
     */
    public function estanteria()
    {
        return $this->belongsTo(Estanteria::class);
    }

    /**
     * Relación con la categoría
     */
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }
}

