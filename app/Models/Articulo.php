<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Articulo extends Model
{
    use HasFactory;
    use LogsActivity;

    // Indica que el ID no es auto-incrementable
    public $incrementing = false;

    // Indica que el tipo de la clave es string
    protected $keyType = 'string';

    protected $fillable =['articulo','id', 'unidad_medida_id', 'stock_min'];

    public function inventario_oficinas()
    {
        return $this->hasMany(InventarioOficina::class, 'id');
    }
    public function salida_oficinas()
    {
        return $this->hasMany(SalidaOficina::class, 'id');
    }
    public function unidad_medida()
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_medida_id' );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['articulo', 'unidad_medida_id', 'stock_min'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
