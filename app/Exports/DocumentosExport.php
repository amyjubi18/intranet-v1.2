<?php

namespace App\Exports;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Models\Documento;
use Maatwebsite\Excel\Concerns\FromCollection;

class DocumentosExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    /* public function collection()
    {
        return Documento::all();
    } */
    public function view(): View
    {
        return view('administracion.docs.documentoexport',[
            'documentos' => Documento::all()
        ]);
    }
}
