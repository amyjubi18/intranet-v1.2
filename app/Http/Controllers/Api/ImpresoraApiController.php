<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Impresora;
use Illuminate\Http\Request;

class ImpresoraApiController extends Controller
{
    /**
     * Return a list of impresoras for API.
     */
    public function index(Request $request)
    {
        $query = Impresora::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('impresora', 'like', '%' . $search . '%');
        }

        $impresoras = $query->orderBy('impresora')->get(['id', 'impresora', 'imagen'])->map(function ($impresora) {
            return [
                'id' => $impresora->id,
                'impresora' => $impresora->impresora,
                'imagen' => $impresora->imagen ? asset('storage/' . $impresora->imagen) : null,
            ];
        });

        return response()->json($impresoras);
    }
}
