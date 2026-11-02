<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadAdministrativa extends Model
{
    use HasFactory;
    protected $fillable =['name'];
    public function bienes()
    {
        return $this->hasMany(Biene::class, 'unidad_administrativa_id');
    }
    public function usuario_de_uso()
    {
        return $this->hasMany(UsuarioDeUso::class, 'id');
    }

    public function usuario_administrativo()
    {
        return $this->hasMany(UsuarioAdministrativo::class, 'id');
    }
}
