<?php

namespace App\Exports;

use App\Models\SalidaOficina;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Http\Request;

class SalidaOficinaExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = SalidaOficina::with(['articulos', 'gerencias', 'areas', 'unidad_medidas', 'users'])
            ->leftJoin('inventario_oficinas', 'salida_oficinas.inventario_oficina_id', '=', 'inventario_oficinas.id')
            ->select('salida_oficinas.*', 'inventario_oficinas.unidades_disponibles');

        // Aplicar filtros igual que en el índice
        if (!empty($this->filters['filterType'])) {
            switch ($this->filters['filterType']) {
                case 'fecha':
                    if (!empty($this->filters['fecha_desde']) && !empty($this->filters['fecha_hasta'])) {
                        $query->whereBetween('fecha_salida', [$this->filters['fecha_desde'], $this->filters['fecha_hasta']]);
                    } elseif (!empty($this->filters['fecha_desde'])) {
                        $query->where('fecha_salida', '>=', $this->filters['fecha_desde']);
                    } elseif (!empty($this->filters['fecha_hasta'])) {
                        $query->where('fecha_salida', '<=', $this->filters['fecha_hasta']);
                    }
                    break;

                case 'fecha_ingreso':
                    if (!empty($this->filters['fecha_ingreso_desde']) && !empty($this->filters['fecha_ingreso_hasta'])) {
                        $query->whereHas('inventario_oficinas', function($q) {
                            $q->whereBetween('fecha_ingreso', [$this->filters['fecha_ingreso_desde'], $this->filters['fecha_ingreso_hasta']]);
                        });
                    } elseif (!empty($this->filters['fecha_ingreso_desde'])) {
                        $query->whereHas('inventario_oficinas', function($q) {
                            $q->where('fecha_ingreso', '>=', $this->filters['fecha_ingreso_desde']);
                        });
                    } elseif (!empty($this->filters['fecha_ingreso_hasta'])) {
                        $query->whereHas('inventario_oficinas', function($q) {
                            $q->where('fecha_ingreso', '<=', $this->filters['fecha_ingreso_hasta']);
                        });
                    }
                    break;

                case 'gerencia':
                    if (!empty($this->filters['secondFilter'])) {
                        $query->where('gerencia_id', $this->filters['secondFilter']);
                    }
                    break;

                case 'area':
                    if (!empty($this->filters['secondFilter'])) {
                        $query->where('area_id', $this->filters['secondFilter']);
                    }
                    break;

                case 'articulo':
                    if (!empty($this->filters['secondFilter'])) {
                        $query->where('salida_oficinas.articulo_id', $this->filters['secondFilter']);
                    }
                    break;

                case 'busqueda':
                    if (!empty($this->filters['busqueda'])) {
                        $search = $this->filters['busqueda'];
                        $query->where(function($q) use ($search) {
                            $q->where('cantidad', 'like', '%' . $search . '%')
                              ->orWhere('ajuste_inventario', 'like', '%' . $search . '%')
                              ->orWhereHas('articulos', function($subQ) use ($search) {
                                  $subQ->where('articulo', 'like', '%' . $search . '%');
                              })
                              ->orWhereHas('gerencias', function($subQ) use ($search) {
                                  $subQ->where('gerencia', 'like', '%' . $search . '%');
                              })
                              ->orWhereHas('areas', function($subQ) use ($search) {
                                  $subQ->where('area', 'like', '%' . $search . '%');
                              })
                              ->orWhereHas('users', function($subQ) use ($search) {
                                  $subQ->where('name', 'like', '%' . $search . '%');
                              });
                        });
                    }
                    break;
            }
        }

        $salidas = $query->orderBy('salida_oficinas.id', 'desc')->get();

        // Calcular sumaUnidadesPorArticulo (igual que en el índice)
        $sumaUnidadesPorArticulo = [];
        $articulos = \App\Models\Articulo::all();
        foreach ($articulos as $articulo) {
            $sumaUnidadesPorArticulo[$articulo->id] = \App\Models\InventarioOficina::where('articulo_id', $articulo->id)
                ->sum('unidades');
        }

        // Agregar campos calculados a cada registro
        $salidas->each(function ($item) use ($sumaUnidadesPorArticulo) {
            $item->stockTotalReal = $item->historico_unidades_disponibles ?? $item->unidades_disponibles ?? 0;
            $item->sumaUnidades = $sumaUnidadesPorArticulo[$item->articulo_id] ?? 0;
        });

        return $salidas;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Fecha de Salida',
            'Artículo',
            'Unidad de Medida',
            'Gerencia',
            'Área',
            'Solicitante',
            'Cantidad',
            'Stock Actual',
            'Total Inicial',
            'Ajuste Inventario',
            'Fecha de Registro',
        ];
    }

    public function map($salida_oficina): array
    {
        $stockTotalReal = $salida_oficina->stockTotalReal ?? 0;
        $sumaUnidades = $salida_oficina->sumaUnidades ?? 0;

        return [
            str_pad($salida_oficina->id, 4, '0', STR_PAD_LEFT),
            $salida_oficina->fecha_salida ? \Carbon\Carbon::parse($salida_oficina->fecha_salida)->format('d/m/Y') : '',
            $salida_oficina->articulos ? $salida_oficina->articulos->articulo : '',
            $salida_oficina->unidad_medidas ? $salida_oficina->unidad_medidas->unidad_medida : '',
            $salida_oficina->gerencias ? $salida_oficina->gerencias->gerencia : '',
            $salida_oficina->areas ? $salida_oficina->areas->area : '',
            $salida_oficina->users ? $salida_oficina->users->name : '',
            $salida_oficina->cantidad,
            $stockTotalReal,
            $sumaUnidades,
            $salida_oficina->ajuste_inventario ?? '',
            $salida_oficina->created_at ? $salida_oficina->created_at->format('d/m/Y H:i') : '',
        ];
    }
}

