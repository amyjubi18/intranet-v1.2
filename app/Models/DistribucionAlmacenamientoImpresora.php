<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class DistribucionAlmacenamientoImpresora extends Model
{
    use HasFactory, SoftDeletes;
    use LogsActivity;

    protected $table = 'distribucion_almacenamiento_impresoras';

    protected $fillable = [
        'almacenamiento_impresora_id',
        'gerencia_id',
        'unidades_asignadas',
        'unidades_asignadas_originales',
        'unidades_restantes',
        'user_id',
    ];

    protected $casts = [
        //
    ];

    public function almacenamientoImpresora()
    {
        return $this->belongsTo(AlmacenamientoImpresora::class, 'almacenamiento_impresora_id');
    }

    public function gerencia()
    {
        return $this->belongsTo(Gerencia::class, 'gerencia_id');
    }

    public function area()
    {
        return $this->hasOneThrough(Area::class, Gerencia::class, 'id', 'id', 'gerencia_id', 'area_id');
    }

    public function users()
        {
            return $this->belongsTo(User::class, 'user_id');
        }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['almacenamiento_impresora_id', 'gerencia_id', 'unidades_asignadas', 'unidades_asignadas_originales', 'unidades_restantes'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function getStockMinAttribute()
    {
        return $this->almacenamientoImpresora && $this->almacenamientoImpresora->cartuchos ? $this->almacenamientoImpresora->cartuchos->stock_min : null;
    }
}
