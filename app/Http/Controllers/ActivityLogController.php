<?php

namespace App\Http\Controllers;

use App\Models\LoginActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ActivityLogExport;

class SecurityActivityController extends Controller
{
    /**
     * Display security activity logs.
     */
    public function index(Request $request)
    {
        $query = LoginActivity::with('user');

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('ip_address')) {
            $query->where('ip_address', 'like', '%' . $request->ip_address . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Statistics
        $stats = [
            'total_today' => LoginActivity::whereDate('created_at', today())->count(),
            'success_today' => LoginActivity::whereDate('created_at', today())->success()->count(),
            'failed_today' => LoginActivity::whereDate('created_at', today())->failed()->count(),
            'unique_ips' => LoginActivity::whereDate('created_at', today())->distinct('ip_address')->count('ip_address'),
        ];

        return view('gtic.security-activity', compact('activities', 'stats'));
    }

    /**
     * Export activity logs.
     */
    public function export(Request $request)
    {
        $query = LoginActivity::with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->orderBy('created_at', 'desc')->get();

        return Excel::download(new ActivityLogExport($activities), 'security-activity.xlsx');
    }

    /**
     * Show failed login attempts.
     */
    public function failedAttempts(Request $request)
    {
        $query = LoginActivity::failed()->with('user');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('ip_address')) {
            $query->where('ip_address', $request->ip_address);
        }

        $attempts = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Group by IP
        $ipSummary = DB::table('login_activities')
            ->where('status', 'failed')
            ->whereDate('created_at', '>=', now()->subDays(7))
            ->groupBy('ip_address')
            ->select('ip_address', DB::raw('count(*) as total'))
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        return view('gtic.failed-attempts', compact('attempts', 'ipSummary'));
    }
}

