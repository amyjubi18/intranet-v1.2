<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fabricante extends Model
{
    use HasFactory;
    protected $fillable =['fabricante'];
    public function salida_impresoras()
    {
        return $this->hasMany(SalidaImpresora::class, 'id');
    }
    public function inventario_impresoras()
    {
        return $this->hasMany(InventarioImpresora::class, 'fabricante_id');
    }
    public function cartuchos()
    {
        return $this->hasMany(Cartucho::class);
    }
    
}
