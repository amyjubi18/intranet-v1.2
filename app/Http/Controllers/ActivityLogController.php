<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index()
    {
        $activities = Activity::with('causer')->latest()->paginate(20);

        return view('gtic.activity-log.index', compact('activities'));
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

    public function show($id)
    {
        $activity = Activity::with('causer')->findOrFail($id);

        return view('gtic.activity-log.show', compact('activity'));
    }
}
