<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Proveedores - PDF</title>
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
            color: #2563eb;
        }
        .header p {
            margin: 5px 0;
            font-size: 12px;
        }
        .stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
        }
        .stat-item {
            text-align: center;
            flex: 1;
        }
        .stat-value {
            font-size: 14px;
            font-weight: bold;
            color: #2563eb;
        }
        .stat-label {
            font-size: 10px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Proveedores</h1>
        <p>Sistema de Inventario Unificado - CODECYT</p>
        <p>Fecha de generación: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="stats">
        <div class="stat-item">
            <div class="stat-value">{{ $proveedores->count() }}</div>
            <div class="stat-label">Total Proveedores</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%;">#</th>
                <th style="width: 90%;">Nombre del Proveedor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($proveedores as $index => $proveedor)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $proveedor->proveedor }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Reporte generado por el Sistema de Inventario Unificado</p>
        <p>Total de proveedores: {{ $proveedores->count() }}</p>
    </div>
</body>
</html>
