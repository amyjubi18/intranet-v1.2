<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Actividades - PDF</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { text-align: center; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .action-created { background-color: #d4edda; }
        .action-updated { background-color: #cce5ff; }
        .action-deleted { background-color: #f8d7da; }
        .action-exported { background-color: #e2e3e5; }
    </style>
</head>
<body>
    <h1>Registro de Actividades</h1>
    <p><strong>Fecha de generación:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
    <p><strong>Total de registros:</strong> {{ $activities->count() }}</p>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Usuario</th>
                <th>Acción</th>
                <th>Modelo</th>
                <th>Descripción</th>
            </tr>
        </thead>
        <tbody>
            @foreach($activities as $activity)
            <tr>
                <td>{{ $activity->id }}</td>
                <td>{{ $activity->created_at->format('d/m/Y h:i:s A') }}</td>
                <td>{{ $activity->causer ? $activity->causer->username : 'Sistema' }}</td>
                <td class="action-{{ $activity->event }}">
                    @if($activity->event == 'created') Registro
                    @elseif($activity->event == 'updated') Actualización
                    @elseif($activity->event == 'deleted') Eliminación
                    @elseif($activity->event == 'exported') Exportación
                    @else {{ ucfirst($activity->event) }}
                    @endif
                </td>
                <td>{{ class_basename($activity->subject_type ?? 'N/A') }}</td>
                <td>
                    @if($activity->description)
                        {{ Str::limit($activity->description, 50) }}
                    @else
                        {{ $activity->properties['url'] ?? request()->getPathInfo() }}
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
