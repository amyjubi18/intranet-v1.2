<?php

namespace App\Exports;

use App\Models\InventarioOficina;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventarioOficinaExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = InventarioOficina::with(['proveedores', 'articulos', 'unidad_medidas']);

        if (!empty($this->filters['selected_ids'])) {
            $selectedIds = is_array($this->filters['selected_ids']) ? $this->filters['selected_ids'] : explode(',', $this->filters['selected_ids']);
            $query->whereIn('id', array_filter($selectedIds));
        }

        if (!empty($this->filters['busqueda'])) {
            $search = $this->filters['busqueda'];
            $query->where(function($q) use ($search) {
                $q->where('n_factura', 'like', '%' . $search . '%')
                  ->orWhereHas('proveedores', function($subQ) use ($search) {
                      $subQ->where('proveedor', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('articulos', function($subQ) use ($search) {
                      $subQ->where('articulo', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('unidad_medidas', function($subQ) use ($search) {
                      $subQ->where('unidad_medida', 'like', '%' . $search . '%');
                  });
            });
        }

        // Filtros combinados específicos
        if (!empty($this->filters['proveedore_id'])) {
            $query->where('proveedore_id', $this->filters['proveedore_id']);
        }

        if (!empty($this->filters['articulo_id'])) {
            $query->where('articulo_id', $this->filters['articulo_id']);
        }

        if (!empty($this->filters['unidad_medida_id'])) {
            $query->where('unidad_medida_id', $this->filters['unidad_medida_id']);
        }

        if (!empty($this->filters['n_factura'])) {
            $query->where('n_factura', 'like', '%' . $this->filters['n_factura'] . '%');
        }

        // Filtro por rango de fechas (fecha_ingreso o created_at)
        $dateColumn = (!empty($this->filters['tipo_fecha']) && ($this->filters['tipo_fecha'] === 'fecha_creacion' || $this->filters['tipo_fecha'] === 'created_at')) ? 'created_at' : 'fecha_ingreso';
        if (!empty($this->filters['fecha_desde']) && !empty($this->filters['fecha_hasta'])) {
            if ($dateColumn === 'created_at') {
                $query->whereBetween('created_at', [$this->filters['fecha_desde'] . ' 00:00:00', $this->filters['fecha_hasta'] . ' 23:59:59']);
            } else {
                $query->whereBetween('fecha_ingreso', [$this->filters['fecha_desde'], $this->filters['fecha_hasta']]);
            }
        } elseif (!empty($this->filters['fecha_desde'])) {
            if ($dateColumn === 'created_at') {
                $query->where('created_at', '>=', $this->filters['fecha_desde'] . ' 00:00:00');
            } else {
                $query->where('fecha_ingreso', '>=', $this->filters['fecha_desde']);
            }
        } elseif (!empty($this->filters['fecha_hasta'])) {
            if ($dateColumn === 'created_at') {
                $query->where('created_at', '<=', $this->filters['fecha_hasta'] . ' 23:59:59');
            } else {
                $query->where('fecha_ingreso', '<=', $this->filters['fecha_hasta']);
            }
        }

        // Aplicar filtros heredados según el tipo seleccionado
        if (!empty($this->filters['filterType']) && empty($this->filters['proveedore_id']) && empty($this->filters['articulo_id']) && empty($this->filters['unidad_medida_id'])) {
            switch ($this->filters['filterType']) {
                case 'fecha':
                    if (!empty($this->filters['fecha_desde']) && !empty($this->filters['fecha_hasta'])) {
                        $query->whereBetween('fecha_ingreso', [$this->filters['fecha_desde'], $this->filters['fecha_hasta']]);
                    } elseif (!empty($this->filters['fecha_desde'])) {
                        $query->where('fecha_ingreso', '>=', $this->filters['fecha_desde']);
                    } elseif (!empty($this->filters['fecha_hasta'])) {
                        $query->where('fecha_ingreso', '<=', $this->filters['fecha_hasta']);
                    }
                    break;
                case 'fecha_creacion':
                    if (!empty($this->filters['fecha_desde']) && !empty($this->filters['fecha_hasta'])) {
                        $query->whereBetween('created_at', [$this->filters['fecha_desde'], $this->filters['fecha_hasta']]);
                    } elseif (!empty($this->filters['fecha_desde'])) {
                        $query->where('created_at', '>=', $this->filters['fecha_desde']);
                    } elseif (!empty($this->filters['fecha_hasta'])) {
                        $query->where('created_at', '<=', $this->filters['fecha_hasta']);
                    }
                    break;
                case 'item':
                    if (!empty($this->filters['secondFilter'])) {
                        $query->where('id', $this->filters['secondFilter']);
                    }
                    break;
                case 'proveedore_id':
                    if (!empty($this->filters['secondFilter'])) {
                        $query->where('proveedore_id', $this->filters['secondFilter']);
                    }
                    break;
                case 'articulo_id':
                    if (!empty($this->filters['secondFilter'])) {
                        $query->where('articulo_id', $this->filters['secondFilter']);
                    }
                    break;
                case 'unidad_medida_id':
                    if (!empty($this->filters['secondFilter'])) {
                        $query->where('unidad_medida_id', $this->filters['secondFilter']);
                    }
                    break;
                case 'n_factura':
                    if (!empty($this->filters['secondFilter'])) {
                        $query->where('n_factura', $this->filters['secondFilter']);
                    } elseif (!empty($this->filters['n_factura'])) {
                        $query->where('n_factura', 'like', '%' . $this->filters['n_factura'] . '%');
                    }
                    break;
                case 'busqueda':
                    if (!empty($this->filters['busqueda'])) {
                        $search = $this->filters['busqueda'];
                        $query->where(function($q) use ($search) {
                            $q->where('descripcion', 'like', '%' . $search . '%')
                              ->orWhere('n_factura', 'like', '%' . $search . '%')
                              ->orWhereHas('proveedores', function($subQ) use ($search) {
                                  $subQ->where('proveedor', 'like', '%' . $search . '%');
                              })
                              ->orWhereHas('articulos', function($subQ) use ($search) {
                                  $subQ->where('articulo', 'like', '%' . $search . '%');
                              })
                              ->orWhereHas('unidad_medidas', function($subQ) use ($search) {
                                  $subQ->where('unidad_medida', 'like', '%' . $search . '%');
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
            'ITEM',
            'Artículo',
            'Fecha de Factura',
            'Unidad de Medida',
            'Proveedor',
            'Unidades',
            'Unidades Disponibles',
            'Total Disponibles',
            'Nº Factura',
            'Fecha de Ingreso',
        ];
    }

    public function map($inventario_oficina): array
    {
        // Calcular total de unidades disponibles del artículo
        $totalDisponiblesArticulo = InventarioOficina::where('articulo_id', $inventario_oficina->articulo_id)
            ->sum('unidades_disponibles');

        return [
            str_pad($inventario_oficina->articulo_id, 4, '0', STR_PAD_LEFT),
            $inventario_oficina->articulos ? $inventario_oficina->articulos->articulo : 'N/A',
            $inventario_oficina->fecha_ingreso ? \Carbon\Carbon::parse($inventario_oficina->fecha_ingreso)->format('d/m/Y') : '',
            $inventario_oficina->unidad_medidas ? $inventario_oficina->unidad_medidas->unidad_medida : 'N/A',
            $inventario_oficina->proveedores ? $inventario_oficina->proveedores->proveedor : 'N/A',
            $inventario_oficina->unidades,
            $inventario_oficina->unidades_disponibles,
            $totalDisponiblesArticulo,
            $inventario_oficina->n_factura,
            $inventario_oficina->created_at ? $inventario_oficina->created_at->format('d/m/Y H:i') : '',
        ];
    }
}
