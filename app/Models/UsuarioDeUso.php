<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioDeUso extends Model
{
    use HasFactory;
    protected $table ='usuario_de_usos';
    protected $fillable =['usuario_de_uso',
    'unidad_administrativa_id'];

    public function bienes()
    {
        return $this->hasMany(Biene::class, 'usuario_de_uso_id');
    }

    public function unidad_administrativa()
    {
        return $this->belongsTo(UnidadAdministrativa::class );
    }
}
