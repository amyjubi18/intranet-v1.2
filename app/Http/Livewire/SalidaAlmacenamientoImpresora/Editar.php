<?php

namespace App\Http\Livewire\SalidaAlmacenamientoImpresora;

use Livewire\Component;
use App\Models\AlmacenamientoImpresora;
use App\Models\DistribucionAlmacenamientoImpresora;
use App\Models\SalidaAlmacenamientoImpresora;
use App\Models\Gerencia;
use App\Models\Area;

class Editar extends Component
{
    public $salida;
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

    public function mount(SalidaAlmacenamientoImpresora $salida)
    {
        $this->salida = $salida;
        $this->almacenamiento_impresoras = AlmacenamientoImpresora::with('cartuchos', 'colores', 'impresoras', 'fabricantes')->get();
        $this->distribuciones = DistribucionAlmacenamientoImpresora::with('almacenamientoImpresora', 'gerencia')->get();
        $this->gerencias = Gerencia::all();
        $this->areas = Area::all();

        // Cargar datos existentes
        $this->fecha_salida = $salida->fecha_salida;
        $this->almacenamiento_impresora_id = $salida->almacenamiento_impresora_id;
        $this->distribucion_almacenamiento_impresora_id = $salida->distribucion_almacenamiento_impresora_id;
        $this->cantidad = $salida->cantidad;
        $this->ajuste_inventario = $salida->ajuste_inventario;

        // Calcular unidades disponibles actuales
        $almacenamiento = AlmacenamientoImpresora::find($this->almacenamiento_impresora_id);
        if ($almacenamiento) {
            $this->unidades_disponibles = $almacenamiento->unidades_disponibles + $this->cantidad; // Restaurar para mostrar disponible antes de salida
        }

        $distribucion = DistribucionAlmacenamientoImpresora::find($this->distribucion_almacenamiento_impresora_id);
        if ($distribucion) {
            $this->unidades_asignadas = $distribucion->unidades_asignadas + $this->cantidad; // Restaurar para mostrar asignadas antes de salida
        }
    }

    public function updatedAlmacenamientoImpresoraId()
    {
        $almacenamiento = AlmacenamientoImpresora::find($this->almacenamiento_impresora_id);
        if ($almacenamiento) {
            $this->unidades_disponibles = $almacenamiento->unidades_disponibles;
        }
        $this->distribuciones = DistribucionAlmacenamientoImpresora::where('almacenamiento_impresora_id', $this->almacenamiento_impresora_id)->with('gerencia')->get();
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
        return view('livewire.salida-almacenamiento-impresora.editar');
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

        // Restaurar unidades anteriores
        $distribucion->unidades_asignadas += $this->salida->cantidad;
        $almacenamiento->unidades_disponibles += $this->salida->cantidad;

        // Verificar si hay suficientes unidades
        if ($distribucion->unidades_asignadas < $this->cantidad || $almacenamiento->unidades_disponibles < $this->cantidad) {
            session()->flash('error', 'No hay suficientes unidades disponibles');
            return;
        }

        $this->salida->update([
            'fecha_salida' => $this->fecha_salida,
            'almacenamiento_impresora_id' => $this->almacenamiento_impresora_id,
            'distribucion_almacenamiento_impresora_id' => $this->distribucion_almacenamiento_impresora_id,
            'cantidad' => $this->cantidad,
            'historico_unidades_disponibles' => $almacenamiento->unidades_disponibles - $this->cantidad,
            'historico_unidades_asignadas_originales' => $distribucion->unidades_asignadas_originales,
            'historico_unidades_asignadas_restantes' => $distribucion->unidades_asignadas,
            'ajuste_inventario' => $this->ajuste_inventario,
            'user_id' => auth()->id(),
        ]);

        // Actualizar unidades
        $distribucion->unidades_asignadas -= $this->cantidad;
        $distribucion->save();
        $almacenamiento->unidades_disponibles -= $this->cantidad;
        $almacenamiento->save();

        return redirect()->route('administracion.salida_almacenamiento_impresora.index')->with('success', 'Registro actualizado correctamente');
    }
}
