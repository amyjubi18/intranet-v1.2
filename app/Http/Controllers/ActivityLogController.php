<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Listado de registros de actividad del sistema.
     * Soporta tanto peticiones web como JSON estructurado para React.
     */
    public function index(Request $request)
    {
        $query = Activity::with(['causer' => function($q) {
            $q->select('id', 'name', 'username', 'email');
        }])->latest();

        // Filtro por tipo de evento (created, updated, deleted, exported)
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        // Filtro de búsqueda textual (usuario, modelo o descripción)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('subject_type', 'like', "%{$search}%")
                  ->orWhereHas('causer', function($causerQuery) use ($search) {
                      $causerQuery->where('username', 'like', "%{$search}%")
                                  ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = (int) $request->input('per_page', 20);
        $activities = $query->paginate($perPage);

        // Si la petición espera JSON o proviene de la API REST
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'data' => $activities,
                'message' => 'Registros de actividad obtenidos exitosamente'
            ], 200);
        }

        return view('gtic.activity-log.index', compact('activities'));
    }

    /**
     * Detalle de un registro de actividad específico.
     */
    public function show($id, Request $request)
    {
        $activity = Activity::with('causer')->findOrFail($id);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'data' => $activity,
                'message' => 'Detalle de actividad obtenido exitosamente'
            ], 200);
        }

        return view('gtic.activity-log.show', compact('activity'));
    }

    public function exportExcel()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ActivityLogExport, 'registro_actividades.xlsx');
    }

    public function exportPdf()
    {
        $activities = Activity::with('causer')->latest()->get();

        $pdf = \PDF::loadView('gtic.activity-log.pdf', compact('activities'));
        return $pdf->download('gtic.registro_actividades.pdf');
    }
}
