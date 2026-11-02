<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class ConstanciaTrabajoController extends Controller
{
    /**
     * Enviar constancia de trabajo
     */
    public function sendRequest(Request $request)
    {
        try {
            // Obtener el usuario autenticado
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe estar autenticado para enviar una constancia de trabajo.'
                ], 401);
            }

            // Validar que el usuario tenga los datos requeridos
            if (!$user->email) {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario no tiene un correo electrónico asociado.'
                ], 400);
            }

            // Enviar el correo directamente
            // Obtener la ruta del logo
            $logoPath = public_path('img/Logo.png');
            $logoContent = file_exists($logoPath) ? file_get_contents($logoPath) : null;
            $logoMime = file_exists($logoPath) ? mime_content_type($logoPath) : 'image/png';

            Mail::send('emails.constancia-trabajo', ['user' => $user], function ($message) use ($user, $logoContent, $logoMime) {
                $message->from(
                    config('mail.from.address', 'hello@example.com'),
                    config('mail.from.name', 'Intranet CODECYT')
                );
                $message->to('tecnologia@codecyt.gob.ve');
                $message->subject('Constancia de Trabajo - ' . $user->name . ' ' . $user->lastname);

                // Incrustar el logo como imagen embebida usando embedData
                if ($logoContent) {
                    $message->embedData($logoContent, 'logo_codecut', $logoMime);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Constancia de trabajo enviada correctamente.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar la constancia: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar formulario de constancia de trabajo (vista)
     */
    public function index()
    {
        $user = Auth::user();
        return view('constancia-trabajo.index', compact('user'));
    }
}
