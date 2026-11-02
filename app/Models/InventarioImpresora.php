<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class InventarioImpresora extends Model
{
    use HasFactory;
    use LogsActivity;
    protected $table ='inventario_impresoras';
    protected $fillable = [

        'fecha_ingreso',
        'colore_id',
        'cartucho_id',
        'impresora_id',
        'proveedore_id',
        'gerencia_id',
        'unidades',
        'area_id',
        'fabricante_id',
        'unidades_disponibles',
        'n_factura',
        'user_id',

        ];
        public function colores()
        {
            return $this->belongsTo(Colore::class, 'colore_id' );
        }
        public function cartuchos()
        {
            return $this->belongsTo(Cartucho::class, 'cartucho_id' );
        }
        public function impresoras()
        {
            return $this->belongsTo(Impresora::class, 'impresora_id' );
        }
        public function fabricantes()
        {
            return $this->belongsTo(Fabricante::class, 'fabricante_id' );
        }
        public function areas()
        {
            return $this->belongsTo(Area::class, 'area_id' );
        }
        public function gerencias()
        {
            return $this->belongsTo(Gerencia::class, 'gerencia_id' );
        }
        public function proveedores()
        {
            return $this->belongsTo(Proveedore::class, 'proveedore_id' );
        }


        public function unidadesDisponibles()
        {
            return self::calcularUnidadesDisponibles(
                $this->cartucho_id,
                $this->colore_id,
                $this->fabricante_id,
            );
        }

          public static function calcularUnidadesDisponibles($cartuchoId, $coloreId, $fabricanteId) {
            $results = DB::table('inventario_impresoras')
                ->select(DB::raw('SUM(unidades_disponibles) AS total_unidades_disponible'))
                ->where('cartucho_id', '=', $cartuchoId)
                ->where('colore_id', '=', $coloreId)
                ->where('fabricante_id', '=', $fabricanteId)
                ->first();

            return $results ? $results->total_unidades_disponible : 0;
        }

        public function user()
        {
            return $this->belongsTo(User::class, 'user_id');
        }

        public function getActivitylogOptions(): LogOptions
        {
            return LogOptions::defaults()
                ->logOnly(['fecha_ingreso', 'colore_id', 'cartucho_id', 'impresora_id', 'proveedore_id', 'gerencia_id', 'unidades', 'area_id', 'fabricante_id', 'unidades_disponibles', 'n_factura'])
                ->logOnlyDirty()
                ->dontSubmitEmptyLogs();
        }
}
