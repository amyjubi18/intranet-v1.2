<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedore extends Model
{
    use HasFactory;
    protected $fillable =['proveedor'];
    public function inventario_oficinas()
    {
        return $this->hasMany(InventarioOficina::class, 'id');
    }
    public function inventario_impresoras()
    {
        return $this->hasMany(InventarioImpresora::class, 'id');
    }
}
