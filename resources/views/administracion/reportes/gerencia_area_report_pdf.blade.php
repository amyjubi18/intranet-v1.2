<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte por Gerencia y Area</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            font-size: 14px;
            color: #333;
        }
        h1, h2 {
            text-align: center;
            color: #004080;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-bottom: 40px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #e6f0ff;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #666;
            position: fixed;
            bottom: 20px;
            width: 100%;
        }
    </style>
</head>
<body>
    <h1>Reporte por Gerencia y Área</h1>
    <h2>
        @if($gerencia)
            Gerencia: {{ $gerencia->gerencia }}
        @else
            Gerencia: Todas las Gerencias
        @endif
        <br>
        @if($area)
            Área: {{ $area->area }}
        @else
            Área: Todas las Áreas
        @endif
    </h2>

    <table>
        <thead>
            <tr>
                <th>Tipo de Salida</th>
                <th>Cantidad Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Salida Oficina</td>
                <td>{{ $salidaOficinaCount }}</td>
            </tr>
            <tr>
                <td>Salida Almacenamiento Impresora</td>
                <td>{{ $salidaAlmacenCount }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Reporte generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
