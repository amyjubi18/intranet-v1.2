<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Distribución de Almacenamiento de Impresoras</title>
    <style>
        @page { size: landscape; }
        body {
            font-family: Arial, sans-serif;
            margin: 10px;
            color: #333;
            font-size: 10px;
            line-height: 1.3;
        }
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
            font-size: 14px;
        }
        .header p {
            margin: 5px 0;
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
            font-size: 10px;
        }
        .stat-box h3 {
            margin: 0;
            color: #007bff;
            font-size: 12px;
        }
        .stat-box p {
            margin: 5px 0 0 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Distribución de Almacenamiento de Impresoras</h1>
        <p>Generado el {{ date('d/m/Y H:i') }}</p>
    </div>

    <div class="stats flex">
        <div class="stat-box">
            <h3>{{ $distribuciones->count() }}</h3>
            <p>Total Distribuciones</p>
        </div>
        <div class="stat-box">
            <h3>{{ $distribuciones->sum('unidades_asignadas') }}</h3>
            <p>Unidades Asignadas</p>
        </div>
        <div class="stat-box">
            <h3>{{ $distribuciones->unique('gerencia_id')->count() }}</h3>
            <p>Gerencias con Distribuciones</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ITEM</th>
                <th>Consumible</th>
                <th>Tipo de Consumible</th>
                <th>Impresora</th>
                <th>Tipo de Impresora</th>
                <th>Gerencia</th>
                <th>Área</th>
                <th>Stock Min</th>
                <th>Unidades</th>
                <th>Unidades Asignadas</th>
                <th>Fecha de Asignacion</th>

            </tr>
        </thead>
        <tbody>
            @foreach($distribuciones as $distribucion)
            <tr>
                <td>{{ str_pad($distribucion->impresoras->id, 4, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $distribucion->almacenamientoImpresora->cartuchos->cartucho ?? '' }}</td>
                <td>{{ $distribucion->almacenamientoImpresora->colores ? $distribucion->almacenamientoImpresora->colores->colore : '' }}</td>
                <td>{{ $distribucion->almacenamientoImpresora->impresoras ? $distribucion->almacenamientoImpresora->impresoras->impresora : '' }}</td>
                <td>{{ $distribucion->almacenamientoImpresora->fabricantes ? $distribucion->almacenamientoImpresora->fabricantes->fabricante : '' }}</td>
                <td>{{ $distribucion->gerencia->gerencia ?? '' }}</td>
                <td>{{ $distribucion->almacenamientoImpresora->cartuchos->stock_min ?? '' }}</td>
                <td>{{ $distribucion->gerencia->area->area ?? '' }}</td>
                <td>{{ $distribucion->unidades_asignadas_originales }}</td>
                <td>{{ $distribucion->unidades_asignadas }}</td>
                <td>{{ $distribucion->created_at }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Reporte generado por el Sistema de Inventario - {{ date('Y') }}</p>
    </div>
</body>
</html>
