<?php

namespace App\Http\Livewire\SalidaAlmacenamientoImpresora;

use Livewire\Component;
use App\Models\AlmacenamientoImpresora;
use App\Models\DistribucionAlmacenamientoImpresora;
use App\Models\SalidaAlmacenamientoImpresora as ModeloSalida;
use Illuminate\Support\Facades\DB;

class Crear extends Component
{
    public $almacenamientos = [];
    public $distribuciones = [];

    // Variables del formulario
    public $almacenamiento_impresora_id = '';
    public $distribucion_almacenamiento_impresora_id = '';
    public $cantidad = '';
    public $fecha_salida = '';
    public $ajuste_inventario = '';

    // Variables para mostrar info
    public $impresora = '';
    public $consumible = '';
    public $color = '';
    public $tipo = '';
    public $imagen_impresora = '';
    public $fecha_ingreso = '';
    public $unidades_disponibles = 0;
    public $unidades_asignadas = 0;

    public function mount()
    {
        $this->cargarAlmacenes();
        $this->fecha_salida = now()->format('Y-m-d');
    }

    public function cargarAlmacenes()
    {
        $todos = AlmacenamientoImpresora::with('cartuchos', 'colores', 'impresoras', 'fabricantes')->get();

        $agrupados = $todos->groupBy(function($item) {
            return ($item->impresoras?->impresora ?? '') . '|' .
                   ($item->cartuchos?->cartucho ?? '') . '|' .
                   ($item->colores?->colore ?? '');
        })->map(function($group) {
            $first = $group->sortByDesc('id')->first();
            return [
                'id' => $first->id,
                'impresora' => $first->impresoras?->impresora ?? '',
                'cartucho' => $first->cartuchos?->cartucho ?? '',
                'color' => $first->colores?->colore ?? '',
                'fabricante' => $first->fabricantes?->fabricante ?? '',
                'unidades_disponibles' => $group->sum('unidades_disponibles'),
            ];
        })->values();

        $this->almacenamientos = $agrupados;
    }

    public function updatedAlmacenamientoImpresoraId()
    {
        $this->cargarDistribuciones();
        $this->actualizarInfoAlmacenamiento();
    }

    public function updatedDistribucionAlmacenamientoImpresoraId()
    {
        $this->actualizarInfoDistribucion();
    }

    public function cargarDistribuciones()
    {
        if (!$this->almacenamiento_impresora_id) {
            $this->distribuciones = [];
            return;
        }

        $almacenamiento = AlmacenamientoImpresora::with('impresoras', 'cartuchos', 'colores')
            ->find($this->almacenamiento_impresora_id);

        if (!$almacenamiento) {
            $this->distribuciones = [];
            return;
        }

        $nombreImpresora = $almacenamiento->impresoras?->impresora ?? '';
        $nombreCartucho = $almacenamiento->cartuchos?->cartucho ?? '';
        $nombreColor = $almacenamiento->colores?->colore ?? '';

        // Buscar distribuciones que coincidan
        $distribuciones = DistribucionAlmacenamientoImpresora::whereHas('almacenamientoImpresora', function($q) use ($nombreImpresora, $nombreCartucho, $nombreColor) {
            $q->whereHas('impresoras', function($q2) use ($nombreImpresora) {
                $q2->where('impresora', $nombreImpresora);
            })->whereHas('cartuchos', function($q2) use ($nombreCartucho) {
                $q2->where('cartucho', $nombreCartucho);
            })->whereHas('colores', function($q2) use ($nombreColor) {
                $q2->where('colore', $nombreColor);
            });
        })->with('gerencia.area', 'almacenamientoImpresora.impresoras', 'almacenamientoImpresora.cartuchos', 'almacenamientoImpresora.colores')
        ->get()
        ->map(function($dist) {
            return [
                'id' => $dist->id,
                'impresora' => $dist->almacenamientoImpresora->impresoras?->impresora ?? '',
                'cartucho' => $dist->almacenamientoImpresora->cartuchos?->cartucho ?? '',
                'color' => $dist->almacenamientoImpresora->colores?->colore ?? '',
                'gerencia' => $dist->gerencia?->gerencia ?? '',
                'area' => $dist->gerencia?->area?->area ?? '',
                'unidades_asignadas' => $dist->unidades_asignadas,
            ];
        })->values();

        $this->distribuciones = $distribuciones;
    }

    public function actualizarInfoAlmacenamiento()
    {
        if (!$this->almacenamiento_impresora_id) {
            $this->resetInfo();
            return;
        }

        $almacenamiento = AlmacenamientoImpresora::with('impresoras', 'cartuchos', 'colores', 'fabricantes')
            ->find($this->almacenamiento_impresora_id);

        if ($almacenamiento) {
            // Calcular unidades disponibles totales
            $nombreImpresora = $almacenamiento->impresoras?->impresora ?? '';
            $nombreCartucho = $almacenamiento->cartuchos?->cartucho ?? '';
            $nombreColor = $almacenamiento->colores?->colore ?? '';

            $todos = AlmacenamientoImpresora::with('impresoras', 'cartuchos', 'colores')->get();
            $coincidentes = $todos->filter(function($item) use ($nombreImpresora, $nombreCartucho, $nombreColor) {
                return ($item->impresoras?->impresora ?? '') === $nombreImpresora
                    && ($item->cartuchos?->cartucho ?? '') === $nombreCartucho
                    && ($item->colores?->colore ?? '') === $nombreColor;
            });

            $this->unidades_disponibles = $coincidentes->sum('unidades_disponibles');

            $this->impresora = $almacenamiento->impresoras?->impresora ?? '';
            $this->consumible = $almacenamiento->cartuchos?->cartucho ?? '';
            $this->color = $almacenamiento->colores?->colore ?? '';
            $this->tipo = $almacenamiento->fabricantes?->fabricante ?? '';
            $this->imagen_impresora = $almacenamiento->impresoras?->imagen ?? '';

            $this->fecha_ingreso = $almacenamiento->fecha_ingreso;
        }
    }

    public function actualizarInfoDistribucion()
    {
        if (!$this->distribucion_almacenamiento_impresora_id) {
            $this->unidades_asignadas = 0;
            return;
        }

        $distribucion = DistribucionAlmacenamientoImpresora::find($this->distribucion_almacenamiento_impresora_id);

        if ($distribucion) {
            $this->unidades_asignadas = $distribucion->unidades_asignadas;
        }
    }

    public function resetInfo()
    {
        $this->impresora = '';
        $this->consumible = '';
        $this->color = '';
        $this->tipo = '';
        $this->imagen_impresora = '';
        $this->fecha_ingreso = '';
        $this->unidades_disponibles = 0;
        $this->unidades_asignadas = 0;
    }

    public function submit()
    {
        $this->validate([
            'almacenamiento_impresora_id' => 'required',
            'distribucion_almacenamiento_impresora_id' => 'required',
            'cantidad' => 'required|integer|min:1',
            'fecha_salida' => 'required|date',
            'ajuste_inventario' => 'nullable|string',
        ]);

        // Verificar cantidad
        if ($this->cantidad > $this->unidades_asignadas) {
            $this->addError('cantidad', 'La cantidad excede las unidades asignadas.');
            return;
        }

        // Obtener el almacenamiento
        $almacenamiento = AlmacenamientoImpresora::find($this->almacenamiento_impresora_id);
        $distribucion = DistribucionAlmacenamientoImpresora::find($this->distribucion_almacenamiento_impresora_id);

        // Crear la salida
        ModeloSalida::create([
            'almacenamiento_impresora_id' => $this->almacenamiento_impresora_id,
            'distribucion_almacenamiento_impresora_id' => $this->distribucion_almacenamiento_impresora_id,
            'cantidad' => $this->cantidad,
            'historico_unidades_disponibles' => $this->unidades_disponibles,
            'historico_unidades_asignadas_originales' => $this->unidades_asignadas,
            'historico_unidades_asignadas_restantes' => $this->unidades_asignadas - $this->cantidad,
            'fecha_salida' => $this->fecha_salida,
            'ajuste_inventario' => $this->ajuste_inventario,
        ]);

        // Actualizar unidades disponibles en almacenamiento
        $almacenamiento->decrement('unidades_disponibles', $this->cantidad);

        // Actualizar unidades restantes en distribución
        $distribucion->decrement('unidades_asignadas', $this->cantidad);

        return redirect()->route('administracion.salida_almacen.index')->with('success', 'Salida creada exitosamente.');
    }

    public function render()
    {
        return view('livewire.salida-almacenamiento-impresora.crear');
    }
}
