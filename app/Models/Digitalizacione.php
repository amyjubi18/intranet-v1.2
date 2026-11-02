<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Digitalizacione extends Model
{
    use HasFactory;
    use LogsActivity;
    protected $table ='digitalizaciones';
    protected $fillable = [

    'cedula',
    'nombre_apellido',
    'salud',
    'expediente',
    'area_adscrita_id',
    'user_id',
    ];
    protected $casts = [
        'salud' => 'string',
    ];
public function area_adscrita()
    {
        return $this->belongsTo(AreaAdscrita::class);
    }
public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación con documentos múltiples
    public function documentos()
    {
        return $this->hasMany(DigitalizacionDocumento::class, 'digitalizacione_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['cedula', 'nombre_apellido', 'salud', 'expediente', 'area_adscrita_id', 'user_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

