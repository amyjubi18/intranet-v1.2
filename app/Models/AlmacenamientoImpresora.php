<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class AlmacenamientoImpresora extends Model
{
    use HasFactory;
    use LogsActivity;
    protected $table = 'almacenamiento_impresoras';
    protected $fillable = [
        'fecha_ingreso',
        'colore_id',
        'cartucho_id',
        'impresora_id',
        'proveedore_id',
        'fabricante_id',
        'unidades',
        'unidades_disponibles',
        'n_factura',
        'user_id',
    ];

    public function colores()
    {
        return $this->belongsTo(Colore::class, 'colore_id');
    }

    public function cartuchos()
    {
        return $this->belongsTo(Cartucho::class, 'cartucho_id');
    }

    public function impresoras()
    {
        return $this->belongsTo(Impresora::class, 'impresora_id');
    }

    public function fabricantes()
    {
        return $this->belongsTo(Fabricante::class, 'fabricante_id');
    }

    public function proveedores()
    {
        return $this->belongsTo(Proveedore::class, 'proveedore_id');
    }

    public function unidadesDisponibles()
    {
        return self::calcularUnidadesDisponibles(
            $this->cartucho_id,
            $this->colore_id,
            $this->fabricante_id,
        );
    }

    public static function calcularUnidadesDisponibles($cartuchoId, $coloreId, $fabricanteId)
    {
        $results = DB::table('almacenamiento_impresoras')
            ->select(DB::raw('SUM(unidades_disponibles) AS total_unidades_disponible'))
            ->where('cartucho_id', '=', $cartuchoId)
            ->where('colore_id', '=', $coloreId)
            ->where('fabricante_id', '=', $fabricanteId)
            ->first();

        return $results ? $results->total_unidades_disponible : 0;
    }

    public function distribuciones()
    {
        return $this->hasMany(DistribucionAlmacenamientoImpresora::class, 'almacenamiento_impresora_id');
    }

    public function users()
        {
            return $this->belongsTo(User::class, 'user_id');
        }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['fecha_ingreso', 'colore_id', 'cartucho_id', 'impresora_id', 'proveedore_id', 'fabricante_id', 'unidades', 'unidades_disponibles', 'n_factura'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function getStockMinAttribute()
    {
        return $this->cartuchos ? $this->cartuchos->stock_min : null;
    }
}
