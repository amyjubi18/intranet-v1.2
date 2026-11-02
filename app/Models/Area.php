<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Area extends Model
{
    use HasFactory;
    use LogsActivity;
    protected $fillable =['area'];
    public function salida_oficinas()
    {
        return $this->hasMany(SalidaOficina::class, 'id');
    }
    public function salida_impresoras()
    {
        return $this->hasMany(SalidaImpresora::class, 'id');
    }

    public function gerencias()
    {
        return $this->hasMany(Gerencia::class);
    }
    public function inventario_impresoras()
    {
        return $this->hasMany(InventarioImpresora::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['area'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
