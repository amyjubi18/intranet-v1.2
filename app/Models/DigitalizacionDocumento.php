<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class DigitalizacionDocumento extends Model
{
    use HasFactory;
    use LogsActivity;
    protected $table = 'digitalizacion_documentos';
    protected $fillable = [
        'digitalizacione_id',
        'nombre_archivo',
        'ruta',
        'user_id',
        'tipo_documento_id',
    ];

    public function digitalizacione()
    {
        return $this->belongsTo(Digitalizacione::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tipo_documento()
    {
        return $this->belongsTo(TipoDocumento::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['digitalizacione_id', 'nombre_archivo', 'ruta', 'user_id', 'tipo_documento_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
