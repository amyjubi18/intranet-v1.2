<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gerencia extends Model
{
    use HasFactory;
    protected $fillable =['gerencia','id', 'area_id'];

    public function inventario_oficinas()
    {
        return $this->hasMany(InventarioOficina::class, 'gerencia_id');
    }
    public function inventario_impresoras()
    {
        return $this->hasMany(InventarioImpresora::class, 'gerencia_id');
    }
    public function salida_oficinas()
    {
        return $this->hasMany(SalidaOficina::class, 'gerencia_id');
    }
    public function salida_impresoras()
    {
        return $this->hasMany(SalidaImpresora::class, 'gerencia_id');
    }
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id' );
    }
    public function contratados()
    {
        return $this->hasMany(Contratados::class, 'id');
    }
}
