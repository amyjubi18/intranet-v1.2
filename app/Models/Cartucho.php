<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Cartucho extends Model
{
    use HasFactory;
    use LogsActivity;
    protected $fillable =['cartucho','id', 'colore_id', 'stock_min', 'impresora_id', 'fabricante_id'];

    public function inventario_impresoras()
    {
        return $this->hasMany(InventarioImpresora::class, 'cartucho_id');
    }
    public function salida_impresoras()
    {
        return $this->hasMany(SalidaImpresora::class, 'id');
    }
    public function colore()
    {
        return $this->belongsTo(Colore::class, 'colore_id' );
    }
    public function impresora()
    {
        return $this->belongsTo(Impresora::class, 'impresora_id' );
    }
    public function fabricante()
    {
        return $this->belongsTo(Fabricante::class, 'fabricante_id' );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['cartucho', 'colore_id', 'stock_min', 'impresora_id', 'fabricante_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
