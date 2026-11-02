<?php

namespace App\Exports;

use App\Models\LoginActivity;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ActivityLogExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $activities;

    public function __construct($activities)
    {
        $this->activities = $activities;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->activities;
    }

    /**
     * @param LoginActivity $activity
     */
    public function map($activity): array
    {
        return [
            $activity->id,
            $activity->user ? $activity->user->name : 'N/A',
            $activity->user ? $activity->user->email : 'N/A',
            $activity->ip_address,
            $activity->device ?? 'Desconocido',
            $activity->location ?? 'N/A',
            $activity->status == 'success' ? 'Exitoso' : ($activity->status == 'failed' ? 'Fallido' : 'Bloqueado'),
            $activity->failure_reason ?? '-',
            $activity->created_at->format('d/m/Y H:i:s'),
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Usuario',
            'Email',
            'Dirección IP',
            'Dispositivo',
            'Ubicación',
            'Estado',
            'Razón de Fallo',
            'Fecha y Hora',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}

