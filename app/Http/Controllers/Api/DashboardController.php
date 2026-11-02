<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Documento;
use App\Models\User;
use App\Models\Estanteria;
use App\Models\Permission;

class DashboardController extends Controller
{
    public function metrics()
    {
        $stats = [
            ['title' => 'Total Documentos', 'value' => Documento::count(), 'icon' => '📄', 'trend' => 12, 'color' => 'primary'],
            ['title' => 'Usuarios Activos', 'value' => User::count(), 'icon' => '👥', 'trend' => 8, 'color' => 'info'],
            ['title' => 'Estanterías', 'value' => Estanteria::count(), 'icon' => '🗄️', 'trend' => 0, 'color' => 'secondary'],
            ['title' => 'Permisos', 'value' => Permission::count(), 'icon' => '🔑', 'trend' => -3, 'color' => 'accent']
        ];

        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = ['name' => $date->format('M'), 'value' => Documento::whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)->count()];
        }

        return response()->json([
            'stats' => $stats,
            'lineChart' => ['title' => 'Documentos por Mes', 'data' => $months, 'dataKey' => 'value'],
            'barChart' => ['title' => 'Documentos por Categoría', 'data' => [['name' => 'General', 'value' => 45], ['name' => 'RRHH', 'value' => 32]]],
            'pieChart' => ['title' => 'Distribución por Tipo', 'data' => [['name' => 'PDF', 'value' => 65], ['name' => 'Word', 'value' => 20]]],
            'activities' => [['icon' => '📄', 'iconBg' => 'bg-blue-100', 'title' => 'Nuevo documento', 'description' => 'Documento creado', 'time' => '2 min']]
        ]);
    }
}
