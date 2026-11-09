<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Color extends Model
{
    use HasFactory;
    use LogsActivity;
    protected $fillable =['color'];

    protected $appends = ['color_css'];

    public function bienes()
    {
        return $this->hasMany(Biene::class, 'color_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['color'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Obtiene el código CSS / Hex correspondiente al nombre del color
     */
    public function getColorCssAttribute(): string
    {
        return self::mapColorToCss($this->color ?? '');
    }

    /**
     * Mapea un nombre de color en español/inglés a su valor CSS
     */
    public static function mapColorToCss(?string $name): string
    {
        if (empty($name)) {
            return '#cccccc';
        }

        $colorName = strtolower(trim($name));

        // Si ya es un valor hexadecimal válido
        if (preg_match('/^#([a-f0-9]{3}){1,2}$/i', $colorName)) {
            return $colorName;
        }

        // Mapeo detallado de colores comunes en español
        $colorMap = [
            'negro' => '#1f2937',
            'blanco' => '#ffffff',
            'blanco humo' => '#f3f4f6',
            'hueso' => '#f5f5dc',
            'marfil' => '#fffff0',
            'gris' => '#6b7280',
            'gris claro' => '#9ca3af',
            'gris oscuro' => '#374151',
            'plata' => '#cbd5e1',
            'plateado' => '#e5e7eb',
            'azul' => '#3b82f6',
            'azul oscuro' => '#1e40af',
            'azul claro' => '#60a5fa',
            'azul marino' => '#1e3a8a',
            'azul marine' => '#000080',
            'azul rey' => '#2563eb',
            'azul cobalto' => '#0047ab',
            'azul tiffany' => '#81d8d0',
            'celeste' => '#7dd3fc',
            'turquesa' => '#14b8a6',
            'cyan' => '#06b6d4',
            'rojo' => '#ef4444',
            'rojo oscuro' => '#b91c1c',
            'rojo claro' => '#f87171',
            'rojo vino' => '#722f37',
            'vino' => '#722f37',
            'granate' => '#800000',
            'verde' => '#22c55e',
            'verde oscuro' => '#15803d',
            'verde claro' => '#4ade80',
            'verde militar' => '#4b5320',
            'verde bosque' => '#228b22',
            'esmeralda' => '#10b981',
            'oliva' => '#808000',
            'olivo' => '#808000',
            'menta' => '#98ff98',
            'lima' => '#32cd32',
            'amarillo' => '#eab308',
            'amarillo oscuro' => '#ca8a04',
            'amarillo claro' => '#fde047',
            'limon' => '#fff700',
            'dorado' => '#fbbf24',
            'oro' => '#ffd700',
            'ámbar' => '#f59e0b',
            'ambar' => '#f59e0b',
            'naranja' => '#f97316',
            'morado' => '#a855f7',
            'violeta' => '#8b5cf6',
            'lila' => '#c084fc',
            'purpura' => '#800080',
            'púrpura' => '#800080',
            'fucsia' => '#d946ef',
            'magenta' => '#d63384',
            'rosa' => '#ec4899',
            'rosado' => '#ffc0cb',
            'cafe' => '#78350f',
            'café' => '#78350f',
            'marrón' => '#78350f',
            'marron' => '#78350f',
            'chocolate' => '#7b3f00',
            'beige' => '#f5f5dc',
            'crema' => '#fffbeb',
            'arena' => '#d4c4a8',
            'bronce' => '#b45309',
            'cobre' => '#b45309',
            'coral' => '#ff7f50',
            'salmon' => '#fa8072',
            'piel' => '#fecaca',
            'carne' => '#fecaca',
            'zafiro' => '#0f766e',
            'acuamarina' => '#2dd4bf',
            'aguamarina' => '#7fffd4',
            'rubí' => '#e11d48',
            'rubi' => '#e11d48',
        ];

        if (isset($colorMap[$colorName])) {
            return $colorMap[$colorName];
        }

        foreach ($colorMap as $key => $val) {
            if (str_contains($colorName, $key)) {
                return $val;
            }
        }

        $cssColors = [
            'red' => '#ef4444', 'blue' => '#3b82f6', 'green' => '#22c55e',
            'yellow' => '#eab308', 'orange' => '#f97316', 'purple' => '#a855f7',
            'pink' => '#ec4899', 'black' => '#1f2937', 'white' => '#ffffff',
            'gray' => '#6b7280', 'grey' => '#6b7280', 'brown' => '#78350f',
            'silver' => '#e5e7eb', 'gold' => '#fbbf24', 'navy' => '#1e3a8a',
            'teal' => '#14b8a6', 'cyan' => '#06b6d4', 'lime' => '#32cd32',
            'maroon' => '#722f37', 'olive' => '#808000', 'violet' => '#8b5cf6'
        ];

        if (isset($cssColors[$colorName])) {
            return $cssColors[$colorName];
        }

        return '#94a3b8';
    }
}
