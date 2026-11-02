<?php

namespace App\Http\Livewire\DistribucionAlmacenamientoImpresora;

use Livewire\Component;
use App\Models\DistribucionAlmacenamientoImpresora;
use App\Models\AlmacenamientoImpresora;
use App\Models\Gerencia;
use App\Models\SalidaAlmacenamientoImpresora;
use Illuminate\Support\Facades\DB;

class Crear extends Component
{
    public $almacenamientos;
    public $gerencias;

    // Variables para el cálculo correcto de unidades
    public $allItems = [];
    public $stock_bajo_keys = [];

    // Formulario
    public $almacenamiento_impresora_id;
    public $gerencia_id;
    public $unidades_asignadas;
    public $unidades_disponibles;

    // Display info
    public $cartucho_display;
    public $colore_display;
    public $fabricante_display;
    public $impresora_display;
    public $imagen_impresora;

    // Sugerencias
    public $sugerencias = [];
    public $mostrar_sugerencias = false;
    public $mensaje_sugerencia = '';
    public $sugerencia_gerencia_id = '';

    public function mount()
    {
        // Obtener todos los registros con sus relaciones
        $this->allItems = AlmacenamientoImpresora::with('cartuchos', 'colores', 'impresoras', 'fabricantes')->get();

        // Calcular totales por key (impresora_id + colore_id)
        $this->stock_bajo_keys = [];

        $agrupados = $this->allItems->groupBy(function($item) {
            return $item->impresora_id . '_' . $item->colore_id;
        });

        foreach($agrupados as $key => $group) {
            $first = $group->first();
            $stockMin = $first->cartuchos ? $first->cartuchos->stock_min : 0;
            $unidades_disponibles = $group->sum('unidades_disponibles');

            if ($stockMin > 0 && $unidades_disponibles < $stockMin) {
                $this->stock_bajo_keys[] = $key;
            }
        }

        // Agrupar consumibles por nombre único (impresora + cartucho + color)
        // Solo mostrar registros que tienen unidades disponibles (no distribuidos completamente)
        $this->almacenamientos = $this->allItems->groupBy(function($item) {
            return ($item->impresoras?->impresora ?? 'N/A') . '|' .
                   ($item->cartuchos?->cartucho ?? 'N/A') . '|' .
                   ($item->colores?->colore ?? 'N/A');
        })->map(function($group) {
            $first = $group->first();

            // Calcular unidades ya distribuidas para este grupo
            $nombreImpresora = $first->impresoras?->impresora ?? '';
            $nombreCartucho = $first->cartuchos?->cartucho ?? '';
            $nombreColor = $first->colores?->colore ?? '';

            $totalAsignado = DistribucionAlmacenamientoImpresora::whereHas('almacenamientoImpresora', function($q) use ($nombreImpresora, $nombreCartucho, $nombreColor) {
                $q->whereHas('impresoras', function($q2) use ($nombreImpresora) {
                    $q2->where('impresora', $nombreImpresora);
                })->whereHas('cartuchos', function($q2) use ($nombreCartucho) {
                    $q2->where('cartucho', $nombreCartucho);
                })->whereHas('colores', function($q2) use ($nombreColor) {
                    $q2->where('colore', $nombreColor);
                });
            })->sum('unidades_asignadas');

            $totalRegistrado = $group->sum('unidades');
            $disponibles = $totalRegistrado - $totalAsignado;

            return [
                'id' => $first->id,
                'impresora' => $first->impresoras?->impresora ?? 'N/A',
                'cartucho' => $first->cartuchos?->cartucho ?? 'N/A',
                'color' => $first->colores?->colore ?? 'N/A',
                'unidades' => $totalRegistrado,
                'unidades_asignadas' => $totalAsignado,
                'unidades_disponibles' => $disponibles,
                'key' => $first->impresora_id . '_' . $first->colore_id,
            ];
        })->filter(function($item) {
            // Solo mostrar los que tienen unidades disponibles para distribuir
            return $item['unidades_disponibles'] > 0;
        })->values();

        $this->gerencias = Gerencia::with('area')->get();
        $this->unidades_disponibles = 0;
        $this->cartucho_display = '';
        $this->colore_display = '';
        $this->fabricante_display = '';
        $this->impresora_display = '';
        $this->imagen_impresora = '';

        // Inicializar variables
        $this->sugerencias = [];
        $this->mensaje_sugerencia = '';
        $this->sugerencia_gerencia_id = '';

        $this->cargarSugerencias();
    }

    public function cargarSugerencias()
    {
        $this->sugerencias = [];

        $haySalidas = SalidaAlmacenamientoImpresora::exists();

        if (!$haySalidas) {
            $this->mensaje_sugerencia = 'No hay registros de consumo en salida_almacen para generar sugerencias.';
            return;
        }

        foreach ($this->stock_bajo_keys as $key) {
            $parts = explode('_', $key);
            $impresora_id = $parts[0];
            $colore_id = $parts[1] ?? '';

            $item = $this->allItems->where('impresora_id', $impresora_id)
                ->where('colore_id', $colore_id)
                ->first();

            if (!$item) continue;

            // Calcular unidades disponibles para esta key
            $unidades_disponibles = $this->allItems->where('impresora_id', $impresora_id)
                ->where('colore_id', $colore_id)
                ->sum('unidades_disponibles');

            $stockMin = $item->cartuchos ? $item->cartuchos->stock_min : 0;

            // Buscar gerencias que han consumido este tipo
            $consumoPorGerencia = SalidaAlmacenamientoImpresora::select(
                'distribucion_almacenamiento_impresora_id',
                DB::raw('SUM(cantidad) as total_consumido')
            )
            ->whereHas('distribucionAlmacenamientoImpresora', function($q) use ($impresora_id, $colore_id) {
                $q->whereHas('almacenamientoImpresora', function($q2) use ($impresora_id, $colore_id) {
                    $q2->where('impresora_id', $impresora_id)
                       ->where('colore_id', $colore_id);
                });
            })
            ->groupBy('distribucion_almacenamiento_impresora_id')
            ->orderBy('total_consumido', 'desc')
            ->limit(5)
            ->get();

            $gerenciasSugeridas = [];
            foreach ($consumoPorGerencia as $consumo) {
                $distribucion = DistribucionAlmacenamientoImpresora::with('gerencia.area')->find($consumo->distribucion_almacenamiento_impresora_id);
                if ($distribucion && $distribucion->gerencia) {
                    $gerenciasSugeridas[] = [
                        'gerencia_id' => $distribucion->gerencia_id,
                        'gerencia' => $distribucion->gerencia->gerencia,
                        'area' => $distribucion->gerencia->area->area ?? 'N/A',
                        'consumo_total' => $consumo->total_consumido,
                    ];
                }
            }

            if (!empty($gerenciasSugeridas)) {
                $this->sugerencias[] = [
                    'key' => $key,
                    'impresora' => $item->impresoras->impresora ?? 'N/A',
                    'consumible' => $item->cartuchos->cartucho ?? 'N/A',
                    'color' => $item->colores->colore ?? 'N/A',
                    'unidades_disponibles' => $unidades_disponibles,
                    'stock_min' => $stockMin,
                    'gerencias' => $gerenciasSugeridas,
                ];
            }
        }

        $this->mostrar_sugerencias = !empty($this->sugerencias);

        if ($this->mostrar_sugerencias) {
            $this->mensaje_sugerencia = 'Se encontraron consumibles con stock bajo. Ver las sugerencias abajo.';
        } else {
            $this->mensaje_sugerencia = 'No hay sugerencias de stock bajo en este momento.';
        }
    }

    public function updatedAlmacenamientoImpresoraId()
    {
        if (!$this->almacenamiento_impresora_id) {
            $this->resetValues();
            return;
        }

        $almacenamiento = AlmacenamientoImpresora::with('impresoras', 'cartuchos', 'colores', 'fabricantes')->find($this->almacenamiento_impresora_id);

        if ($almacenamiento) {
            // Calcular usando la misma lógica
            $key = $almacenamiento->impresora_id . '_' . $almacenamiento->colore_id;

            $this->unidades_disponibles = $this->allItems->where('impresora_id', $almacenamiento->impresora_id)
                ->where('colore_id', $almacenamiento->colore_id)
                ->sum('unidades_disponibles');

            $this->colore_display = $almacenamiento->colores?->colore ?? '';
            $this->fabricante_display = $almacenamiento->fabricantes?->fabricante ?? '';
            $this->cartucho_display = $almacenamiento->cartuchos?->cartucho ?? '';
            $this->impresora_display = $almacenamiento->impresoras?->impresora ?? '';
            $this->imagen_impresora = $almacenamiento->impresoras && $almacenamiento->impresoras->imagen
                ? asset('storage/' . $almacenamiento->impresoras->imagen)
                : '';
        } else {
            $this->resetValues();
        }
    }

    private function resetValues()
    {
        $this->unidades_disponibles = 0;
        $this->colore_display = '';
        $this->fabricante_display = '';
        $this->cartucho_display = '';
        $this->impresora_display = '';
        $this->imagen_impresora = '';
    }

    public function submit()
    {
        $this->validate([
            'almacenamiento_impresora_id' => 'required|exists:almacenamiento_impresoras,id',
            'gerencia_id' => 'required|exists:gerencias,id',
            'unidades_asignadas' => 'required|integer|min:1',
        ]);

        if ($this->unidades_asignadas > $this->unidades_disponibles) {
            $this->addError('unidades_asignadas', 'Las unidades asignadas exceden las disponibles.');
            return;
        }

        DistribucionAlmacenamientoImpresora::create([
            'almacenamiento_impresora_id' => $this->almacenamiento_impresora_id,
            'gerencia_id' => $this->gerencia_id,
            'unidades_asignadas' => $this->unidades_asignadas,
            'unidades_asignadas_originales' => $this->unidades_asignadas,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('administracion.distribucion_almacenamiento_impresoras.index')->with('success', 'Distribución creada exitosamente.');
    }

    public function render()
    {
        return view('livewire.distribucion-almacenamiento-impresora.crear');
    }
}

