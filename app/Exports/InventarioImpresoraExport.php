<?php

namespace App\Exports;

use App\Models\InventarioImpresora;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InventarioImpresoraExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = InventarioImpresora::with('cartuchos', 'colores', 'impresoras', 'fabricantes', 'areas', 'gerencias', 'proveedores');

        // Apply the same filters as in index
        if ($this->request->filled('fecha_desde') && $this->request->filled('fecha_hasta')) {
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
                case 'gerencia_id':
                    $query->where('gerencia_id', $secondFilter);
                    break;
                case 'area_id':
                    $query->where('area_id', $secondFilter);
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
                  ->orWhereHas('gerencias', function ($q5) use ($busqueda) {
                      $q5->where('gerencia', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('areas', function ($q6) use ($busqueda) {
                      $q6->where('area', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('fabricantes', function ($q7) use ($busqueda) {
                      $q7->where('fabricante', 'like', "%{$busqueda}%");
                  });
            });
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Item',
            'Fecha de Ingreso',
            'Consumible',
            'Color',
            'Impresora',
            'Tipo de consumible',
            'Proveedor',
            'Gerencia',
            'Área',
            'Unidades',
            'Unidades Disponibles',
            'Factura',
        ];
    }

    public function map($inventario): array
    {
        return [
            str_pad($inventario->id, 4, '0', STR_PAD_LEFT),
            $inventario->fecha_ingreso,
            $inventario->cartuchos ? $inventario->cartuchos->cartucho : 'N/A',
            $inventario->colores ? $inventario->colores->colore : 'N/A',
            $inventario->impresoras ? $inventario->impresoras->impresora : 'N/A',
            $inventario->fabricantes ? $inventario->fabricantes->fabricante : 'N/A',
            $inventario->proveedores ? $inventario->proveedores->proveedor : 'N/A',
            $inventario->gerencias ? $inventario->gerencias->gerencia : 'N/A',
            $inventario->areas ? $inventario->areas->area : 'N/A',
            $inventario->unidades,
            $inventario->unidades_disponibles,
            $inventario->n_factura,
        ];
    }
}
