<?php

namespace App\Http\Livewire;
use App\Exports\BienesExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

use Livewire\Component;

class Biene extends Component
{
    public Collection $bienes;
    public Collection $selectedBienes;
    public $designTemplate = 'tailwind';
    public function mount(){
        $this->bienes = Biene::with('unidad_administrativas', 'forma_adquisicions', 'estado_del_usos', 'categoria_generals', 'sub_categorias', 'categoria_especificas')->get();
        $this->selectedBienes = collect();
    }
    public function render()
    {
        return view('livewere.'.$this->designTemplate.'.bienes');
        /* return view('livewire.biene'); */
    }
    private function getSelectedBienes(){
        return $this->selectedBienes->filter(fn($b)=>$b)->keys();
    }
    public function export($excel){
        abort_if(!in_array($excel, ['xlsx']), Response::HTTP_NOT_FOUND);
        return Excel::download( new BienesExport($this->getSelectedBienes()), 'bienes.'. $excel);

    }
}
