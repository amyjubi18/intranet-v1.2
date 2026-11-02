<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análisis de Consumo - PDF</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
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
        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .section h2 {
            color: #2563eb;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
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
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .chart-placeholder {
            border: 2px dashed #ccc;
            padding: 20px;
            text-align: center;
            background-color: #f9f9fa;
            margin: 20px 0;
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .chart-placeholder p {
            margin: 0;
            font-size: 12px;
            color: #666;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        @media print {
            body { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Análisis de Consumo de Inventario</h1>
        <p>Reporte generado el {{ date('d/m/Y H:i') }}</p>
        <p>Consumibles y Materiales Utilizados</p>
    </div>

    <div class="section">
        <h2>Consumibles de Impresora Utilizados</h2>
        <p>Ordenados de mayor a menor consumo basado en salidas del almacén</p>

        <table>
            <thead>
                <tr>
                    <th>Posición</th>
                    <th>Consumible</th>
                    <th>Total Salidas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($consumibles as $index => $item)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td style="text-align: center;">{{ $item['value'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Materiales de Oficina Consumidos</h2>
        <p>Ordenados de mayor a menor consumo basado en salidas de oficina</p>

        <table>
            <thead>
                <tr>
                    <th>Posición</th>
                    <th>Material</th>
                    <th>Total Salidas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($materiales as $index => $item)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td style="text-align: center;">{{ $item['value'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Reporte generado por el Sistema de Inventario Unificado</p>
        <p>Los gráficos interactivos están disponibles en la vista web del sistema</p>
    </div>
</body>
</html>
