<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class ReciboPagoController extends Controller
{
    public function sendRequest(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe estar autenticado para enviar una solicitud.'
                ], 401);
            }

            if (!$user->email) {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario no tiene un correo electrónico asociado.'
                ], 400);
            }

            // Obtener la ruta del logo
            $logoPath = public_path('img/Logo.png');
            $logoContent = file_exists($logoPath) ? file_get_contents($logoPath) : null;
            $logoMime = file_exists($logoPath) ? mime_content_type($logoPath) : 'image/png';

            // Enviar el correo directamente
            Mail::send('emails.recibo-pago', ['user' => $user], function ($message) use ($user, $logoContent, $logoMime) {
                $message->from(
                    config('mail.from.address', 'hello@example.com'),
                    config('mail.from.name', 'Intranet CODECYT')
                );
                $message->to('tecnologia@codecyt.gob.ve');
                $message->subject('Solicitud de Recibo de Pago - ' . $user->name . ' ' . $user->lastname);

                // Incrustar el logo como imagen embebida
                if ($logoContent) {
                    $message->embedData($logoContent, 'logo_codecut', $logoMime);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de recibo de pago enviada correctamente.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        $user = Auth::user();
        return view('recibo-pago.index', compact('user'));
    }
}

