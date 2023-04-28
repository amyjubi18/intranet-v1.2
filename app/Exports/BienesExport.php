<?php

namespace App\Exports;

use App\Models\Biene;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

 class BienesExport implements FromView  /* FromCollection, WithHeadings, WithMapping */
{
    /* private $bienes_ids;
    public function __construct($bienes_ids){
        $this->bienes_ids;
    }
    public function headings():array{
        return [
            'SEDE',
            'UNIDAD ADMINISTRATIVA',
            'N° INTERNO DEL BIEN',
            'DESCRIPCIÓN',
            'FORMA DE ADQUISICIÓN',
            'FECHA DE ADQUISICIÓN',
            'N° DE FACTURA',
            'VALOR DE FACTURA',
            'MONEDA',
            'ESTADO DEL USO',
            'CONDICIÓN FÍSICA',
            'MARCA',
            'MODELO',
            'SERIAL',
            'CATEGORÍA GENERAL',
            'SUBCATEGORÍA',
            'CATEGORÍA ESPECÍFICA',
            'CÓDIGO CONTABLE'
        ];
    }
    public function map($biene):array{
        return [
            $biene->sede,
            $biene->unidad_administrativas->unidad_administrativa,
            $biene->n_interno_bien,
            $biene->descripcion,
            $biene->forma_adquisicions->forma_adquisicion,
            $biene->fecha_adquisicion,
            $biene->n_factura,
            $biene->valor_factura,
            $biene->moneda,
            $biene->estado_del_usos->estado_del_uso,
            $biene->condicion_fisica,
            $biene->marca,
            $biene->modelo,
            $biene->serial,
            $biene->categoria_generals->categoria_general,
            $biene->sub_categorias->sub_categoria,
            $biene->categoria_especificas->categoria_especifica,
            $biene->codigo_contable
        ];
    }

    public function collection(){
        return Biene::with('unidad_administrativas', 'forma_adquisicions', 'estado_del_usos', 'categoria_generals', 'sub_categorias', 'categoria_especificas')->find($this->bienes_ids);
    } */
    /**
    * @return \Illuminate\Support\Collection
    */

      public function view(): View
    {
        return view('administracion.bienes_publicos.bienespublicosexport',[
            'bienes' => Biene::all()
        ]);
    }
}



