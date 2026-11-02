<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;
use App\Models\User;
use App\Models\UserNotification;

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

        return response()->json([
            'success' => true,
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

        return response()->json([
            'success' => true,
            'message' => 'Notificación marcada como leída'
        ]);
    }

    /**
     * Obtener estado de notificaciones no leídas
     */
    public function getUnreadCount(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['count' => 0]);
        }

        // Contar notificaciones de Laravel (incluye birthday wishes)
        $laravelCount = DatabaseNotification::where('notifiable_id', $user->id)
            ->where('notifiable_type', User::class)
            ->whereNull('read_at')
            ->count();

        // Contar notificaciones personalizadas
        $customCount = UserNotification::where('user_id', $user->id)
            ->unread()
            ->count();

        return response()->json(['count' => $laravelCount + $customCount]);
    }
}

