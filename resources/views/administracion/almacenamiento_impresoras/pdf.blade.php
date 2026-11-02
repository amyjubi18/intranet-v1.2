<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Almacenamiento de Consumibles de Impresoras</title>
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
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Almacenamiento de Consumibles de Impresoras</h1>
        <p>Generado el {{ date('d/m/Y H:i') }}</p>
    </div>

    <div class="stats flex">
        <div class="stat-box">
            <h3>{{ $almacenamiento_impresoras->count() }}</h3>
            <p>Total Items</p>
        </div>
        <div class="stat-box">
            <h3>{{ $almacenamiento_impresoras->sum('unidades') }}</h3>
            <p>Total Unidades</p>
        </div>
        <div class="stat-box">
            <h3>{{ $almacenamiento_impresoras->sum('unidades_disponibles') }}</h3>
            <p>Unidades Disponibles</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Fecha de Factura</th>
                <th>Fecha de Ingreso</th>
                <th>Consumible</th>
                <th>Tipo de Consumible</th>
                <th>Impresora</th>
                <th>Tipo de Impresora</th>
                <th>Proveedor</th>
                <th>Stock Min</th>
                <th>Unidades</th>
                <th>Unidades Disponibles</th>
                <th>Total Disp. (Impresora)</th>
                <th>Factura</th>
            </tr>
        </thead>
        <tbody>
            @php
                // Calcular totales por impresora, color y fecha
                $totalesPorImpresoraColor = [];
                foreach($almacenamiento_impresoras as $item) {
                    $impresoraId = $item->impresora_id;
                    $colorId = $item->colore_id;
                    $fechaIngreso = $item->fecha_ingreso;
                    $key = $impresoraId . '_' . $colorId . '_' . $fechaIngreso;
                    if (!isset($totalesPorImpresoraColor[$key])) {
                        $totalesPorImpresoraColor[$key] = $almacenamiento_impresoras->where('impresora_id', $impresoraId)->where('colore_id', $colorId)->where('fecha_ingreso', $fechaIngreso)->sum('unidades_disponibles');
                    }
                }
            @endphp
            @foreach($almacenamiento_impresoras as $item)
            @php
                $key = $item->impresora_id . '_' . $item->colore_id . '_' . $item->fecha_ingreso;
                $totalDisp = $totalesPorImpresoraColor[$key] ?? $item->unidades_disponibles;
            @endphp
            <tr>
                <td>{{ str_pad($item->impresoras->id, 4, '0', STR_PAD_LEFT) }}</td>
                <td>{{ \Carbon\Carbon::parse($item->fecha_ingreso)->format('d/m/Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}</td>
                <td>{{ $item->cartuchos ? $item->cartuchos->cartucho : 'N/A' }}</td>
                <td>{{ $item->colores ? $item->colores->colore : 'N/A' }}</td>
                <td>{{ $item->impresoras ? $item->impresoras->impresora : 'N/A' }}</td>
                <td>{{ $item->fabricantes ? $item->fabricantes->fabricante : 'N/A' }}</td>
                <td>{{ $item->proveedores ? $item->proveedores->proveedor : 'N/A' }}</td>
                <td>{{ $item->cartuchos ? $item->cartuchos->stock_min : 'N/A' }}</td>
                <td>{{ $item->unidades }}</td>
                <td>{{ $item->unidades_disponibles }}</td>
                <td>{{ $totalDisp }}</td>
                <td>{{ $item->n_factura }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Reporte generado por el Sistema de Inventario - {{ date('Y') }}</p>
    </div>
</body>
</html>
