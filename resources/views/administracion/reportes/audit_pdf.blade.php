<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoría de Inventario - PDF</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8px;
            line-height: 1.3;
            color: #333;
            margin: 0;
            padding: 15px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 8px;
        }
        .header h1 {
            margin: 0;
            font-size: 16px;
            color: #2563eb;
        }
        .header p {
            margin: 3px 0;
            font-size: 10px;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #2563eb;
            margin: 15px 0 8px 0;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 6px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
            font-size: 7px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .tipo-badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 5px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .tipo-inventario-oficina { background-color: #dbeafe; color: #1e40af; }
        .tipo-salida-oficina { background-color: #fee2e2; color: #dc2626; }
        .tipo-almacenamiento-impresoras { background-color: #dcfce7; color: #166534; }
        .tipo-distribucion-almacen { background-color: #fef3c7; color: #92400e; }
        .tipo-salida-almacen { background-color: #f3e8ff; color: #7c3aed; }
        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 6px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
        .stats {
            justify-content: space-between;
            margin-bottom: 15px;
            background: #f8f9fa;
            padding: 8px;
            border-radius: 3px;
        }
        .stat-item {
            text-align: center;
            display: block
        }
        .stat-value {
            font-size: 12px;
            font-weight: bold;
            color: #2563eb;
        }
        .stat-label {
            font-size: 8px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Auditoría de Inventario</h1>
        <p>CODECYT</p>
        <p>Fecha de generación: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="stats">
        <div class="stat-item">
            <div class="stat-value">{{ $inventarioOficina->count() }}</div>
            <div class="stat-label">Inventario Oficina</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $salidaOficina->count() }}</div>
            <div class="stat-label">Salidas Oficina</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $almacenamientoImpresoras->count() }}</div>
            <div class="stat-label">Almacenamiento Impresora</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $distribucionAlmacen->count() }}</div>
            <div class="stat-label">Distribuciones de Almacenamiento Impresora</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $salidaAlmacen->count() }}</div>
            <div class="stat-label">Salidas Almacén</div>
        </div>
    </div>

    @if($inventarioOficina->count() > 0)
    <div class="section-title">Inventario de Oficina</div>
    <table>
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Artículo</th>
                <th>Unidad de Medida</th>
                <th>Unidades</th>
                <th>Unidades Disponibles</th>
                <th>Stock Min</th>
                <th>Factura</th>
                <th>Fecha Ingreso</th>
                <th>Proveedor</th>
                <th>Usuario</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inventarioOficina as $item)
            <tr>
                <td><span class="tipo-badge tipo-inventario-oficina">Inventario</span></td>
                <td>{{ $item->articulos ? $item->articulos->articulo : 'N/A' }}</td>
                <td>{{ $item->unidad_medidas ? $item->unidad_medidas->unidad_medida : 'N/A' }}</td>
                <td style="text-align: center;">{{ $item->unidades }}</td>
                <td style="text-align: center;">{{ $item->unidades_disponibles }}</td>
                <td style="text-align: center;">{{ $item->articulos ? $item->articulos->stock_min : 'N/A' }}</td>
                <td>{{ $item->n_factura }}</td>
                <td>{{ \Carbon\Carbon::parse($item->fecha_ingreso)->format('d/m/Y') }}</td>
                <td>{{ $item->proveedores ? $item->proveedores->proveedor : 'N/A' }}</td>

                <td>{{ $item->users ? $item->users->username : 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($salidaOficina->count() > 0)
    <div class="section-title">Salidas de Oficina</div>
    <table>
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Artículo</th>
                <th>Unidad de Medida</th>
                <th>Cantidad</th>
                <th>Stock Mínimo</th>
                <th>Unidades Ingresadas</th>
                <th>Unidades Disponibles</th>
                <th>Fecha Salida</th>
                <th>Proveedor</th>
                <th>Gerencia</th>
                <th>Área</th>
                 <th>Usuario</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salidaOficina as $item)
            <tr>
                <td><span class="tipo-badge tipo-salida-oficina">Salida</span></td>
                <td>{{ $item->inventario_oficinas && $item->inventario_oficinas->articulos ? $item->inventario_oficinas->articulos->articulo : 'N/A' }}</td>
                <td>{{ $item->inventario_oficinas && $item->inventario_oficinas->unidad_medidas ? $item->inventario_oficinas->unidad_medidas->unidad_medida : 'N/A' }}</td>
                <td style="text-align: center;">{{ $item->cantidad }}</td>
                <td style="text-align: center;">{{ $item->inventario_oficinas && $item->inventario_oficinas->articulos ? $item->inventario_oficinas->articulos->stock_min : 'N/A' }}</td>
                <td style="text-align: center;">{{ $item->historico_unidades_disponibles }}</td>
                <td style="text-align: center;">{{ $item->inventario_oficinas->unidades_disponibles }}</td>
                <td>{{ \Carbon\Carbon::parse($item->fecha_salida)->format('d/m/Y') }}</td>

                <td>{{ $item->inventario_oficinas && $item->inventario_oficinas->proveedores ? $item->inventario_oficinas->proveedores->proveedor : 'N/A' }}</td>
                <td>{{ $item->gerencias ? $item->gerencias->gerencia : 'N/A' }}</td>
                <td>{{ $item->areas ? $item->areas->area : 'N/A' }}</td>
                <td>{{ $item->users ? $item->users->username : 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($almacenamientoImpresoras->count() > 0)
    <div class="section-title">Almacenamiento de Impresoras</div>
    <table>
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Consumible</th>
                <th>Impresora</th>
                <th>Tipo de Consumible</th>
                <th>Color</th>
                <th>Unidades</th>
                <th>Unidades Disponibles</th>
                <th>Stock Min</th>
                <th>Factura</th>
                <th>Fecha Ingreso</th>
                <th>Proveedor</th>
                <th>Usuario</th>

            </tr>
        </thead>
        <tbody>
            @foreach($almacenamientoImpresoras as $item)
            <tr>
                <td><span class="tipo-badge tipo-almacenamiento-impresoras">Almacén</span></td>
                <td>{{ $item->cartuchos ? $item->cartuchos->cartucho : 'N/A' }}</td>
                <td>{{ $item->cartuchos && $item->cartuchos->impresora ? $item->cartuchos->impresora->impresora : 'N/A' }}</td>
                <td>{{ $item->cartuchos && $item->cartuchos->fabricante ? $item->cartuchos->fabricante->fabricante : 'N/A' }}</td>
                <td>{{ $item->colores ? $item->colores->colore : 'N/A' }}</td>
                <td style="text-align: center;">{{ $item->unidades }}</td>
                <td style="text-align: center;">{{ $item->unidades_disponibles }}</td>
                <td style="text-align: center;">{{ $item->cartuchos ? $item->cartuchos->stock_min : 'N/A' }}</td>
                <td>{{ $item->n_factura }}</td>
                <td>{{ \Carbon\Carbon::parse($item->fecha_ingreso)->format('d/m/Y') }}</td>
                <td>{{ $item->proveedores ? $item->proveedores->proveedor : 'N/A' }}</td>
                <td>{{ $item->users ? $item->users->username : 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($distribucionAlmacen->count() > 0)
    <div class="section-title">Distribuciones de Almacén</div>
    <table>
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Consumible</th>
                <th>Impresora</th>
                <th>Color</th>
                <th>Tipo de Consumible</th>
                <th style="text-align: center;">Unidades Originales</th>
                <th style="text-align: center;">Unidades Asignadas</th>
                <th style="text-align: center;">Unidades Disponibles</th>
                <th>Fecha Dist.</th>
                <th>Gerencia</th>
                <th>Área</th>
                <th>Usuario</th>
            </tr>
        </thead>
        <tbody>
            @foreach($distribucionAlmacen as $item)
            <tr>
                <td><span class="tipo-badge tipo-distribucion-almacen">Distribución</span></td>
                <td>{{ $item->almacenamientoImpresora && $item->almacenamientoImpresora->cartuchos ? $item->almacenamientoImpresora->cartuchos->cartucho  : 'N/A' }}</td>
                <td>{{ $item->almacenamientoImpresora && $item->almacenamientoImpresora->cartuchos && $item->almacenamientoImpresora->cartuchos->impresora ? $item->almacenamientoImpresora->cartuchos->impresora->impresora : 'N/A' }}</td>
                <td>{{ $item->almacenamientoImpresora && $item->almacenamientoImpresora->colores ? $item->almacenamientoImpresora->colores->colore : 'N/A' }}</td>
                <td>{{ $item->almacenamientoImpresora && $item->almacenamientoImpresora->cartuchos && $item->almacenamientoImpresora->cartuchos->fabricante ? $item->almacenamientoImpresora->cartuchos->fabricante->fabricante : 'N/A' }}</td>
                <td style="text-align: center;">{{ $item->unidades_asignadas_originales }}</td>
                <td style="text-align: center;">{{ $item->unidades_asignadas }}</td>
                <td style="text-align: center;">{{ $item->almacenamientoImpresora ? $item->almacenamientoImpresora->unidades_disponibles : 'N/A' }}</td>
                <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</td>

                <td>{{ $item->gerencia ? $item->gerencia->gerencia : 'N/A' }}</td>
                <td>{{ $item->area ? $item->area->area : 'N/A' }}</td>
                <td>{{ $item->users ? $item->users->username : 'N/A' }}</td>

            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($salidaAlmacen->count() > 0)
    <div class="section-title">Salidas de Almacén</div>
    <table>
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Consumible</th>
                <th>Impresora</th>
                <th>Color</th>
                <th>Tipo de Consumible</th>
                <th>Cantidad</th>
                <th>Unidades Asignadas</th>
                <th>Unidades Asignadas Restantes</th>
                <th>Unidades Totales Disponibles</th>
                <th>Fecha Salida</th>
                <th>Gerencia</th>
                <th>Área</th>
                <th>Usuario</th>

            </tr>
        </thead>
        <tbody>
            @foreach($salidaAlmacen as $item)
            <tr>
                <td><span class="tipo-badge tipo-salida-almacen">Salida Alm.</span></td>
                <td>{{ $item->almacenamientoImpresora && $item->almacenamientoImpresora->cartuchos ? $item->almacenamientoImpresora->cartuchos->cartucho  : 'N/A' }}</td>
                <td>{{ $item->almacenamientoImpresora && $item->almacenamientoImpresora->cartuchos && $item->almacenamientoImpresora->cartuchos->impresora ? $item->almacenamientoImpresora->cartuchos->impresora->impresora : 'N/A' }}</td>
                <td>{{ $item->almacenamientoImpresora && $item->almacenamientoImpresora->colores ? $item->almacenamientoImpresora->colores->colore : 'N/A' }}</td>
                <td>{{ $item->almacenamientoImpresora && $item->almacenamientoImpresora->cartuchos && $item->almacenamientoImpresora->cartuchos->fabricante ? $item->almacenamientoImpresora->cartuchos->fabricante->fabricante : 'N/A' }}</td>
                <td style="text-align: center;">{{ $item->cantidad }}</td>
                <td style="text-align: center;">{{ $item->historico_unidades_asignadas_originales }}</td>
                <td style="text-align: center;">{{ $item->historico_unidades_asignadas_restantes }}</td>
                <td style="text-align: center;">{{ $item->almacenamientoImpresora ? $item->almacenamientoImpresora->unidades_disponibles : 'N/A' }}</td>
                <td>{{ \Carbon\Carbon::parse($item->fecha_salida)->format('d/m/Y') }}</td>
                <td>{{ $item->distribucionAlmacenamientoImpresora && $item->distribucionAlmacenamientoImpresora->gerencia ? $item->distribucionAlmacenamientoImpresora->gerencia->gerencia : 'N/A' }}</td>
                <td>{{ $item->distribucionAlmacenamientoImpresora && $item->distribucionAlmacenamientoImpresora->area ? $item->distribucionAlmacenamientoImpresora->area->area : 'N/A' }}</td>
                <td>{{ $item->users ? $item->users->username : 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="footer">
        <p>Reporte de Auditoría generado por el Sistema de Inventario Unificado</p>
        <p>Fecha: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
