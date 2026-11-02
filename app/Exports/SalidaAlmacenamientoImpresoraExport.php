<?php

namespace App\Exports;

use App\Models\SalidaAlmacenamientoImpresora;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalidaAlmacenamientoImpresoraExport implements FromCollection, WithHeadings, WithStyles
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = SalidaAlmacenamientoImpresora::with('almacenamientoImpresora.cartuchos', 'almacenamientoImpresora.colores', 'almacenamientoImpresora.impresoras', 'almacenamientoImpresora.fabricantes', 'distribucionAlmacenamientoImpresora.gerencia.area');

        // Apply q->where('cantidad', 'like', "%{$busqueda}%");



        return $query->get()->map(function ($item) {
            return [
                'ITEM' => str_pad($item->almacenamientoImpresora ? $item->almacenamientoImpresora->impresoras->id : 'N/A', 4, '0', STR_PAD_LEFT),
                'Fecha Salida' => $item->fecha_salida,
                'Consumible' => $item->almacenamientoImpresora ? $item->almacenamientoImpresora->cartuchos->cartucho : 'N/A',
                'Tipo de Consumible' => $item->almacenamientoImpresora ? $item->almacenamientoImpresora->colores->colore : 'N/A',
                'Impresora' => $item->almacenamientoImpresora ? $item->almacenamientoImpresora->impresoras->impresora : 'N/A',
                'Tipo de Impresora' => $item->almacenamientoImpresora ? $item->almacenamientoImpresora->fabricantes->fabricante : 'N/A',
                'Gerencia' => $item->distribucionAlmacenamientoImpresora ? $item->distribucionAlmacenamientoImpresora->gerencia->gerencia : 'N/A',
                'Area' => $item->distribucionAlmacenamientoImpresora ? $item->distribucionAlmacenamientoImpresora->gerencia->area->area : 'N/A',
                'Stock Minimo' => $item->almacenamientoImpresora ? $item->almacenamientoImpresora->cartuchos->stock_minimo : 'N/A',
                'Cantidad' => $item->cantidad,
                'Unidades Disponibles' => $item->historico_unidades_disponibles,
                'Unidades Asignadas Originales' => $item->historico_unidades_asignadas_originales,
                'Unidades Asignadas Disponibles' => $item->historico_unidades_asignadas_restantes,
                'Ajuste Inventario' => $item->ajuste_inventario,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ITEM',
            'Fecha Salida',
            'Consumible',

            'Tipo de Consumible',
            'Impresora',
            'Tipo de Impresora',
            'Gerencia',
            'Area',
            'Stock Minimo',
            'Cantidad',
            'Unidades Disponibles',
            'Unidades Asignadas Originales',
            'Unidades Asignadas Disponibles',
            'Ajuste Inventario',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => 'ADD8E6',
                    ],
                ],
            ],
        ];
    }
}
