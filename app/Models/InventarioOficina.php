<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class InventarioOficina extends Model
{
    use HasFactory;
    use LogsActivity;
    protected $table ='inventario_oficinas';
    protected $fillable = [

        'fecha_ingreso',
        'unidad_medida_id',
        'articulo_id',
        'ubicacion_id',
        'proveedore_id',
        'unidades',
        'unidades_disponibles',
        'n_factura',
        'user_id',

        ];
        public function unidad_medidas()
        {
            return $this->belongsTo(UnidadMedida::class, 'unidad_medida_id' );
        }
        public function articulos()
        {
            return $this->belongsTo(Articulo::class, 'articulo_id' );
        }

        public function proveedores()
        {
            return $this->belongsTo(Proveedore::class, 'proveedore_id' );
        }

        public function ubicacion()
        {
            return $this->belongsTo(Ubicacion::class, 'ubicacion_id' );
        }

        public function unidadesDisponibles()
        {
            return self::calcularUnidadesDisponibles(
                $this->articulo_id,
                $this->unidad_medida_id,
            );
        }

          public static function calcularUnidadesDisponibles($articuloId, $unidadMedidaId) {
            $results = DB::table('inventario_oficinas')
                ->select(DB::raw('SUM(unidades_disponibles) AS total_unidades_disponible'))
                ->where('articulo_id', '=', $articuloId)
                ->where('unidad_medida_id', '=', $unidadMedidaId)
                ->first();

            return $results ? $results->total_unidades_disponible : 0;
        }

        public function users()
        {
            return $this->belongsTo(User::class, 'user_id');
        }



        public function getActivitylogOptions(): LogOptions
        {
        return LogOptions::defaults()
                ->logOnly(['fecha_ingreso', 'unidad_medida_id', 'articulo_id', 'unidades', 'proveedore_id', 'ubicacion_id', 'unidades_disponibles', 'n_factura'])
                ->logOnlyDirty()
                ->dontSubmitEmptyLogs();
        }
}
