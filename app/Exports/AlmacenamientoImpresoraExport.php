<?php

namespace App\Exports;

use App\Models\AlmacenamientoImpresora;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AlmacenamientoImpresoraExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = AlmacenamientoImpresora::with('cartuchos', 'colores', 'impresoras', 'fabricantes', 'proveedores');

        // Apply the same filters as in index
        if ($this->request->filled('fecha_desde_created_at') && $this->request->filled('fecha_hasta_created_at')) {
            $query->whereBetween('created_at', [$this->request->input('fecha_desde_created_at'), $this->request->input('fecha_hasta_created_at')]);
        } elseif ($this->request->filled('fecha_desde') && $this->request->filled('fecha_hasta')) {
            $query->whereBetween('fecha_ingreso', [$this->request->input('fecha_desde'), $this->request->input('fecha_hasta')]);
        }
        if ($this->request->filled('filterType') && $this->request->filled('secondFilter')) {
            $filterType = $this->request->input('filterType');
            $secondFilter = $this->request->input('secondFilter');
            switch ($filterType) {
                case 'cartucho_id':
                    $query->where('cartucho_id', $secondFilter);
                    break;
                case 'colore_id':
                    $query->where('colore_id', $secondFilter);
                    break;
                case 'impresora_id':
                    $query->where('impresora_id', $secondFilter);
                    break;
                case 'proveedore_id':
                    $query->where('proveedore_id', $secondFilter);
                    break;

                case 'fabricante_id':
                    $query->where('fabricante_id', $secondFilter);
                    break;
            }
        }
        if ($this->request->filled('n_factura')) {
            $query->where('n_factura', 'like', '%' . $this->request->input('n_factura') . '%');
        }
        if ($this->request->filled('busqueda')) {
            $busqueda = $this->request->input('busqueda');
            $query->where(function ($q) use ($busqueda) {
                $q->where('n_factura', 'like', "%{$busqueda}%")
                  ->orWhereHas('cartuchos', function ($q2) use ($busqueda) {
                      $q2->where('cartucho', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('impresoras', function ($q3) use ($busqueda) {
                      $q3->where('impresora', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('proveedores', function ($q4) use ($busqueda) {
                      $q4->where('proveedor', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('fabricantes', function ($q5) use ($busqueda) {
                      $q8->where('fabricante', 'like', "%{$busqueda}%");
                  });
            });
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Item',
            'Fecha de Factura',
            'Fecha de Ingreso',
            'Consumible',
            'Tipo de Consumible',
            'Impresora',
            'Tipo de Impresora',
            'Proveedor',
            'Stock Min',
            'Unidades',
            'Unidades Disponibles',
            'Factura',
        ];
    }

    public function map($almacenamiento): array
    {
        return [
            str_pad($almacenamiento->impresoras->id, 4, '0', STR_PAD_LEFT),
            $almacenamiento->fecha_ingreso,
            \Carbon\Carbon::parse($almacenamiento->created_at)->format('d/m/Y'),
            $almacenamiento->cartuchos ? $almacenamiento->cartuchos->cartucho : 'N/A',
            $almacenamiento->colores ? $almacenamiento->colores->colore : 'N/A',
            $almacenamiento->impresoras ? $almacenamiento->impresoras->impresora : 'N/A',
            $almacenamiento->fabricantes ? $almacenamiento->fabricantes->fabricante : 'N/A',
            $almacenamiento->proveedores ? $almacenamiento->proveedores->proveedor : 'N/A',
            $almacenamiento->cartuchos ? $almacenamiento->cartuchos->stock_min : 'N/A',
            $almacenamiento->unidades,
            $almacenamiento->unidades_disponibles,
            $almacenamiento->n_factura,
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
