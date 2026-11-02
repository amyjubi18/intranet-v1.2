<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadMedida extends Model
{
    use HasFactory;
    protected $fillable =['unidad_medida'];
    public function inventario_oficinas()
    {
        return $this->hasMany(InventarioOficina::class, 'id');
    }
    public function salida_oficinas()
    {
        return $this->hasMany(SalidaOficina::class, 'id');
    }
    public function articulos()
    {
        return $this->hasMany(Articulo::class);
    }
}
