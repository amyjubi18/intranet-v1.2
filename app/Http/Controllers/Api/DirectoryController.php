<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;

class DirectoryController extends Controller
{
    private $filePath;

    public function __construct()
    {
        $this->filePath = storage_path('app/directory.json');
    }

    /**
     * Leer todos los registros del directorio
     */
    public function index()
    {
        if (!File::exists($this->filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'Archivo de directorio no encontrado'
            ], 404);
        }

        $content = File::get($this->filePath);
        $directory = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'success' => false,
                'message' => 'Error al parsear el archivo de directorio'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => $directory
        ]);
    }

    /**
     * Crear nuevo registro en el directorio
     */
    public function store(Request $request)
    {
        // Verificar que sea Admin
        if (!Auth::user()->hasRole('Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción'
            ], 403);
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'extension' => 'required|string|max:20',
            'icono' => 'nullable|string|max:10'
        ]);

        if (!File::exists($this->filePath)) {
            $directory = [];
        } else {
            $content = File::get($this->filePath);
            $directory = json_decode($content, true) ?: [];
        }

        // Generar nuevo ID
        $maxId = 0;
        foreach ($directory as $item) {
            if ($item['id'] > $maxId) {
                $maxId = $item['id'];
            }
        }

        $newItem = [
            'id' => $maxId + 1,
            'nombre' => $validated['nombre'],
            'extension' => $validated['extension'],
            'icono' => $validated['icono'] ?? '📞'
        ];

        $directory[] = $newItem;

        if (File::put($this->filePath, json_encode($directory, JSON_PRETTY_PRINT))) {
            chmod($this->filePath, 0666); // Restablecer permisos
            return response()->json([
                'success' => true,
                'message' => 'Registro creado correctamente',
                'data' => $newItem
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => 'Error al guardar el registro'
        ], 500);
    }

    /**
     * Actualizar un registro del directorio
     */
    public function update(Request $request, $id)
    {
        // Verificar que sea Admin
        if (!Auth::user()->hasRole('Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción'
            ], 403);
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'extension' => 'required|string|max:20',
            'icono' => 'nullable|string|max:10'
        ]);

        if (!File::exists($this->filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'Archivo de directorio no encontrado'
            ], 404);
        }

        $content = File::get($this->filePath);
        $directory = json_decode($content, true) ?: [];

        $index = null;
        foreach ($directory as $key => $item) {
            if ($item['id'] == $id) {
                $index = $key;
                break;
            }
        }

        if ($index === null) {
            return response()->json([
                'success' => false,
                'message' => 'Registro no encontrado'
            ], 404);
        }

        $directory[$index] = [
            'id' => $id,
            'nombre' => $validated['nombre'],
            'extension' => $validated['extension'],
            'icono' => $validated['icono'] ?? $directory[$index]['icono']
        ];

        if (File::put($this->filePath, json_encode($directory, JSON_PRETTY_PRINT))) {
            chmod($this->filePath, 0666); // Restablecer permisos
            return response()->json([
                'success' => true,
                'message' => 'Registro actualizado correctamente',
                'data' => $directory[$index]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Error al guardar los cambios'
        ], 500);
    }

    /**
     * Eliminar un registro del directorio
     */
    public function destroy($id)
    {
        // Verificar que sea Admin
        if (!Auth::user()->hasRole('Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción'
            ], 403);
        }

        if (!File::exists($this->filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'Archivo de directorio no encontrado'
            ], 404);
        }

        $content = File::get($this->filePath);
        $directory = json_decode($content, true) ?: [];

        $index = null;
        foreach ($directory as $key => $item) {
            if ($item['id'] == $id) {
                $index = $key;
                break;
            }
        }

        if ($index === null) {
            return response()->json([
                'success' => false,
                'message' => 'Registro no encontrado'
            ], 404);
        }

        $deletedItem = $directory[$index];
        unset($directory[$index]);
        $directory = array_values($directory);

        if (File::put($this->filePath, json_encode($directory, JSON_PRETTY_PRINT))) {
            chmod($this->filePath, 0666); // Restablecer permisos
            return response()->json([
                'success' => true,
                'message' => 'Registro eliminado correctamente',
                'data' => $deletedItem
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Error al eliminar el registro'
        ], 500);
    }
}

