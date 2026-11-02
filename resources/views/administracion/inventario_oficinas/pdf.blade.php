<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Inventario de Oficinas</title>
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
            max-width: 90px;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Inventario de Oficinas</h1>
        <p>Generado el {{ date('d/m/Y H:i') }}</p>
    </div>

    <div class="stats flex">
        <div class="stat-box">
            <h3>{{ $totalItems }}</h3>
            <p>Total Items</p>
        </div>
        <div class="stat-box">
            <h3>{{ $totalUnidades }}</h3>
            <p>Total Unidades</p>
        </div>
        <div class="stat-box">
            <h3>{{ $totalUnidadesDisponibles }}</h3>
            <p>Unidades Disponibles</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha de Factura</th>
                <th>Articulo</th>
                <th>Unidad de Medida</th>
                <th>Proveedor</th>
                <th>Unidades</th>
                <th>Unidades Disponibles</th>
                <th> Nº Factura</th>
                <th>Fecha de Ingreso</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inventario_oficinas as $item)
            <tr>
                <td>{{ str_pad($item->id, 4, '0', STR_PAD_LEFT) }}</td>
                <td>{{ \Carbon\Carbon::parse($item->fecha_ingreso)->format('d/m/Y') }}</td>
                <td>{{ $item->articulos ? $item->articulos->articulo : 'N/A' }}</td>
                <td>{{ $item->unidad_medidas ? $item->unidad_medidas->unidad_medida : 'N/A' }}</td>
                <td>{{ $item->proveedores ? $item->proveedores->proveedor : 'N/A' }}</td>
                <td>{{ $item->unidades }}</td>
                <td>{{ $item->unidades_disponibles }}</td>
                <td>{{ $item->n_factura }}</td>
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
