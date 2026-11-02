<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class SalidaAlmacenamientoImpresora extends Model
{
    use HasFactory;
    use LogsActivity;
    protected $table = 'salida_almacenamiento_impresoras';
    protected $fillable = [
        'fecha_salida',
        'almacenamiento_impresora_id',
        'distribucion_almacenamiento_impresora_id',
        'cantidad',
        'historico_unidades_disponibles',
        'historico_unidades_asignadas_originales',
        'historico_unidades_asignadas_restantes',
        'ajuste_inventario',
        'user_id',
    ];

    public function almacenamientoImpresora()
    {
        return $this->belongsTo(AlmacenamientoImpresora::class, 'almacenamiento_impresora_id');
    }

    public function distribucionAlmacenamientoImpresora()
    {
        return $this->belongsTo(DistribucionAlmacenamientoImpresora::class, 'distribucion_almacenamiento_impresora_id');
    }

    protected function totalDisponible(): Attribute
    {
        return Attribute::make(
            get: fn () => abs($this->historico_unidades_disponibles - $this->cantidad),
        );
    }

    public function users()
        {
            return $this->belongsTo(User::class, 'user_id');
        }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['fecha_salida', 'almacenamiento_impresora_id', 'distribucion_almacenamiento_impresora_id', 'cantidad', 'historico_unidades_disponibles', 'historico_unidades_asignadas_originales', 'historico_unidades_asignadas_restantes', 'ajuste_inventario'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function getStockMinAttribute()
    {
        return $this->almacenamientoImpresora && $this->almacenamientoImpresora->cartuchos ? $this->almacenamientoImpresora->cartuchos->stock_min : null;
    }
}
