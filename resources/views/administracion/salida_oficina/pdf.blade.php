<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Salida de Oficina</title>
    <style>
        @page { size: landscape; }
        body {
            font-family: Arial, sans-serif;
            margin: 10px;
            color: #333;
            font-size: 10px;
            line-height: 1.3;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 8px;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 14px;
        }
        .header p {
            margin: 4px 0;
            color: #666;
            font-size: 10px;
        }
        .stats {
            margin-bottom: 15px;
            display: flex;
            justify-content: space-around;
        }
        .stat-box {
            background-color: #e9ecef;
            padding: 6px;
            border-radius: 5px;
            text-align: center;
            flex: 1;
            margin: 0 4px;
            font-size: 10px;
        }
        .stat-box h3 {
            margin: 0;
            color: #007bff;
            font-size: 12px;
        }
        .stat-box p {
            margin: 3px 0 0 0;
            font-size: 9px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed;
            word-wrap: break-word;
            word-break: break-word;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 4px 6px;
            text-align: left;
            vertical-align: top;
            overflow-wrap: break-word;
            max-width: 80px;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #007bff;
            font-size: 11px;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #666;
        }
        .filters-info {
            margin-bottom: 15px;
            padding: 8px;
            background-color: #e3f2fd;
            border-radius: 5px;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Salida de Oficina</h1>
        <p>Generado el {{ date('d/m/Y H:i') }}</p>
    </div>

    @if(isset($filtersApplied) && $filtersApplied)
    <div class="filters-info">
        <strong>Filtros aplicados:</strong>
        @if(isset($filterDescription))
            {{ $filterDescription }}
        @else
            Sí
        @endif
    </div>
    @endif

    <div class="stats flex">
        <div class="stat-box">
            <h3>{{ $totalRegistros }}</h3>
            <p>Total Registros</p>
        </div>
        <div class="stat-box">
            <h3>{{ $totalUnidades }}</h3>
            <p>Total Unidades Retiradas</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ITEM</th>
                <th>Fecha de Salida</th>
                <th>Artículo</th>
                <th>Unidad de Medida</th>
                <th>Gerencia</th>
                <th>Área</th>
                <th>Solicitante</th>
                <th>Cantidad</th>
                <th>Unidades Disponibles</th>
                <th>Histórico de Unidades</th>
                <th>Ajuste Inv.</th>
                <th>Fecha Reg.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salida_oficinas as $item)
            @php
                $stockHistorico = $item->stockHistorico ?? $item->historico_unidades_disponibles ?? 0;
                $stockRestante = max(0, $stockHistorico - $item->cantidad);
            @endphp
            <tr>
                <td>{{ str_pad($item->articulos->id, 4, '0', STR_PAD_LEFT) }}</td>
                <td>{{ \Carbon\Carbon::parse($item->fecha_salida)->format('d/m/Y') }}</td>
                <td>{{ $item->articulos ? $item->articulos->articulo : 'N/A' }}</td>
                <td>{{ $item->unidad_medidas ? $item->unidad_medidas->unidad_medida : 'N/A' }}</td>
                <td>{{ $item->gerencias ? $item->gerencias->gerencia : 'N/A' }}</td>
                <td>{{ $item->areas ? $item->areas->area : 'N/A' }}</td>
                <td>{{ $item->users ? $item->users->username : 'N/A' }}</td>
                <td>{{ $item->cantidad }}</td>
                <td>{{ $stockRestante }}</td>
                <td>{{ $stockHistorico }}</td>
                <td>{{ $item->ajuste_inventario ?? 'N/A' }}</td>
                <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Reporte generado por el Sistema de Inventario - {{ date('Y') }}</p>
    </div>
</body>
</html>

