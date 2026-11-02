<?php

namespace App\Http\Livewire\SalidaImpresora;

use Livewire\Component;
use App\Models\Cartucho;
use App\Models\Colore;
use App\Models\Impresora;
use App\Models\Fabricante;
use App\Models\SalidaImpresora;
use App\Models\InventarioImpresora;
use App\Models\Area;
use App\Models\Proveedore;
use App\Models\Gerencia;

class Editar extends Component
{
    public $salida_impresora;
    public $inventario_impresoras;
    public $areas;
    public $unidades;
    public $gerencias;

    // formulario
    public $cartucho_id;
    public $inventario_impresora_id;
    public $cantidad;
    public $colore_id;
    public $impresora_id;
    public $fecha_ingreso;
    public $fecha_salida;
    public $gerencia_id;
    public $area_id;
    public $ajuste_inventario;
    public $fabricante_id;
    public $colore_display;
    public $fabricante_display;
    public $cartucho_display;
    public $impresora_display;
    public $imagen_impresora;

    public function mount(SalidaImpresora $salida_impresora)
    {
        $this->salida_impresora = $salida_impresora;
        $this->inventario_impresoras = InventarioImpresora::with('impresoras', 'cartuchos', 'colores', 'fabricantes', 'areas', 'gerencias')->get();
        $this->areas = Area::all();
        $this->gerencias = Gerencia::all()->unique('gerencia');

        // Cargar datos del registro existente
        $this->fecha_salida = $salida_impresora->fecha_salida;
        $this->inventario_impresora_id = $salida_impresora->inventario_impresora_id;
        $this->cantidad = $salida_impresora->cantidad;
        $this->cartucho_id = $salida_impresora->cartucho_id;
        $this->colore_id = $salida_impresora->colore_id;
        $this->impresora_id = $salida_impresora->impresora_id;
        $this->gerencia_id = $salida_impresora->gerencia_id;
        $this->area_id = $salida_impresora->area_id;
        $this->ajuste_inventario = $salida_impresora->ajuste_inventario;
        $this->fabricante_id = $salida_impresora->fabricante_id;

        // Calcular unidades disponibles actuales
        $this->unidades = InventarioImpresora::where('cartucho_id', $this->cartucho_id)
            ->where('colore_id', $this->colore_id)
            ->where('area_id', $this->area_id)
            ->where('gerencia_id', $this->gerencia_id)
            ->sum('unidades_disponibles');

        // Cargar displays
        $inventario = InventarioImpresora::with(['impresoras', 'fabricantes', 'cartuchos', 'colores'])->find($this->inventario_impresora_id);
        if ($inventario) {
            $this->fecha_ingreso = $inventario->fecha_ingreso;
            $this->colore_display = $inventario->colores?->colore ?? '';
            $this->fabricante_display = $inventario->fabricantes?->fabricante ?? '';
            $this->cartucho_display = $inventario->cartuchos?->cartucho ?? '';
            $this->impresora_display = $inventario->impresoras?->impresora ?? '';
            $this->imagen_impresora = $inventario->impresoras && $inventario->impresoras->imagen ? asset('storage/' . $inventario->impresoras->imagen) : '';
        }
    }

    public function updatedGerenciaId()
    {
        $gerencia = Gerencia::find($this->gerencia_id);
        $this->areas = Area::whereHas('gerencias', function($query) use ($gerencia) {
            $query->where('gerencia', $gerencia->gerencia);
        })->get();
        $this->inventario_impresoras = collect();
        $this->resetDownstream();
    }

    public function updatedAreaId()
    {
        $gerencia = Gerencia::find($this->gerencia_id);
        $inventarios = InventarioImpresora::where('area_id', $this->area_id)
            ->whereHas('gerencias', function($query) use ($gerencia) {
                $query->where('gerencia', $gerencia->gerencia);
            })
            ->with(['impresoras', 'fabricantes', 'cartuchos', 'colores'])
            ->get();

        // Unique by impresora_id, cartucho_id, colore_id
        $this->inventario_impresoras = $inventarios->unique(function ($item) {
            return $item->impresora_id . '-' . $item->cartucho_id . '-' . $item->colore_id;
        });

        $this->resetDownstream();
    }

    public function updatedInventarioImpresoraId()
    {
        $inventario = InventarioImpresora::with(['impresoras', 'fabricantes', 'cartuchos', 'colores'])->find($this->inventario_impresora_id);
        if (!$inventario) return;
        $this->impresora_id = $inventario->impresora_id;
        $this->fabricante_id = $inventario->fabricante_id;
        $this->cartucho_id = $inventario->cartucho_id;
        $this->colore_id = $inventario->colore_id;
        $this->area_id = $inventario->area_id;
        $this->gerencia_id = $inventario->gerencia_id;

        // Calculate total available units for matching criteria
        $this->unidades = InventarioImpresora::where('cartucho_id', $this->cartucho_id)
            ->where('colore_id', $this->colore_id)
            ->where('area_id', $this->area_id)
            ->where('gerencia_id', $this->gerencia_id)
            ->sum('unidades_disponibles');

        $this->fecha_ingreso = $inventario->fecha_ingreso;
        $this->colore_display = $inventario->colores?->colore ?? '';
        $this->fabricante_display = $inventario->fabricantes?->fabricante ?? '';
        $this->cartucho_display = $inventario->cartuchos?->cartucho ?? '';
        $this->impresora_display = $inventario->impresoras?->impresora ?? '';
        $this->imagen_impresora = $inventario->impresoras && $inventario->impresoras->imagen ? asset('storage/' . $inventario->impresoras->imagen) : '';
    }

    public function resetDownstream()
    {
        $this->unidades = '';
        $this->fecha_ingreso = '';
        $this->inventario_impresora_id = '';
        $this->colore_display = '';
        $this->fabricante_display = '';
        $this->cartucho_display = '';
        $this->impresora_display = '';
        $this->imagen_impresora = '';
    }

    public function render()
    {
        return view('livewire.salida-impresora.editar');
    }

    public function submit()
    {
        $this->validate([
            'fecha_salida' => 'required|date',
            'inventario_impresora_id' => 'required|exists:inventario_impresoras,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        $inventario = InventarioImpresora::find($this->inventario_impresora_id);

        if (!$inventario) {
            session()->flash('error', 'Inventario no encontrado');
            return;
        }

        // Restaurar unidades anteriores
        $inventario->unidades_disponibles += $this->salida_impresora->cantidad;

        // Verificar si hay suficientes unidades después del cambio
        if ($inventario->unidades_disponibles < $this->cantidad) {
            session()->flash('error', 'No hay suficientes unidades disponibles');
            return;
        }

        // Actualizar el registro
        $this->salida_impresora->update([
            'fecha_salida' => $this->fecha_salida,
            'inventario_impresora_id' => $this->inventario_impresora_id,
            'cartucho_id' => $inventario->cartucho_id,
            'colore_id' => $inventario->colore_id,
            'impresora_id' => $inventario->impresora_id,
            'fabricante_id' => $inventario->fabricante_id,
            'gerencia_id' => $inventario->gerencia_id,
            'area_id' => $inventario->area_id,
            'cantidad' => $this->cantidad,
            'historico_unidades_disponibles' => $inventario->unidades_disponibles - $this->cantidad,
            'ajuste_inventario' => $inventario->unidades_disponibles - $this->cantidad,
        ]);

        // Actualizar unidades disponibles en inventario
        $inventario->unidades_disponibles -= $this->cantidad;
        $inventario->save();

        return redirect()->route('administracion.salida_impresora.index')->with('success', 'Registro actualizado correctamente');
    }
}
