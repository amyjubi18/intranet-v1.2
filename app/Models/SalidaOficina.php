<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class SalidaOficina extends Model
{
    use HasFactory;
    use LogsActivity;
    protected $table ='salida_oficinas';
    protected $fillable = [

        'fecha_salida',
        'unidad_medida_id',
        'articulo_id',
        'inventario_oficina_id',
        'cantidad',
        'gerencia_id',
        'area_id',
        'historico_unidades_disponibles',
        'ajuste_inventario',
        'user_id'
        ];


        public function inventario_oficinas()
        {
            return $this->belongsTo(InventarioOficina::class, 'inventario_oficina_id' );
        }
        public function unidad_medidas()
        {
            return $this->belongsTo(UnidadMedida::class, 'unidad_medida_id' );
        }
        public function articulos()
        {
            return $this->belongsTo(Articulo::class, 'articulo_id' );
        }
        public function gerencias()
        {
            return $this->belongsTo(Gerencia::class, 'gerencia_id' );
        }
        public function areas()
        {
            return $this->belongsTo(Area::class, 'area_id' );
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
                ->logOnly(['fecha_salida', 'unidad_medida_id', 'articulo_id', 'inventario_oficina_id', 'cantidad', 'gerencia_id', 'area_id', 'historico_unidades_disponibles', 'ajuste_inventario'])
                ->logOnlyDirty()
                ->dontSubmitEmptyLogs();
        }
}
