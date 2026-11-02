<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Salida de Consumibles de Impresoras</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #007bff;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .stats {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-around;
        }
        .stat-box {
            background-color: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            flex: 1;
            margin: 0 5px;
        }
        .stat-box h3 {
            margin: 0;
            color: #007bff;
        }
        .stat-box p {
            margin: 5px 0 0 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Salida de Consumibles de Impresoras</h1>
        <p>Generado el {{ date('d/m/Y H:i') }}</p>
    </div>

    <div class="stats">
        <div class="stat-box">
            <h3>{{ $salida_impresoras->count() }}</h3>
            <p>Total Salidas</p>
        </div>
        <div class="stat-box">
            <h3>{{ $salida_impresoras->sum('cantidad') }}</h3>
            <p>Total Unidades Salidas</p>
        </div>
        <div class="stat-box">
            <h3>{{ $salida_impresoras->unique('gerencia_id')->count() }}</h3>
            <p>Gerencias con Salidas</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha de Salida</th>
                <th>Fecha de Ingreso</th>
                <th>Cantidad Retirada</th>
                <th>Unidades Disponibles</th>
                <th>Consumible</th>
                <th>Color</th>
                <th>Impresora</th>
                <th>Tipo Consumible</th>
                <th>Área</th>
                <th>Gerencia</th>
                <th>Ajuste Inventario</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salida_impresoras as $item)
            <tr>
                <td>{{ $item->id }}</td>
                <td>{{ \Carbon\Carbon::parse($item->fecha_salida)->format('d/m/Y') }}</td>
                <td>{{ $item->inventario_impresoras ? \Carbon\Carbon::parse($item->inventario_impresoras->fecha_ingreso)->format('d/m/Y') : 'N/A' }}</td>
                <td>{{ $item->cantidad }}</td>
                <td>{{ $item->historico_unidades_disponibles }}</td>
                <td>{{ $item->cartuchos ? $item->cartuchos->cartucho : 'N/A' }}</td>
                <td>{{ $item->colores ? $item->colores->colore : 'N/A' }}</td>
                <td>{{ $item->impresoras ? $item->impresoras->impresora : 'N/A' }}</td>
                <td>{{ $item->fabricantes ? $item->fabricantes->fabricante : 'N/A' }}</td>
                <td>{{ $item->areas ? $item->areas->area : 'N/A' }}</td>
                <td>{{ $item->gerencias ? $item->gerencias->gerencia : 'N/A' }}</td>
                <td>{{ $item->ajuste_inventario ? 'Sí' : 'No' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Reporte generado por el Sistema de Inventario - {{ date('Y') }}</p>
    </div>
</body>
</html>
