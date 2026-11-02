<?php

namespace App\Exports;

use App\Models\Biene;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;

class BienesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithCustomStartCell, WithTitle, WithStyles
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Biene::with(['unidad_administrativas', 'usuario_administrativos', 'usuario_de_usos', 'forma_adquisicions', 'estado_del_usos', 'condicions', 'colors']);

        // Aplicar filtros
        if (!empty($this->filters['filterType'])) {
            switch ($this->filters['filterType']) {
                case 'fecha':
                    if (!empty($this->filters['fecha_desde']) && !empty($this->filters['fecha_hasta'])) {
                        $query->whereBetween('fecha_adquisicion', [$this->filters['fecha_desde'], $this->filters['fecha_hasta']]);
                    } elseif (!empty($this->filters['fecha_desde'])) {
                        $query->where('fecha_adquisicion', '>=', $this->filters['fecha_desde']);
                    } elseif (!empty($this->filters['fecha_hasta'])) {
                        $query->where('fecha_adquisicion', '<=', $this->filters['fecha_hasta']);
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
                case 'estado_del_uso_id':
                    if (!empty($this->filters['secondFilter'])) {
                        $query->where('estado_del_uso_id', $this->filters['secondFilter']);
                    }
                    break;
                case 'condicion_id':
                    if (!empty($this->filters['secondFilter'])) {
                        $query->where('condicion_id', $this->filters['secondFilter']);
                    }
                    break;
                case 'unidad_administrativa_id':
                    if (!empty($this->filters['secondFilter'])) {
                        $query->where('unidad_administrativa_id', $this->filters['secondFilter']);
                    }
                    break;
                case 'forma_adquisicion_id':
                    if (!empty($this->filters['secondFilter'])) {
                        $query->where('forma_adquisicion_id', $this->filters['secondFilter']);
                    }
                    break;
                case 'descripcion':
                    if (!empty($this->filters['secondFilter'])) {
                        $query->where('descripcion', 'like', '%' . $this->filters['secondFilter'] . '%');
                    }
                    break;
                case 'busqueda':
                    if (!empty($this->filters['busqueda'])) {
                        $search = $this->filters['busqueda'];
                        $query->where(function($q) use ($search) {
                            $q->where('descripcion', 'like', '%' . $search . '%')
                              ->orWhere('n_factura', 'like', '%' . $search . '%')
                              ->orWhere('n_interno_bien', 'like', '%' . $search . '%')
                              ->orWhere('marca', 'like', '%' . $search . '%')
                              ->orWhere('modelo', 'like', '%' . $search . '%')
                              ->orWhere('serial', 'like', '%' . $search . '%')
                              ->orWhere('sede', 'like', '%' . $search . '%');
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
            'ID',
            'Sede',
            'Unidad Administrativa',
            'Usuario Administrativo',
            'Usuario de Uso',
            'N° Interno del Bien',
            'Descripción',
            'Forma de Adquisición',
            'Fecha de Adquisición',
            'N° Factura',
            'Valor Factura',
            'Moneda',
            'Estado del Uso',
            'Condición Física',
            'Marca',
            'Modelo',
            'Color',
            'Serial',
            'Comentarios',
            'Fecha de Comentario',
        ];
    }

    public function map($biene): array
    {
        return [
            $biene->id,
            $biene->sede,
            $biene->unidad_administrativas ? $biene->unidad_administrativas->name : '',
            $biene->usuario_administrativos ? $biene->usuario_administrativos->usuario_administrativo : '',
            $biene->usuario_de_usos ? $biene->usuario_de_usos->usuario_de_uso : '',
            $biene->n_interno_bien,
            $biene->descripcion,
            $biene->forma_adquisicions ? $biene->forma_adquisicions->forma_adquisicion : '',
            $biene->fecha_adquisicion ? Carbon::parse($biene->fecha_adquisicion)->format('d/m/Y') : '',
            $biene->n_factura,
            $biene->valor_factura,
            $biene->moneda,
            $biene->estado_del_usos ? $biene->estado_del_usos->estado_del_uso : '',
            $biene->condicions ? $biene->condicions->condicion : '',
            $biene->marca,
            $biene->modelo,
            $biene->colors ? $biene->colors->color : '',
            $biene->serial,
            $biene->comentario,
            $biene->fecha_comentario ? Carbon::parse($biene->fecha_comentario)->format('d/m/Y') : '',
        ];
    }

    public function startCell(): string
    {
        return 'A2';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            2 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Bienes Públicos';
    }
}




