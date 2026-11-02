<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioAdministrativo extends Model
{
    use HasFactory;
    protected $table ='usuario_administrativos';
    protected $fillable =['usuario_administrativo',
    'unidad_administrativa_id'];

    public function bienes()
    {
        return $this->hasMany(Biene::class, 'usuario_administrativo_id');
    }
    public function unidad_administrativa()
    {
        return $this->belongsTo(UnidadAdministrativa::class);
    }

}
