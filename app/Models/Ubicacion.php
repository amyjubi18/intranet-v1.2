<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ubicacion extends Model
{
    use HasFactory;
    protected $fillable =['ubicacion','id'];

    public function inventario_oficinas()
    {
        return $this->hasMany(InventarioOficina::class, 'id');
    }
    
}
