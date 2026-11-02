<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    use HasFactory;

    protected $table = 'eventos';

    protected $fillable = [
        'title',
        'descripcion',
        'start',
        'end',
        'color',
        'all_day'
    ];

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
        'all_day' => 'boolean'
    ];

    /**
     * Get all events for calendar display
     */
    public static function getCalendarEvents($month = null, $year = null)
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        return static::whereYear('start', $year)
            ->whereMonth('start', $month)
            ->orderBy('start', 'asc')
            ->get();
    }

    /**
     * Get events for a specific day
     */
    public static function getEventsForDate($date)
    {
        return static::whereDate('start', $date)->get();
    }

    /**
     * Get upcoming events
     */
    public static function getUpcomingEvents($limit = 5)
    {
        return static::where('start', '>=', now())
            ->orderBy('start', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get formatted event for API response
     */
    public function toCalendarFormat()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->descripcion,
            'start' => $this->start->format('Y-m-d H:i:s'),
            'end' => $this->end->format('Y-m-d H:i:s'),
            'color' => $this->color ?? '#3b82f6',
            'allDay' => $this->all_day ?? false
        ];
    }
}

