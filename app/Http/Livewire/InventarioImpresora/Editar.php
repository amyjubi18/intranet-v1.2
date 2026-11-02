<?php

namespace App\Http\Livewire\InventarioImpresora;

use Livewire\Component;
use App\Models\Cartucho;
use App\Models\Colore;
use App\Models\Impresora;
use App\Models\Fabricante;
use App\Models\InventarioImpresora;
use App\Models\Area;
use App\Models\Proveedore;
use App\Models\Gerencia;

class Editar extends Component
{
    public $inventario_impresora;
    public $cartuchos;
    public $colores;
    public $impresoras;
    public $fabricantes;
    public $areas;
    public $proveedores;
    public $gerencias;

    // formulario
    public $fecha_ingreso;
    public $cartucho_id;
    public $colore_id;
    public $impresora_id;
    public $fabricante_id;
    public $proveedore_id;
    public $gerencia_id;
    public $area_id;
    public $unidades;
    public $n_factura;

    public function mount(InventarioImpresora $inventario_impresora)
    {
        $this->inventario_impresora = $inventario_impresora;
        $this->cartuchos = Cartucho::with('colore', 'impresora', 'fabricante')->get();
        $this->colores = Colore::all();
        $this->impresoras = Impresora::all();
        $this->fabricantes = Fabricante::all();
        $this->areas = Area::all();
        $this->proveedores = Proveedore::all();
        $this->gerencias = Gerencia::with('area')->get();

        // Cargar datos del registro existente
        $this->fecha_ingreso = $inventario_impresora->fecha_ingreso;
        $this->cartucho_id = $inventario_impresora->cartucho_id;
        $this->colore_id = $inventario_impresora->colore_id;
        $this->impresora_id = $inventario_impresora->impresora_id;
        $this->fabricante_id = $inventario_impresora->fabricante_id;
        $this->proveedore_id = $inventario_impresora->proveedore_id;
        $this->gerencia_id = $inventario_impresora->gerencia_id;
        $this->area_id = $inventario_impresora->area_id;
        $this->unidades = $inventario_impresora->unidades;
        $this->n_factura = $inventario_impresora->n_factura;
    }

    public function updatedImpresoraId()
    {
        // Filter cartuchos based on selected impresora
        $this->cartuchos = Cartucho::with('colore', 'impresora', 'fabricante')
            ->where('impresora_id', $this->impresora_id)
            ->get();

        // Reset dependent fields
        $this->cartucho_id = '';
        $this->colore_id = '';
        $this->fabricante_id = '';
    }

    public function updatedCartuchoId()
    {
        $cartucho = Cartucho::with('colore', 'fabricante')->find($this->cartucho_id);
        if ($cartucho) {
            $this->colore_id = $cartucho->colore_id;
            $this->fabricante_id = $cartucho->fabricante_id;
        }
    }

    public function updatedGerenciaId()
    {
        $gerencia = Gerencia::find($this->gerencia_id);
        if ($gerencia && $gerencia->area) {
            $this->area_id = $gerencia->area->id;
        }
    }

    public function render()
    {
        return view('livewire.inventario-impresora.editar');
    }

    public function submit()
    {
        $this->validate([
            'fecha_ingreso' => 'required|date',
            'cartucho_id' => 'required|exists:cartuchos,id',
            'colore_id' => 'required|exists:colores,id',
            'impresora_id' => 'required|exists:impresoras,id',
            'fabricante_id' => 'required|exists:fabricantes,id',
            'proveedore_id' => 'required|exists:proveedores,id',
            'gerencia_id' => 'required|exists:gerencias,id',
            'area_id' => 'required|exists:areas,id',
            'unidades' => 'required|integer|min:1',
            'n_factura' => 'required|string|max:255',
        ]);

        $this->inventario_impresora->update([
            'fecha_ingreso' => $this->fecha_ingreso,
            'cartucho_id' => $this->cartucho_id,
            'colore_id' => $this->colore_id,
            'impresora_id' => $this->impresora_id,
            'fabricante_id' => $this->fabricante_id,
            'proveedore_id' => $this->proveedore_id,
            'gerencia_id' => $this->gerencia_id,
            'area_id' => $this->area_id,
            'unidades' => $this->unidades,
            'n_factura' => $this->n_factura,
        ]);

        return redirect()->route('administracion.inventario_impresoras.index')->with('success', 'Registro actualizado correctamente');
    }
}
