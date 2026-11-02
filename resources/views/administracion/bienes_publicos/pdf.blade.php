<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Bienes Públicos</title>
    <style>
        @page { size: landscape; margin: 5px; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 5px;
            color: #000000;
            font-size: 8px;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 2px solid #000080;
        }
        .header h1 {
            color: #000080;
            margin: 0;
            font-size: 14px;
            font-weight: bold;
        }
        .header p {
            margin: 3px 0;
            color: #444;
            font-size: 9px;
        }
        .stats {
            margin-bottom: 10px;
            width: 100%;
        }
        .stats table {
            width: 100%;
            border-collapse: collapse;
        }
        .stats td {
            border: 1px solid #000080;
            padding: 4px;
            text-align: center;
            background-color: #E6F0FF;
        }
        .stats h3 {
            margin: 0;
            color: #000080;
            font-size: 12px;
        }
        .stats p {
            margin: 2px 0 0 0;
            font-size: 8px;
            color: #444;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        th, td {
            border: 1px solid #666;
            padding: 2px 3px;
            text-align: left;
            vertical-align: top;
            font-size: 7px;
        }
        th {
            background-color: #000080;
            color: #FFFFFF;
            font-weight: bold;
            font-size: 8px;
        }
        tr:nth-child(even) {
            background-color: #F5F5F5;
        }
        .footer {
            margin-top: 10px;
            text-align: center;
            font-size: 7px;
            color: #666;
            border-top: 1px solid #999;
            padding-top: 3px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Bienes Públicos</h1>
        <p>Generado el {{ date('d/m/Y H:i') }}</p>
    </div>

    <div class="stats">
        <table>
            <tr>
                <td style="width: 25%;">
                    <h3>{{ $totalBienes }}</h3>
                    <p>Total Bienes Registrados</p>
                </td>
                <td style="width: 25%;">
                    <h3>{{ number_format((float)$valorTotal, 2) }}</h3>
                    <p>Valor Total (Bs.)</p>
                </td>
                <td style="width: 25%;">
                    <h3>{{ $statsEnTransito }}</h3>
                    <p>En Uso (Estado 6)</p>
                </td>
                
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">ID</th>
                <th style="width: 8%;">Sede</th>
                <th style="width: 10%;">Unidad Admin.</th>
                <th style="width: 10%;">Usuario Admin.</th>
                <th style="width: 10%;">Usuario de Uso</th>
                <th style="width: 8%;">N° Interno</th>
                <th style="width: 12%;">Descripción</th>
                <th style="width: 8%;">Forma Adq.</th>
                <th style="width: 7%;">Fecha Adq.</th>
                <th style="width: 7%;">N° Fact.</th>
                <th style="width: 7%;">Valor</th>
                <th style="width: 5%;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bienes as $item)
            <tr>
                <td class="text-center">{{ $item->id }}</td>
                <td>{{ $item->sede }}</td>
                <td>{{ $item->unidad_administrativas ? $item->unidad_administrativas->name : 'N/A' }}</td>
                <td>{{ $item->usuario_administrativos ? $item->usuario_administrativos->usuario_administrativo : 'N/A' }}</td>
                <td>{{ $item->usuario_de_usos ? $item->usuario_de_usos->usuario_de_uso : 'N/A' }}</td>
                <td>{{ $item->n_interno_bien }}</td>
                <td>{{ Str::limit($item->descripcion, 50) }}</td>
                <td>{{ $item->forma_adquisicions ? $item->forma_adquisicions->forma_adquisicion : 'N/A' }}</td>
                <td>{{ $item->fecha_adquisicion ? \Carbon\Carbon::parse($item->fecha_adquisicion)->format('d/m/Y') : 'N/A' }}</td>
                <td>{{ $item->n_factura }}</td>
                <td>{{ number_format((float)$item->valor_factura, 2) }}</td>
                <td>{{ $item->estado_del_usos ? $item->estado_del_usos->estado_del_uso : 'N/A' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="12" class="text-center">No hay registros para mostrar</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Reporte generado por el Sistema de Inventario - {{ date('Y') }}</p>
    </div>
</body>
</html>
