<?php

namespace App\Exports;

use App\Models\DistribucionAlmacenamientoImpresora;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Http\Request;

class DistribucionAlmacenamientoImpresoraExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = DistribucionAlmacenamientoImpresora::with('almacenamientoImpresora.cartuchos', 'gerencia.area');

        if ($this->request->filled('selected_ids')) {
            $ids = is_array($this->request->selected_ids) ? $this->request->selected_ids : explode(',', $this->request->selected_ids);
            $query->whereIn('distribucion_almacenamiento_impresoras.id', array_filter($ids));
        }

        if ($this->request->filled('gerencia')) {
            $query->where('gerencia_id', $this->request->gerencia);
        }
        if ($this->request->filled('area')) {
            $query->whereHas('gerencia', function($q) {
                $q->where('area_id', $this->request->area);
            });
        }
        if ($this->request->filled('cartucho')) {
            $query->whereHas('almacenamientoImpresora.cartuchos', function($subQ) {
                $subQ->where('id', $this->request->cartucho);
            });
        }
        if ($this->request->filled('impresora')) {
            $query->whereHas('almacenamientoImpresora', function($q) {
                $q->where('impresora_id', $this->request->impresora);
            });
        }
        if ($this->request->filled('colore')) {
            $query->whereHas('almacenamientoImpresora.colores', function($subQ) {
                $subQ->where('id', $this->request->colore);
            });
        }
        if ($this->request->filled('fabricante')) {
            $query->whereHas('almacenamientoImpresora.fabricantes', function($subQ) {
                $subQ->where('id', $this->request->fabricante);
            });
        }
        if ($this->request->filled('fecha_desde') && $this->request->filled('fecha_hasta')) {
            $query->whereBetween('created_at', [$this->request->fecha_desde, $this->request->fecha_hasta]);
        }

        // Apply filters if needed
        if ($this->request->filled('almacenamiento_impresora_id')) {
            $query->where('almacenamiento_impresora_id', $this->request->input('almacenamiento_impresora_id'));
        }
        if ($this->request->filled('gerencia_id')) {
            $query->where('gerencia_id', $this->request->input('gerencia_id'));
        }

        // Apply advanced filters
        if ($this->request->filled('filterType')) {
            switch ($this->request->filterType) {

                case 'almacenamiento_impresora_id':
                    if ($this->request->filled('secondFilter')) {
                        $query->where('almacenamiento_impresora_id', $this->request->secondFilter);
                    }
                    break;
                case 'gerencia_id':
                    if ($this->request->filled('secondFilter')) {
                        $query->where('gerencia_id', $this->request->secondFilter);
                    }
                    break;
                case 'busqueda':
                    if ($this->request->filled('busqueda')) {
                        $search = $this->request->busqueda;
                        $query->where(function($q) use ($search) {
                            $q->whereHas('almacenamientoImpresora.cartuchos', function($subQ) use ($search) {
                                $subQ->where('cartucho', 'like', '%' . $search . '%');
                            })
                            ->orWhereHas('gerencia', function($subQ) use ($search) {
                                $subQ->where('gerencia', 'like', '%' . $search . '%');
                            });
                        });
                    }
                    break;
            }
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Item',
            'Consumible',
            'Tipo de Consumible',
            'Impresora',
            'Tipo de Impresora',
            'Gerencia',
            'Área',
            'Stock Min',
            'Unidades',
            'Unidades Asignadas',
            'Fecha de Distribución'
        ];
    }

    public function map($distribucion): array
    {
        return [
            str_pad($distribucion->impresoras->id, 4, '0', STR_PAD_LEFT),
            $distribucion->almacenamientoImpresora->cartuchos->cartucho ?? '',
            $distribucion->almacenamientoImpresora->colores ? $distribucion->almacenamientoImpresora->colores->colore : '',
            $distribucion->almacenamientoImpresora->impresoras ? $distribucion->almacenamientoImpresora->impresoras->impresora : '',
            $distribucion->almacenamientoImpresora->fabricantes ? $distribucion->almacenamientoImpresora->fabricantes->fabricante : '',
            $distribucion->gerencia->gerencia ?? '',
            $distribucion->gerencia->area->area ?? '',
            $distribucion->almacenamientoImpresora->cartuchos->stock_min ?? '',
            $distribucion->unidades_asignadas_originales,
            $distribucion->unidades_asignadas,
            $distribucion->created_at
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
