<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class SalidaImpresora extends Model
{
    use HasFactory;
    use LogsActivity;
    protected $table ='salida_impresoras';
    protected $fillable = [

        'fecha_salida',
        'cartucho_id',
        'colore_id',
        'impresora_id',
        'inventario_impresora_id',
        'cantidad',
        'gerencia_id',
        'area_id',
        'historico_unidades_disponibles',
        'ajuste_inventario',
        'fabricante_id'
        ];


        public function inventario_impresoras()
        {
            return $this->belongsTo(InventarioImpresora::class, 'inventario_impresora_id' );
        }
        public function cartuchos()
        {
            return $this->belongsTo(Cartucho::class, 'cartucho_id' );
        }
        public function colores()
        {
            return $this->belongsTo(Colore::class, 'colore_id' );
        }
        public function impresoras()
        {
            return $this->belongsTo(Impresora::class, 'impresora_id' );
        }
        public function gerencias()
        {
            return $this->belongsTo(Gerencia::class, 'gerencia_id' );
        }
        public function areas()
        {
            return $this->belongsTo(Area::class, 'area_id' );
        }
        public function fabricantes()
        {
            return $this->belongsTo(Fabricante::class, 'fabricante_id' );
        }
        protected function totalDisponible(): Attribute
        {
            return Attribute::make(
                get: fn () => abs($this->historico_unidades_disponibles - $this->cantidad),
            );
        }

        public function getActivitylogOptions(): LogOptions
        {
            return LogOptions::defaults()
                ->logOnly(['fecha_salida', 'cartucho_id', 'colore_id', 'impresora_id', 'inventario_impresora_id', 'cantidad', 'gerencia_id', 'area_id', 'historico_unidades_disponibles', 'ajuste_inventario', 'fabricante_id'])
                ->logOnlyDirty()
                ->dontSubmitEmptyLogs();
        }
}
