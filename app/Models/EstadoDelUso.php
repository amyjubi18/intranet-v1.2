<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstadoDelUso extends Model
{
    use HasFactory;
    protected $fillable =['estado_del_uso'];
    public function bienes()
    {
        return $this->hasMany(Biene::class, 'estado_del_uso_id');
    }
}
