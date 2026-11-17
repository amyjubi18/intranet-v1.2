<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Salida de Oficina</title>
    <style>
        @page {
            size: landscape;
            margin: 12mm 10mm 15mm 10mm;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #1e293b;
            font-size: 9px;
            line-height: 1.25;
        }
        .header {
            text-align: center;
            margin-bottom: 12px;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 6px;
        }
        .header h1 {
            color: #1e3a8a;
            margin: 0;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 3px 0 0 0;
            color: #64748b;
            font-size: 9px;
        }
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .stats-table td {
            border: none;
            padding: 0 4px;
        }
        .stat-box {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 6px 10px;
            border-radius: 4px;
            text-align: center;
        }
        .stat-box h3 {
            margin: 0;
            color: #1e3a8a;
            font-size: 13px;
        }
        .stat-box p {
            margin: 2px 0 0 0;
            font-size: 8px;
            text-transform: uppercase;
            color: #475569;
            font-weight: bold;
        }
        .filters-info {
            margin-bottom: 10px;
            padding: 6px 10px;
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 4px;
            font-size: 8.5px;
            color: #1e40af;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 5px;
            text-align: left;
            vertical-align: middle;
            font-size: 8.5px;
        }
        table.data-table th {
            background-color: #1e3a8a;
            color: #ffffff;
            font-weight: bold;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        thead {
            display: table-header-group;
        }
        tr {
            page-break-inside: avoid;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: bold;
            background-color: #e2e8f0;
            color: #334155;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Salida de Oficina</h1>
        <p>Generado el {{ date('d/m/Y H:i') }} | Sistema Intranet Codecyt</p>
    </div>

    @if(!empty($filtersApplied) && $filtersApplied)
    <div class="filters-info">
        <strong>Filtros aplicados:</strong> {{ !empty($filterDescription) ? $filterDescription : 'Personalizados' }}
    </div>
    @endif

    <table class="stats-table">
        <tr>
            <td style="width: 50%;">
                <div class="stat-box">
                    <h3>{{ number_format($totalRegistros, 0, ',', '.') }}</h3>
                    <p>Total Registros Despachados</p>
                </div>
            </td>
            <td style="width: 50%;">
                <div class="stat-box">
                    <h3>{{ number_format($totalUnidades, 0, ',', '.') }}</h3>
                    <p>Total Unidades Retiradas de Almacén</p>
                </div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">ITEM</th>
                <th style="width: 8%;" class="text-center">Fecha Salida</th>
                <th style="width: 17%;">Artículo</th>
                <th style="width: 7%;" class="text-center">Unidad</th>
                <th style="width: 14%;">Gerencia</th>
                <th style="width: 14%;">Área</th>
                <th style="width: 8%;">Solicitante</th>
                <th style="width: 6%;" class="text-center">Cantidad</th>
                <th style="width: 7%;" class="text-center">Disponible</th>
                <th style="width: 7%;" class="text-center">Histórico</th>
                <th style="width: 7%;" class="text-center">Fecha Reg.</th>
            </tr>
        </thead>
        <tbody>
            @forelse($salida_oficinas as $item)
            @php
                $stockHistorico = $item->stockHistorico ?? $item->historico_unidades_disponibles ?? 0;
                $stockRestante = max(0, $stockHistorico - $item->cantidad);
                $artId = $item->articulos ? $item->articulos->id : ($item->articulo_id ?? $item->id);
            @endphp
            <tr>
                <td class="text-center" style="font-weight: bold; color: #1e3a8a;">
                    {{ str_pad($artId, 4, '0', STR_PAD_LEFT) }}
                </td>
                <td class="text-center">
                    {{ $item->fecha_salida ? \Carbon\Carbon::parse($item->fecha_salida)->format('d/m/Y') : 'N/A' }}
                </td>
                <td>
                    <strong>{{ $item->articulos ? $item->articulos->articulo : 'Artículo #' . ($item->articulo_id ?? 'N/A') }}</strong>
                </td>
                <td class="text-center">
                    <span class="badge">{{ $item->unidad_medidas ? $item->unidad_medidas->unidad_medida : 'UNIDAD' }}</span>
                </td>
                <td>
                    {{ $item->gerencias ? $item->gerencias->gerencia : 'N/A' }}
                </td>
                <td>
                    {{ $item->areas ? $item->areas->area : 'N/A' }}
                </td>
                <td>
                    {{ $item->users ? ($item->users->name ?? $item->users->username ?? 'N/A') : 'N/A' }}
                </td>
                <td class="text-center" style="font-weight: bold; color: #dc2626;">
                    {{ number_format($item->cantidad, 0, ',', '.') }}
                </td>
                <td class="text-center" style="color: #059669; font-weight: bold;">
                    {{ number_format($stockRestante, 0, ',', '.') }}
                </td>
                <td class="text-center" style="color: #64748b;">
                    {{ number_format($stockHistorico, 0, ',', '.') }}
                </td>
                <td class="text-center">
                    {{ $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') : 'N/A' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="11" class="text-center" style="padding: 15px; color: #64748b;">
                    No se encontraron registros de salidas de oficina con los filtros seleccionados.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Reporte Oficial de Almacén - Generado automáticamente por Sistema Intranet Codecyt &copy; {{ date('Y') }}
    </div>
</body>
</html>
