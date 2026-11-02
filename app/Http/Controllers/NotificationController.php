<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $count = UserNotification::where('user_id', $user->id)
            ->unread()
            ->count();

        return response()->json(['count' => $count]);
    }
}

