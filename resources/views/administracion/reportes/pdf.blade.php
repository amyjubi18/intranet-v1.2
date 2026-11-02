<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario Unificado - PDF</title>
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
            font-size: 8px;
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
        .tipo-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .tipo-impresora {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .tipo-oficina {
            background-color: #dcfce7;
            color: #166534;
        }
        .stock-bajo {
            background-color: #fef2f2 !important;
            color: #dc2626;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .page-break {
            page-break-before: always;
        }
        @media print {
            body { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Inventario Unificado</h1>
        <p>Reporte generado el {{ date('d/m/Y H:i') }}</p>
        <p>Consumibles de Impresoras y Artículos de Oficina</p>
    </div>

    <div class="stats">
        <div class="stat-item">
            <div class="stat-value">{{ $inventarioUnificado->sum('unidades') }}</div>
            <div class="stat-label">Total Items</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $inventarioUnificado->sum('unidades_disponibles') }}</div>
            <div class="stat-label">Items Disponibles</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $inventarioUnificado->where('unidades_disponibles', '<', 5)->count() }}</div>
            <div class="stat-label">Stock Bajo</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $inventarioUnificado->count() }}</div>
            <div class="stat-label">Registros Totales</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tipo</th>
                <th>Fecha Ingreso</th>
                <th>Factura</th>
                <th>Unidades</th>
                <th>Disponibles</th>
                <th>Item</th>
                <th>Especificaciones</th>
                <th>Proveedor</th>

            </tr>
        </thead>
        <tbody>
            @foreach($inventarioUnificado as $item)
            <tr class="{{ $item->unidades_disponibles < 5 ? 'stock-bajo' : '' }}">
                <td style="text-align: center;">{{ $item->id }}</td>
                <td style="text-align: center;">
                    <span class="tipo-badge {{ $item->tipo == 'impresora' ? 'tipo-impresora' : 'tipo-oficina' }}">
                        {{ $item->tipo == 'impresora' ? 'Impresora' : 'Oficina' }}
                    </span>
                </td>
                <td>{{ \Carbon\Carbon::parse($item->fecha_ingreso)->format('d/m/Y') }}</td>
                <td>{{ $item->n_factura }}</td>
                <td style="text-align: center;">{{ $item->unidades }}</td>
                <td style="text-align: center;">{{ $item->unidades_disponibles }}</td>
                <td>
                    @if($item->tipo == 'impresora')
                        {{ $item->cartuchos ? $item->cartuchos->cartucho : 'N/A' }}
                    @else
                        {{ $item->articulos ? $item->articulos->articulo : 'N/A' }}
                    @endif
                </td>
                <td>
                    @if($item->tipo == 'impresora')
                        @if($item->colores)
                            <strong>Color:</strong> {{ $item->colores->colore }}<br>
                        @endif
                        @if($item->impresoras)
                            <strong>Impresora:</strong> {{ $item->impresoras->impresora }}<br>
                        @endif
                        @if($item->fabricantes)
                            <strong>Tipo:</strong> {{ $item->fabricantes->fabricante }}
                        @endif
                    @else
                        @if($item->unidad_medidas)
                            <strong>Unidad:</strong> {{ $item->unidad_medidas->unidad_medida }}<br>
                        @endif
                        @if($item->descripcion)
                            <strong>Descripción:</strong> {{ $item->descripcion }}
                        @endif
                    @endif
                </td>
                <td>{{ $item->proveedores ? $item->proveedores->proveedor : 'N/A' }}</td>
                
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Reporte generado por el Sistema de Inventario Unificado</p>
        <p>Total de registros: {{ $inventarioUnificado->count() }}</p>
    </div>
</body>
</html>
