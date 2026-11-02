<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    /**
     * Get all events for the calendar
     */
    public function index(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $events = Evento::getCalendarEvents($month, $year);

        return response()->json([
            'success' => true,
            'data' => $events->map(function($event) {
                return $event->toCalendarFormat();
            }),
            'month' => $month,
            'year' => $year
        ]);
    }

    /**
     * Store a newly created event
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'start' => 'required|date',
            'end' => 'nullable|date|after_or_equal:start',
            'color' => 'nullable|string|max:7',
            'all_day' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $event = Evento::create([
            'title' => $request->title,
            'descripcion' => $request->descripcion ?? '',
            'start' => $request->start,
            'end' => $request->end ?? $request->start,
            'color' => $request->color ?? '#3b82f6',
            'all_day' => $request->all_day ?? false
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Evento creado exitosamente',
            'data' => $event->toCalendarFormat()
        ], 201);
    }

    /**
     * Update the specified event
     */
    public function update(Request $request, $id)
    {
        $event = Evento::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'start' => 'sometimes|required|date',
            'end' => 'nullable|date|after_or_equal:start',
            'color' => 'nullable|string|max:7',
            'all_day' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $event->update([
            'title' => $request->title ?? $event->title,
            'descripcion' => $request->descripcion ?? $event->descripcion,
            'start' => $request->start ?? $event->start,
            'end' => $request->end ?? $event->end,
            'color' => $request->color ?? $event->color,
            'all_day' => $request->all_day ?? $event->all_day
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Evento actualizado exitosamente',
            'data' => $event->toCalendarFormat()
        ]);
    }

    /**
     * Remove the specified event
     */
    public function destroy($id)
    {
        $event = Evento::findOrFail($id);
        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Evento eliminado exitosamente'
        ]);
    }

    /**
     * Get events for a specific date
     */
    public function byDate(Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        $events = Evento::getEventsForDate($request->date);

        return response()->json([
            'success' => true,
            'data' => $events->map(function($event) {
                return $event->toCalendarFormat();
            }),
            'date' => $request->date
        ]);
    }

    /**
     * Get upcoming events
     */
    public function upcoming()
    {
        $events = Evento::getUpcomingEvents();

        return response()->json([
            'success' => true,
            'data' => $events->map(function($event) {
                return $event->toCalendarFormat();
            })
        ]);
    }
}

