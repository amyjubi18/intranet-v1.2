<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Stock Crítico - PDF</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #dc2626;
        }
        .header p {
            margin: 5px 0;
            font-size: 12px;
        }
        .stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            background: #fef2f2;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #fecaca;
        }
        .stat-item {
            text-align: center;
            flex: 1;
        }
        .stat-value {
            font-size: 14px;
            font-weight: bold;
            color: #dc2626;
        }
        .stat-label {
            font-size: 10px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 9px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
            font-size: 10px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .critical {
            background-color: #fef2f2 !important;
            border-left: 4px solid #dc2626;
        }
        .warning {
            background-color: #fffbeb !important;
            border-left: 4px solid #f59e0b;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .legend {
            margin-bottom: 15px;
            font-size: 9px;
            text-align: center;
        }
        .legend-item {
            display: inline-block;
            margin: 0 10px;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 8px;
        }
        .legend-critical {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
        }
        .legend-warning {
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #f59e0b;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Stock Crítico</h1>
        <p>Sistema de Inventario Unificado - CODECYT</p>
        <p>Fecha de generación: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="legend">
        <span class="legend-item legend-critical">Crítico: Stock < 3 unidades</span>
        <span class="legend-item legend-warning">Advertencia: Stock 3-4 unidades</span>
    </div>

    <div class="stats">
        <div class="stat-item">
            <div class="stat-value">{{ $lowStockItems->count() }}</div>
            <div class="stat-label">Items con Stock Bajo</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $lowStockItems->where('color', 'red')->count() }}</div>
            <div class="stat-label">Items Críticos</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $lowStockItems->where('color', 'yellow')->count() }}</div>
            <div class="stat-label">Items en Advertencia</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 25%;">Producto</th>
                <th style="width: 10%;">Stock</th>
                <th style="width: 15%;">Tipo</th>
                <th style="width: 20%;">Categoría</th>
                <th style="width: 10%;">Fecha Ingreso</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lowStockItems as $index => $item)
            <tr class="{{ $item['color'] === 'red' ? 'critical' : 'warning' }}">
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $item['nombre'] }}</td>
                <td style="text-align: center; font-weight: bold;">{{ $item['stock'] }}</td>
                <td>{{ $item['tipo'] }}</td>
                <td>{{ $item['categoria'] }}</td>
                <td style="text-align: center;">
                    @if($item['fecha_ingreso'])
                        {{ \Carbon\Carbon::parse($item['fecha_ingreso'])->format('d/m/Y') }}
                    @else
                        N/A
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Reporte generado por el Sistema de Inventario Unificado</p>
        <p>Items con stock crítico requieren atención inmediata para evitar interrupciones en las operaciones</p>
    </div>
</body>
</html>
