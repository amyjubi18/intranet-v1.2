<?php

namespace App\Http\Livewire\DistribucionAlmacenamientoImpresora;

use Livewire\Component;
use App\Models\DistribucionAlmacenamientoImpresora;
use App\Models\AlmacenamientoImpresora;
use App\Models\Gerencia;

class Crear extends Component
{
    public $almacenamientos;
    public $gerencias;

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

    public function mount()
    {
        $this->almacenamientos = AlmacenamientoImpresora::with('cartuchos', 'colores', 'impresoras', 'fabricantes')->get();
        $this->gerencias = Gerencia::with('area')->get();
        $this->unidades_disponibles = 0;
        $this->cartucho_display = '';
        $this->colore_display = '';
        $this->fabricante_display = '';
        $this->impresora_display = '';
        $this->imagen_impresora = '';
    }

    public function updatedAlmacenamientoImpresoraId()
    {
        $almacenamiento = AlmacenamientoImpresora::with('impresoras', 'cartuchos', 'colores', 'fabricantes')->find($this->almacenamiento_impresora_id);
        if ($almacenamiento) {
            $totalAsignado = DistribucionAlmacenamientoImpresora::where('almacenamiento_impresora_id', $this->almacenamiento_impresora_id)->sum('unidades_asignadas');
            $this->unidades_disponibles = $almacenamiento->unidades_disponibles - $totalAsignado;
            $this->colore_display = $almacenamiento->colores?->colore ?? '';
            $this->fabricante_display = $almacenamiento->fabricantes?->fabricante ?? '';
            $this->cartucho_display = $almacenamiento->cartuchos?->cartucho ?? '';
            $this->impresora_display = $almacenamiento->impresoras?->impresora ?? '';
            $this->imagen_impresora = $almacenamiento->impresoras && $almacenamiento->impresoras->imagen ? asset('storage/' . $almacenamiento->impresoras->imagen) : '';
        } else {
            $this->unidades_disponibles = 0;
            $this->colore_display = '';
            $this->fabricante_display = '';
            $this->cartucho_display = '';
            $this->impresora_display = '';
            $this->imagen_impresora = '';
        }
    }

    public function submit()
    {
        $this->validate([
            'almacenamiento_impresora_id' => 'required|exists:almacenamiento_impresoras,id',
            'gerencia_id' => 'required|exists:gerencias,id',
            'unidades_asignadas' => 'required|integer|min:1',
        ]);

        // Verificar unidades disponibles
        if ($this->unidades_asignadas > $this->unidades_disponibles) {
            $this->addError('unidades_asignadas', 'Las unidades asignadas exceden las disponibles.');
            return;
        }

        DistribucionAlmacenamientoImpresora::create([
            'almacenamiento_impresora_id' => $this->almacenamiento_impresora_id,
            'gerencia_id' => $this->gerencia_id,
            'unidades_asignadas' => $this->unidades_asignadas,
            'unidades_asignadas_originales' => $this->unidades_asignadas,
        ]);

        return redirect()->route('administracion.distribucion_almacenamiento_impresoras.index')->with('success', 'Distribución creada exitosamente.');
    }

    public function render()
    {
        return view('livewire.distribucion-almacenamiento-impresora.crear');
    }
}
