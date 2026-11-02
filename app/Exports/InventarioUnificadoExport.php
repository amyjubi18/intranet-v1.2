<?php

namespace App\Exports;

use App\Models\InventarioImpresora;
use App\Models\InventarioOficina;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InventarioUnificadoExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        // Query para inventario de impresoras
        $queryImpresora = InventarioImpresora::with('cartuchos', 'colores', 'impresoras', 'fabricantes', 'areas', 'gerencias', 'proveedores')
            ->select('*', DB::raw("'impresora' as tipo"));

        // Query para inventario de oficina
        $queryOficina = InventarioOficina::with('articulos', 'unidad_medidas', 'areas', 'gerencias', 'proveedores')
            ->select('*', DB::raw("'oficina' as tipo"));

        // Aplicar filtros
        if (isset($this->filters['fecha_desde']) && isset($this->filters['fecha_hasta'])) {
            $queryImpresora->whereBetween('fecha_ingreso', [$this->filters['fecha_desde'], $this->filters['fecha_hasta']]);
            $queryOficina->whereBetween('fecha_ingreso', [$this->filters['fecha_desde'], $this->filters['fecha_hasta']]);
        }

        if (isset($this->filters['filterType']) && isset($this->filters['secondFilter'])) {
            $filterType = $this->filters['filterType'];
            $secondFilter = $this->filters['secondFilter'];
            switch ($filterType) {
                case 'proveedore_id':
                    $queryImpresora->where('proveedore_id', $secondFilter);
                    $queryOficina->where('proveedore_id', $secondFilter);
                    break;
                case 'gerencia_id':
                    $queryImpresora->where('gerencia_id', $secondFilter);
                    $queryOficina->where('gerencia_id', $secondFilter);
                    break;
                case 'area_id':
                    $queryImpresora->where('area_id', $secondFilter);
                    $queryOficina->where('area_id', $secondFilter);
                    break;

                case 'cartucho_id':
                    $queryImpresora->where('cartucho_id', $secondFilter);
                    break;
                case 'colore_id':
                    $queryImpresora->where('colore_id', $secondFilter);
                    break;
                case 'impresora_id':
                    $queryImpresora->where('impresora_id', $secondFilter);
                    break;
                case 'fabricante_id':
                    $queryImpresora->where('fabricante_id', $secondFilter);
                    break;
                case 'articulo_id':
                    $queryOficina->where('articulo_id', $secondFilter);
                    break;
                case 'unidad_medida_id':
                    $queryOficina->where('unidad_medida_id', $secondFilter);
                    break;
            }
        }

        if (isset($this->filters['n_factura'])) {
            $queryImpresora->where('n_factura', 'like', '%' . $this->filters['n_factura'] . '%');
            $queryOficina->where('n_factura', 'like', '%' . $this->filters['n_factura'] . '%');
        }

        if (isset($this->filters['busqueda'])) {
            $busqueda = $this->filters['busqueda'];
            $queryImpresora->where(function ($q) use ($busqueda) {
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
            $queryOficina->where(function ($q) use ($busqueda) {
                $q->where('descripcion', 'like', "%{$busqueda}%")
                  ->orWhere('n_factura', 'like', "%{$busqueda}%")
                  ->orWhereHas('articulos', function ($q2) use ($busqueda) {
                      $q2->where('articulo', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('proveedores', function ($q4) use ($busqueda) {
                      $q4->where('proveedor', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('gerencias', function ($q5) use ($busqueda) {
                      $q5->where('gerencia', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('areas', function ($q6) use ($busqueda) {
                      $q6->where('area', 'like', "%{$busqueda}%");
                  });
            });
        }

        // Unir los resultados
        $inventarioImpresora = $queryImpresora->get();
        $inventarioOficina = $queryOficina->get();
        return $inventarioImpresora->concat($inventarioOficina)->sortByDesc('fecha_ingreso');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tipo',
            'Fecha de Ingreso',
            'Factura',
            'Unidades',
            'Unidades Disponibles',
            'Consumible/Cartucho',
            'Color',
            'Impresora',
            'Tipo Consumible',
            'Artículo',
            'Unidad de Medida',
            'Descripción',
            'Proveedor',
            'Gerencia',
            'Área',
        ];
    }

    public function map($item): array
    {
        return [
            $item->id,
            ucfirst($item->tipo),
            \Carbon\Carbon::parse($item->fecha_ingreso)->format('d/m/Y'),
            $item->n_factura,
            $item->unidades,
            $item->unidades_disponibles,
            $item->tipo == 'impresora' ? ($item->cartuchos ? $item->cartuchos->cartucho : 'N/A') : '',
            $item->tipo == 'impresora' ? ($item->colores ? $item->colores->colore : 'N/A') : '',
            $item->tipo == 'impresora' ? ($item->impresoras ? $item->impresoras->impresora : 'N/A') : '',
            $item->tipo == 'impresora' ? ($item->fabricantes ? $item->fabricantes->fabricante : 'N/A') : '',
            $item->tipo == 'oficina' ? ($item->articulos ? $item->articulos->articulo : 'N/A') : '',
            $item->tipo == 'oficina' ? ($item->unidad_medidas ? $item->unidad_medidas->unidad_medida : 'N/A') : '',
            $item->tipo == 'oficina' ? ($item->descripcion ?? 'N/A') : '',
            $item->proveedores ? $item->proveedores->proveedor : 'N/A',
            $item->gerencias ? $item->gerencias->gerencia : 'N/A',
            $item->areas ? $item->areas->area : 'N/A',
        ];
    }
}
