<?php

namespace App\Http\Livewire;
use App\Models\Biene;
use App\Models\Color;
use App\Models\Condicion;
use App\Models\EstadoDelUso;
use App\Models\FormaAdquisicion;
use App\Models\UnidadAdministrativa;
use App\Models\UsuarioAdministrativo;
use App\Models\UsuarioDeUso;
use Livewire\Component;
use Carbon\Carbon;



class ReportesController extends Component
{
    public $data, $reportType, $usuario_de_uso_id, $dateFrom, $dateTo, $biene_id;
    public function mount(){
        $this->data = [];
        $this->reportType = 0;
        $this->usuario_de_uso_id =0;
        $this->biene_id =0;
    }

    public function render()
    {
        $this->ReportesDias();
        return view('livewire.reportes.componentes',[
            'usuario_de_usos' =>UsuarioDeUso::orderBy('usuario_de_uso', 'asc')->get()]);
    }

    public function ReportesDias(){
        if($this->reportType == 0)//ventas del dia
        {
            $from = Carbon::parse(Carbon::now())->format('Y-m-d') . ' 00:00:00';
            $to = Carbon::parse(Carbon::now())->format('Y-m-d') . ' 23:59:59';
        }else{
            $from = Carbon::parse($this->dateFrom)->format('Y-m-d') . ' 00:00:00';
            $to = Carbon::parse($this->dateTo)->format('Y-m-d') . ' 23:59:59';
        }
        if($this->reportType == 1 && ($this->dateFrom == '' || $this->dateTo == '')){
            return;
        }
        if($this->usuario_de_uso_id == 0){
            $this->data = Biene::join('usuario_de_usos as u', 'u.id', 'bienes.usuario_de_uso_id')->select('bienes.*', 'u.usuario_de_uso as user')->whereBetween('bienes.fecha_adquisicion', [$from, $to])->get();
        }else{
            $this->data = Biene::join('usuario_de_usos as u', 'u.id', 'bienes.usuario_de_uso_id')->select('bienes.*', 'u.usuario_de_uso as user')->whereBetween('bienes.fecha_adquisicion', [$from, $to])->where('usuario_de_uso_id', $this->usuario_de_uso_id)->get();
        }
    }
}
