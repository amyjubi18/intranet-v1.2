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
        $query = SalidaAlmacenamientoImpresora::with([
            'almacenamientoImpresora.cartuchos',
            'almacenamientoImpresora.colores',
            'almacenamientoImpresora.impresoras',
            'almacenamientoImpresora.fabricantes',
            'distribucionAlmacenamientoImpresora.gerencia.area'
        ]);

        if ($this->request->filled('selected_ids')) {
            $ids = is_array($this->request->selected_ids) ? $this->request->selected_ids : explode(',', $this->request->selected_ids);
            $query->whereIn('id', array_filter($ids));
        }

        if ($this->request->filled('fecha_desde') && $this->request->filled('fecha_hasta')) {
            $query->whereBetween('fecha_salida', [$this->request->input('fecha_desde'), $this->request->input('fecha_hasta')]);
        } elseif ($this->request->filled('fecha_desde')) {
            $query->whereDate('fecha_salida', '>=', $this->request->input('fecha_desde'));
        } elseif ($this->request->filled('fecha_hasta')) {
            $query->whereDate('fecha_salida', '<=', $this->request->input('fecha_hasta'));
        }

        $gerenciaParam = $this->request->input('gerencia_id') ?: $this->request->input('gerencia');
        if ($gerenciaParam) {
            $query->whereHas('distribucionAlmacenamientoImpresora', function ($q) use ($gerenciaParam) {
                $q->where('gerencia_id', $gerenciaParam);
            });
        }

        $areaParam = $this->request->input('area_id') ?: $this->request->input('area');
        if ($areaParam) {
            $query->whereHas('distribucionAlmacenamientoImpresora.gerencia', function ($q) use ($areaParam) {
                $q->where('area_id', $areaParam);
            });
        }

        $cartuchoParam = $this->request->input('cartucho_id') ?: $this->request->input('cartucho');
        if ($cartuchoParam) {
            $query->whereHas('almacenamientoImpresora', function ($q) use ($cartuchoParam) {
                $q->where('cartucho_id', $cartuchoParam);
            });
        }

        $impresoraParam = $this->request->input('impresora_id') ?: $this->request->input('impresora');
        if ($impresoraParam) {
            $query->whereHas('almacenamientoImpresora', function ($q) use ($impresoraParam) {
                $q->where('impresora_id', $impresoraParam);
            });
        }

        $coloreParam = $this->request->input('colore_id') ?: $this->request->input('colore');
        if ($coloreParam) {
            $query->whereHas('almacenamientoImpresora', function ($q) use ($coloreParam) {
                $q->where('colore_id', $coloreParam);
            });
        }

        $fabricanteParam = $this->request->input('fabricante_id') ?: $this->request->input('fabricante');
        if ($fabricanteParam) {
            $query->whereHas('almacenamientoImpresora', function ($q) use ($fabricanteParam) {
                $q->where('fabricante_id', $fabricanteParam);
            });
        }

        if ($this->request->filled('busqueda')) {
            $busqueda = trim($this->request->input('busqueda'));
            $query->where(function ($q) use ($busqueda) {
                $q->where('cantidad', 'like', "%{$busqueda}%");
            });
        }

        return $query->get()->map(function ($item) {
            return [
                'ITEM' => str_pad($item->almacenamientoImpresora && $item->almacenamientoImpresora->impresoras ? $item->almacenamientoImpresora->impresoras->id : 'N/A', 4, '0', STR_PAD_LEFT),
                'Fecha Salida' => $item->fecha_salida,
                'Consumible' => $item->almacenamientoImpresora && $item->almacenamientoImpresora->cartuchos ? $item->almacenamientoImpresora->cartuchos->cartucho : 'N/A',
                'Tipo de Consumible' => $item->almacenamientoImpresora && $item->almacenamientoImpresora->colores ? $item->almacenamientoImpresora->colores->colore : 'N/A',
                'Impresora' => $item->almacenamientoImpresora && $item->almacenamientoImpresora->impresoras ? $item->almacenamientoImpresora->impresoras->impresora : 'N/A',
                'Tipo de Impresora' => $item->almacenamientoImpresora && $item->almacenamientoImpresora->fabricantes ? $item->almacenamientoImpresora->fabricantes->fabricante : 'N/A',
                'Gerencia' => $item->distribucionAlmacenamientoImpresora && $item->distribucionAlmacenamientoImpresora->gerencia ? $item->distribucionAlmacenamientoImpresora->gerencia->gerencia : 'N/A',
                'Area' => $item->distribucionAlmacenamientoImpresora && $item->distribucionAlmacenamientoImpresora->gerencia && $item->distribucionAlmacenamientoImpresora->gerencia->area ? $item->distribucionAlmacenamientoImpresora->gerencia->area->area : 'N/A',
                'Stock Minimo' => $item->almacenamientoImpresora && $item->almacenamientoImpresora->cartuchos ? $item->almacenamientoImpresora->cartuchos->stock_min : 'N/A',
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
