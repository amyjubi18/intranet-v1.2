<?php

namespace App\Http\Livewire\SalidaAlmacenamientoImpresora;

use Livewire\Component;
use App\Models\AlmacenamientoImpresora;
use App\Models\DistribucionAlmacenamientoImpresora;
use App\Models\SalidaAlmacenamientoImpresora;
use App\Models\Gerencia;
use App\Models\Area;

class Crear extends Component
{
    public $almacenamiento_impresoras;
    public $distribuciones;
    public $gerencias;
    public $areas;

    // formulario
    public $almacenamiento_impresora_id;
    public $distribucion_almacenamiento_impresora_id;
    public $cantidad;
    public $fecha_salida;
    public $ajuste_inventario;
    public $unidades_disponibles;
    public $unidades_asignadas;
    public $consumible;
    public $color;
    public $tipo;
    public $impresora;
    public $imagen_impresora;
    public $fecha_ingreso;

    public function mount()
    {
        $this->almacenamiento_impresoras = AlmacenamientoImpresora::with('cartuchos', 'colores', 'impresoras', 'fabricantes')->whereRaw('(unidades - (SELECT COALESCE(SUM(cantidad), 0) FROM salida_almacenamiento_impresoras WHERE almacenamiento_impresora_id = almacenamiento_impresoras.id)) > 0')->get();
        $this->distribuciones = DistribucionAlmacenamientoImpresora::with('almacenamientoImpresora', 'gerencia')->get();
        $this->gerencias = Gerencia::all();
        $this->areas = Area::all();
    }

    public function updatedAlmacenamientoImpresoraId()
    {
        $almacenamiento = AlmacenamientoImpresora::find($this->almacenamiento_impresora_id);
        if ($almacenamiento) {
            $this->unidades_disponibles = $almacenamiento->unidades_disponibles;
            $this->consumible = $almacenamiento->cartuchos->cartucho ?? '';
            $this->color = $almacenamiento->colores->colore ?? '';
            $this->tipo = $almacenamiento->fabricantes->fabricante ?? '';
            $this->impresora = $almacenamiento->impresoras->impresora ?? '';
            $this->imagen_impresora = $almacenamiento->impresoras->imagen ?? '';
            $this->fecha_ingreso = $almacenamiento->fecha_ingreso ?? '';
        }
        $this->distribuciones = DistribucionAlmacenamientoImpresora::where('almacenamiento_impresora_id', $this->almacenamiento_impresora_id)->with('gerencia.area')->get();
        $this->distribucion_almacenamiento_impresora_id = '';
        $this->unidades_asignadas = '';
    }

    public function updatedDistribucionAlmacenamientoImpresoraId()
    {
        $distribucion = DistribucionAlmacenamientoImpresora::find($this->distribucion_almacenamiento_impresora_id);
        if ($distribucion) {
            $this->unidades_asignadas = $distribucion->unidades_asignadas;
        }
    }

    public function render()
    {
        return view('livewire.salida-almacenamiento-impresora.crear');
    }

    public function submit()
    {
        $this->validate([
            'fecha_salida' => 'required|date|before_or_equal:today',
            'almacenamiento_impresora_id' => 'required|exists:almacenamiento_impresoras,id',
            'distribucion_almacenamiento_impresora_id' => 'required|exists:distribucion_almacenamiento_impresoras,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        $almacenamiento = AlmacenamientoImpresora::find($this->almacenamiento_impresora_id);
        $distribucion = DistribucionAlmacenamientoImpresora::find($this->distribucion_almacenamiento_impresora_id);

        if (!$almacenamiento || !$distribucion) {
            session()->flash('error', 'Datos no encontrados');
            return;
        }

        if ($distribucion->unidades_asignadas < $this->cantidad || $almacenamiento->unidades_disponibles < $this->cantidad) {
            session()->flash('error', 'No hay suficientes unidades disponibles');
            return;
        }

        // Restar de unidades_asignadas en distribucion
        $distribucion->unidades_asignadas -= $this->cantidad;
        $distribucion->save();

        // Restar de unidades_disponibles en almacenamiento
        $almacenamiento->unidades_disponibles -= $this->cantidad;
        $almacenamiento->save();

        SalidaAlmacenamientoImpresora::create([
            'fecha_salida' => $this->fecha_salida,
            'almacenamiento_impresora_id' => $this->almacenamiento_impresora_id,
            'distribucion_almacenamiento_impresora_id' => $this->distribucion_almacenamiento_impresora_id,
            'cantidad' => $this->cantidad,
            'historico_unidades_disponibles' => $almacenamiento->unidades_disponibles + $this->cantidad,
            'historico_unidades_asignadas_originales' => $distribucion->unidades_asignadas_originales,
            'historico_unidades_asignadas_restantes' => $distribucion->unidades_asignadas,
            'ajuste_inventario' => $this->ajuste_inventario,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('administracion.salida_almacen.index')->with('success', 'Salida realizada exitosamente.');
    }
}
