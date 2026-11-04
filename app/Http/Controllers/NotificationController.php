<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;
use App\Models\User;
use App\Models\UserNotification;
use App\Http\View\Composers\NotificationComposer;

class NotificationController extends Controller
{
    /**
     * Marcar todas las notificaciones como leídas
     */
    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 401);
        }

        // Marcar notificaciones de Laravel (incluye birthday wishes)
        DatabaseNotification::where('notifiable_id', $user->id)
            ->where('notifiable_type', User::class)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Marcar todas las notificaciones del usuario como leídas
        UserNotification::where('user_id', $user->id)
            ->update(['read_at' => now()]);

        NotificationComposer::clearUserCache($user->id);

        return response()->json([
            'success' => true,
            'count' => 0,
            'message' => 'Todas las notificaciones han sido marcadas como leídas'
        ]);
    }

    /**
     * Marcar una notificación específica como leída
     */
    public function markAsRead(Request $request, $type)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 401);
        }

        // Marcar notificación de Laravel si es de tipo específico
        if ($type === 'birthday_wish') {
            DatabaseNotification::where('notifiable_id', $user->id)
                ->where('notifiable_type', User::class)
                ->where('data', 'like', '%"type":"birthday_wish"%')
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        UserNotification::where('user_id', $user->id)
            ->where('notification_type', $type)
            ->update(['read_at' => now()]);

        NotificationComposer::clearUserCache($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Notificación marcada como leída'
        ]);
    }

    /**
     * Obtener estado de notificaciones no leídas de forma ultra rápida
     */
    public function getUnreadCount(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['count' => 0]);
        }

        $cacheKey = "user_notifs_data_{$user->id}";
        $data = \Illuminate\Support\Facades\Cache::get($cacheKey);

        if ($data && isset($data['totalNotificaciones'])) {
            return response()->json([
                'success' => true,
                'count' => $data['totalNotificaciones']
            ]);
        }

        $composer = new NotificationComposer();
        $notificaciones = $composer->obtenerNotificaciones();
        $count = 0;
        foreach ($notificaciones as $n) {
            if (empty($n['leida'])) {
                $count++;
            }
        }

        \Illuminate\Support\Facades\Cache::put($cacheKey, [
            'notificaciones' => $notificaciones,
            'totalNotificaciones' => $count,
        ], 20);

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }
}

