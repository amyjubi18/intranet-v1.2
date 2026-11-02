<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Documento;
use App\Models\User;
use App\Models\Estanteria;
use App\Models\Permission;
use App\Notifications\BirthdayWishNotification;
use Illuminate\Support\Facades\Notification;

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

    /**
     * Get birthdays for current month and calendar data
     */
    public function birthdays()
    {
        // Get birthdays for current month
        $currentMonthBirthdays = User::getBirthdaysForCurrentMonth();

        // Format current month birthdays
        $formattedBirthdays = $currentMonthBirthdays->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'lastname' => $user->lastname,
                'fullname' => $user->name . ' ' . $user->lastname,
                'departamento' => $user->departamento ?? 'Sin asignar',
                'birthday_date' => $user->birthday_display_date,
                'birthday_day' => $user->birthday_day,
                'initials' => $user->initials,
                'days_until' => $user->days_until_birthday,
                'has_today' => $user->fecha_nacimiento && $user->fecha_nacimiento->isToday()
            ];
        });

        // Get all birthdays for calendar
        $allBirthdays = User::getAllBirthdaysForCalendar();

        // Format calendar data
        $calendarData = [];
        foreach ($allBirthdays as $month => $users) {
            $calendarData[$month] = $users->map(function($user) {
                return [
                    'day' => $user->birthday_day,
                    'name' => $user->name . ' ' . $user->lastname,
                    'departamento' => $user->departamento ?? 'Sin asignar'
                ];
            })->toArray();
        }

        // Month names in Spanish
        $months = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        return response()->json([
            'current_month' => now()->month,
            'current_month_name' => $months[now()->month],
            'current_year' => now()->year,
            'birthdays' => $formattedBirthdays,
            'calendar_data' => $calendarData,
            'total_this_month' => $formattedBirthdays->count(),
            'today_birthdays' => $formattedBirthdays->where('has_today', true)->values()
        ]);
    }

    /**
     * Send a birthday wish to a user
     */
    public function sendBirthdayWish(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $birthdayUser = User::findOrFail($request->user_id);

        // Get the authenticated user from session (web guard)
        $senderUser = null;

        // Try to get user from different auth methods
        if ($request->user()) {
            $senderUser = $request->user();
        } elseif (\Illuminate\Support\Facades\Auth::guard('web')->check()) {
            $senderUser = \Illuminate\Support\Facades\Auth::guard('web')->user();
        } elseif (\Illuminate\Support\Facades\Auth::check()) {
            $senderUser = \Illuminate\Support\Facades\Auth::user();
        }

        if (!$senderUser) {
            return response()->json([
                'success' => false,
                'message' => 'Debes estar autenticado para enviar felicitaciones'
            ], 401);
        }

        // Don't allow self-wishes
        if ($birthdayUser->id === $senderUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes enviarte felicitaciones a ti mismo'
            ], 400);
        }

        // Send notification
        Notification::send($birthdayUser, new BirthdayWishNotification($birthdayUser, $senderUser));

        return response()->json([
            'success' => true,
            'message' => '¡Felicitación enviada exitosamente a ' . $birthdayUser->name . '!'
        ]);
    }

    /**
     * Get user statistics for dashboard
     */
    public function userStats()
    {
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->orWhere('status', true)->count();
        $inactiveUsers = User::where(function($query) {
            $query->where('is_active', false)
                  ->orWhere('status', false);
        })->count();

        // Calculate new users this month
        $newUsersThisMonth = User::whereMonth('created_at', now()->month)
                                 ->whereYear('created_at', now()->year)
                                 ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_users' => $totalUsers,
                'active_users' => $activeUsers,
                'inactive_users' => $inactiveUsers,
                'new_users_this_month' => $newUsersThisMonth,
                'active_percentage' => $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100, 1) : 0,
            ]
        ]);
    }
}
