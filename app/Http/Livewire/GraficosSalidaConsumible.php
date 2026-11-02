<?php

namespace App\Http\Livewire;

use Livewire\Component;

class GraficosSalidaConsumible extends Component
{
    public function render()
    {
        return view('livewire.salida-impresora.graficos-salida-consumible')->section('content');
    }
}
