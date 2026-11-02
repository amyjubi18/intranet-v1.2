<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Models\SalidaAlmacenamientoImpresora;
use App\Models\Gerencia;

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

    /**
     * Verificar si el stock actual está bajo el mínimo
     * @return bool
     */
    public function tieneStockBajo(): bool
    {
        $stockMin = $this->getStockMinAttribute();
        if ($stockMin === null) {
            return false;
        }

        // Calcular unidades disponibles reales (sumando todos los registros con el mismo nombre)
        $nombreImpresora = $this->impresoras?->impresora ?? '';
        $nombreCartucho = $this->cartuchos?->cartucho ?? '';
        $nombreColor = $this->colores?->colore ?? '';

        $todos = self::with('impresoras', 'cartuchos', 'colores')->get();
        $coincidentes = $todos->filter(function($item) use ($nombreImpresora, $nombreCartucho, $nombreColor) {
            return ($item->impresoras?->impresora ?? '') === $nombreImpresora
                && ($item->cartuchos?->cartucho ?? '') === $nombreCartucho
                && ($item->colores?->colore ?? '') === $nombreColor;
        });

        $totalDisponibles = $coincidentes->sum('unidades_disponibles');

        return $totalDisponibles < $stockMin;
    }

    /**
     * Obtener las unidades disponibles totales para este consumible (misma impresora, cartucho, color)
     * @return int
     */
    public function getUnidadesDisponiblesTotales(): int
    {
        $nombreImpresora = $this->impresoras?->impresora ?? '';
        $nombreCartucho = $this->cartuchos?->cartucho ?? '';
        $nombreColor = $this->colores?->colore ?? '';

        $todos = self::with('impresoras', 'cartuchos', 'colores')->get();
        $coincidentes = $todos->filter(function($item) use ($nombreImpresora, $nombreCartucho, $nombreColor) {
            return ($item->impresoras?->impresora ?? '') === $nombreImpresora
                && ($item->cartuchos?->cartucho ?? '') === $nombreCartucho
                && ($item->colores?->colore ?? '') === $nombreColor;
        });

        return $coincidentes->sum('unidades_disponibles');
    }

    /**
     * Verificar si existen registros en salida_almacenamiento_impresoras
     * @return bool
     */
    public static function hayRegistrosEnSalida(): bool
    {
        return SalidaAlmacenamientoImpresora::count() > 0;
    }

    /**
     * Obtener la gerencia que más ha consumido este tipo de consumible
     * @return array|null
     */
    public function gerenciaConMayorConsumo(): ?array
    {
        // Verificar si hay registros en salida
        if (!self::hayRegistrosEnSalida()) {
            return null;
        }

        $nombreImpresora = $this->impresoras?->impresora ?? '';
        $nombreCartucho = $this->cartuchos?->cartucho ?? '';
        $nombreColor = $this->colores?->colore ?? '';

        // Buscar los IDs de almacenamiento que coinciden con este consumible
        $todos = self::with('impresoras', 'cartuchos', 'colores')->get();
        $coincidentes = $todos->filter(function($item) use ($nombreImpresora, $nombreCartucho, $nombreColor) {
            return ($item->impresoras?->impresora ?? '') === $nombreImpresora
                && ($item->cartuchos?->cartucho ?? '') === $nombreCartucho
                && ($item->colores?->colore ?? '') === $nombreColor;
        });

        $almacenamientoIds = $coincidentes->pluck('id');

        if ($almacenamientoIds->isEmpty()) {
            return null;
        }

        // Obtener la gerencia con mayor consumo de este tipo de consumible
        // Usar nombres de tabla completos para evitar ambigüedad
        $resultado = DB::table('salida_almacenamiento_impresoras')
            ->join('distribucion_almacenamiento_impresoras', 'salida_almacenamiento_impresoras.distribucion_almacenamiento_impresora_id', '=', 'distribucion_almacenamiento_impresoras.id')
            ->join('gerencias', 'distribucion_almacenamiento_impresoras.gerencia_id', '=', 'gerencias.id')
            ->join('areas', 'gerencias.area_id', '=', 'areas.id')
            ->whereIn('salida_almacenamiento_impresoras.almacenamiento_impresora_id', $almacenamientoIds)
            ->select(
                'gerencias.id as gerencia_id',
                'gerencias.gerencia',
                'areas.area',
                DB::raw('SUM(salida_almacenamiento_impresoras.cantidad) as total_consumido')
            )
            ->groupBy('gerencias.id', 'gerencias.gerencia', 'areas.area')
            ->orderByDesc('total_consumido')
            ->first();

        return $resultado ? [
            'gerencia_id' => $resultado->gerencia_id,
            'gerencia' => $resultado->gerencia,
            'area' => $resultado->area,
            'total_consumido' => $resultado->total_consumido
        ] : null;
    }

    /**
     * Generar mensaje de sugerencia para asignar a la gerencia adecuada
     * @return string|null
     */
    public function generarMensajeSugerencia(): ?string
    {
        // Solo generar sugerencia si hay registros en salida
        if (!self::hayRegistrosEnSalida()) {
            return null;
        }

        $nombreImpresora = $this->impresoras?->impresora ?? 'Impresora Desconocida';
        $nombreCartucho = $this->cartuchos?->cartucho ?? 'Consumible Desconocido';
        $stockMin = $this->getStockMinAttribute();
        $unidadesDisponibles = $this->getUnidadesDisponiblesTotales();

        // Verificar si tiene stock bajo
        $tieneStockBajo = $this->tieneStockBajo();

        // Obtener gerencia con mayor consumo
        $gerenciaConsumo = $this->gerenciaConMayorConsumo();

        if ($gerenciaConsumo) {
            if ($tieneStockBajo) {
                return "⚠️ Stock bajo: La impresora {$nombreImpresora} - {$nombreCartucho} tiene solo {$unidadesDisponibles} unidades disponibles (mínimo: {$stockMin}). Se recomienda asignar a la gerencia '{$gerenciaConsumo['gerencia']}' ({$gerenciaConsumo['area']}) que es la que más consume este consumible ({$gerenciaConsumo['total_consumido']} unidades consumidas).";
            } else {
                return "💡 La gerencia '{$gerenciaConsumo['gerencia']}' ({$gerenciaConsumo['area']}) es la que más consume este consumible ({$gerenciaConsumo['total_consumido']} unidades consumidas).";
            }
        }

        return null;
    }
}
