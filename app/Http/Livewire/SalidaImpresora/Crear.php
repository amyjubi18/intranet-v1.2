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

class Crear extends Component
{

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


    public function mount()
    {
        $this->inventario_impresoras = collect();
        $this->salida_impresoras = SalidaImpresora::all();
        $this->areas = Area::all();
        $this->gerencias = Gerencia::whereHas('inventario_impresoras')->get()->unique('gerencia');
        $this->cartucho_display = '';
        $this->imagen_impresora = '';

    }

    public function updatedGerenciaId()
    {
        $gerencia = Gerencia::find($this->gerencia_id);
        $this->areas = Area::whereHas('inventario_impresoras', function($query) use ($gerencia) {
            $query->where('gerencia_id', $gerencia->id);
        })->get();
        $this->area_id = '';
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
        return view('livewire.salida-impresora.crear');
    }
    public function submit()
    {

        //code...
        $inventario_impresoras = InventarioImpresora::where('cartucho_id', $this->cartucho_id)
        ->where('colore_id', $this->colore_id)
        ->where('area_id', $this->area_id)
        ->where('gerencia_id', $this->gerencia_id)->get();



    $diferenciaSalida = $this->cantidad;
    foreach ($inventario_impresoras as $inventario_oficina) {
        if ($diferenciaSalida > $inventario_oficina->unidades_disponibles ) {
            $diferenciaSalida -= $inventario_oficina->unidades_disponibles;
            $inventario_oficina->unidades_disponibles = 0;
            $inventario_oficina->save();
        } else {
            $inventario_oficina->unidades_disponibles = $inventario_oficina->unidades_disponibles - $diferenciaSalida;
            $diferenciaSalida -= $diferenciaSalida;
            $inventario_oficina->save();
        }
    }


        SalidaImpresora::create([
            'fecha_salida' => $this->fecha_salida,
            'cartucho_id' => $this->cartucho_id,
            'inventario_impresora_id' => $this->inventario_impresora_id,
            'cantidad' => $this->cantidad,
            'colore_id' => $this->colore_id,
            'impresora_id' => $this->impresora_id,
            'gerencia_id' => $this->gerencia_id,
            'area_id' => $this->area_id,
            'ajuste_inventario' => $this->ajuste_inventario,
            'historico_unidades_disponibles' => $this->unidades,
            'fabricante_id' => $this->fabricante_id,
        ]);



        return redirect()->route('administracion.salida_impresora.index')->with('success', 'Salida realizada exitosamente.');
    }
}
