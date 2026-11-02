<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Salida de Almacenamiento de Impresoras</title>
    <style>
        @page { size: landscape; }
        body {
            font-family: Arial, sans-serif;
            margin: 10px;
            color: #333;
            font-size: 10px;
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
        colgroup col:nth-child(1) {
            width: 5%;
        }
        colgroup col:nth-child(2) {
            width: 12%;
        }
        colgroup col:nth-child(3) {
            width: 12%;
        }
        colgroup col:nth-child(4) {
            width: 15%;
        }
        colgroup col:nth-child(5) {
            width: 8%;
        }
        colgroup col:nth-child(6) {
            width: 10%;
        }
        colgroup col:nth-child(7) {
            width: 12%;
        }
        colgroup col:nth-child(8) {
            width: 10%;
        }
        colgroup col:nth-child(9) {
            width: 10%;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 4px 6px;
            text-align: left;
            vertical-align: top;
            overflow-wrap: break-word;
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
        <h1>Reporte de Salida de Almacenamiento de Impresoras</h1>
        <p>Generado el {{ date('d/m/Y H:i') }}</p>
    </div>

    <div class="stats">
        <div class="stat-box">
            <h3>{{ $salida_almacenamiento_impresoras->count() }}</h3>
            <p>Total Salidas</p>
        </div>
        <div class="stat-box">
            <h3>{{ $salida_almacenamiento_impresoras->sum('cantidad') }}</h3>
            <p>Total Unidades Salidas</p>
        </div>
        <div class="stat-box">
            <h3>{{ $salida_almacenamiento_impresoras->unique('distribucion_almacenamiento_impresora_id')->count() }}</h3>
            <p>Distribuciones con Salidas</p>
        </div>
    </div>

    <table>
        <colgroup>
            <col />
            <col />
            <col />
            <col />
            <col />
            <col />
            <col />
            <col />
            <col />
            <col />
            <col />
            <col />
        </colgroup>
        <thead>
            <tr>
                <th>ITEM</th>
                <th>Fecha Salida</th>
                <th>Fecha Ingreso</th>
                <th>Consumible</th>
                <th>Impresora</th>
                <th>Tipo de Consumible</th>
                <th>Tipo de Impresora</th>
                <th>Gerencia</th>
                <th>Área</th>
                <th>Stock Min</th>
                <th>Cantidad</th>
                <th>Unidades Disponibles</th>
                <th>Unidades Asignadas Originales</th>
                <th>Unidades Asignadas Disponibles</th>
                <th>Ajuste Inventario</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salida_almacenamiento_impresoras as $item)
                <tr>
                    <td>{{  str_pad($item->almacenamientoImpresora ? $item->almacenamientoImpresora->impresoras->id : 'N/A' , 4, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $item->fecha_salida }}</td>
                    <td>{{ $item->almacenamientoImpresora ? $item->almacenamientoImpresora->fecha_ingreso : 'N/A' }}</td>
                    <td>{{ $item->almacenamientoImpresora ? $item->almacenamientoImpresora->cartuchos->cartucho : 'N/A' }}</td>

                    <td>{{ $item->almacenamientoImpresora ? $item->almacenamientoImpresora->impresoras->impresora : 'N/A' }}</td>
                    <td>{{ $item->almacenamientoImpresora ? $item->almacenamientoImpresora->colores->colore : 'N/A' }}</td>
                    <td>{{ $item->almacenamientoImpresora ? $item->almacenamientoImpresora->fabricantes->fabricante : 'N/A' }}</td>


                    <td>{{ $item->distribucionAlmacenamientoImpresora ? $item->distribucionAlmacenamientoImpresora->gerencia->gerencia : 'N/A' }}</td>
                    <td>{{ $item->distribucionAlmacenamientoImpresora && $item->distribucionAlmacenamientoImpresora->area ? $item->distribucionAlmacenamientoImpresora->area->area : 'N/A' }}</td>
                    <td>{{ $item->almacenamientoImpresora ? $item->almacenamientoImpresora->cartuchos->stock_min : 'N/A' }}</td>
                    <td>{{ $item->cantidad }}</td>
                    <td>{{ $item->historico_unidades_disponibles }}</td>

                   <td>{{ $item->historico_unidades_asignadas_originales }}</td>
                <td>{{ $item->historico_unidades_asignadas_restantes }}</td> <td>{{ $item->ajuste_inventario }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Reporte generado por el Sistema de Inventario - {{ date('Y') }}</p>
    </div>
</body>
</html>
