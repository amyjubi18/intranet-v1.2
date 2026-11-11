<?php

namespace App\Exports;

use App\Models\SalidaImpresora;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Http\Request;

class SalidaImpresoraExport implements FromCollection, WithHeadings
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = SalidaImpresora::with('cartuchos', 'colores', 'impresoras', 'fabricantes', 'areas', 'gerencias', 'inventario_impresoras');

        if ($this->request->filled('selected_ids')) {
            $ids = is_array($this->request->selected_ids) ? $this->request->selected_ids : explode(',', $this->request->selected_ids);
            $query->whereIn('salida_impresoras.id', array_filter($ids));
        }

        // Apply filters similar to controller index
        if ($this->request->filled('fecha_desde') && $this->request->filled('fecha_hasta')) {
            $query->whereBetween('fecha_salida', [$this->request->input('fecha_desde'), $this->request->input('fecha_hasta')]);
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

        if ($this->request->filled('cartucho_id')) {
            $query->where('cartucho_id', $this->request->cartucho_id);
        }
        if ($this->request->filled('colore_id')) {
            $query->where('colore_id', $this->request->colore_id);
        }
        if ($this->request->filled('impresora_id')) {
            $query->where('impresora_id', $this->request->impresora_id);
        }
        if ($this->request->filled('gerencia_id')) {
            $query->where('gerencia_id', $this->request->gerencia_id);
        }
        if ($this->request->filled('area_id')) {
            $query->where('area_id', $this->request->area_id);
        }
        if ($this->request->filled('fabricante_id')) {
            $query->where('fabricante_id', $this->request->fabricante_id);
        }
        if ($this->request->filled('n_factura')) {
            $query->where('n_factura', 'like', '%' . $this->request->input('n_factura') . '%');
        }
        if ($this->request->filled('busqueda')) {
            $busqueda = $this->request->input('busqueda');
            $query->where(function ($q) use ($busqueda) {
                $q->where('cantidad', 'like', "%{$busqueda}%")
                  ->orWhereHas('cartuchos', function ($q2) use ($busqueda) {
                      $q2->where('cartucho', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('impresoras', function ($q3) use ($busqueda) {
                      $q3->where('impresora', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('gerencias', function ($q5) use ($busqueda) {
                      $q5->where('gerencia', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('areas', function ($q6) use ($busqueda) {
                      $q6->where('area', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('fabricantes', function ($q8) use ($busqueda) {
                      $q8->where('fabricante', 'like', "%{$busqueda}%");
                  });
            });
        }

        $salida_impresoras = $query->get();

        // Map to exportable collection
        return $salida_impresoras->map(function ($item) {
            return [
                'Item' => $item->impresoras ? $item->impresoras->id : '',
                'Fecha Salida' => $item->fecha_salida,
                'Fecha Ingreso' => $item->inventario_impresoras ? $item->inventario_impresoras->fecha_ingreso : '',
                'Cantidad Retirada' => $item->cantidad,
                'Unidades Disponibles' => $item->historico_unidades_disponibles,
                'Consumible' => $item->cartuchos ? $item->cartuchos->cartucho : '',
                'Tipo de Consumible' => $item->colores ? $item->colores->colore : '',
                'Impresora' => $item->impresoras ? $item->impresoras->impresora : '',
                'Tipo de Impresora' => $item->fabricantes ? $item->fabricantes->fabricante : '',
                'Área' => $item->areas ? $item->areas->area : '',
                'Gerencia' => $item->gerencias ? $item->gerencias->gerencia : '',
                'Ajuste Inventario' => $item->ajuste_inventario ? 'Sí' : 'No',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Fecha Salida',
            'Fecha Ingreso',
            'Cantidad Retirada',
            'Unidades Disponibles',
            'Consumible',
            'Color',
            'Impresora',
            'Tipo Consumible',
            'Área',
            'Gerencia',
            'Ajuste Inventario',
        ];
    }
}
