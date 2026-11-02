<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\BienesExport;
use Carbon\Carbon;
use App\Models\Biene;
use App\Models\Color;
use App\Models\Condicion;
use App\Models\EstadoDelUso;
use App\Models\FormaAdquisicion;
use App\Models\UnidadAdministrativa;
use App\Models\UsuarioAdministrativo;
use App\Models\UsuarioDeUso;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class ExportarController extends Controller
{
    public function reporteExcel ($usuario_de_uso_id, $reportType, $dateFrom = null, $dateTo =null ){
        $reportName ='Reporte de bienes_' .  uniqid() . '.xlsx';
        return Excel::download(new BienesExport($usuario_de_uso_id, $reportType, $dateFrom, $dateTo), $reportName );
    }
}
