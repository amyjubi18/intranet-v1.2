<?php

namespace App\Http\Livewire\Cdtcreate;

use Livewire\Component;
use App\Models\Cdt;
use App\Models\Contratado;
use App\Models\Directivo;
use App\Models\Abogado;
use Illuminate\Http\Request;

class Crear extends Component
{
    //relaciones
    public $contratados;
    public $directivos;
    public $abogados;
    public $n_contratado;
    public $directivo_cargo;
    public $directivo_name;
    public $directivo_cedula;
    public $contratado_name;
    public $nacionalidad;
    public $cdts;

    //formulario
    public $n_addendum;
    public $contratado_id;
    public $directivo_id;
    public $gaceta;
    public $ciudadano;



    public function render()
    {
        return view('livewire.cdtcreate.crear');
    }

    public function mount(Request $request){
        $this->cdts = Cdt::all();
        $this->contratados = Contratado::all();
        $this->directivos= Directivo::all();
        $this->abogados= Abogado::all();
    }
    public function updateDirectivoId(){
         $cdt = $this->cdts->where("directivo_id", $this->directivo_id)->first();
        $this->id = optional($cdt)->id ?? null;
        $this->nombre_apellido = $cdt ? $cdt->directivo_name: null;
    }
}
