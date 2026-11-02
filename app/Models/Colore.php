<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Colore extends Model
{
    use HasFactory;
    use LogsActivity;
    protected $fillable =['colore'];
    public function inventario_impresoras()
    {
        return $this->hasMany(InventarioImpresora::class, 'id');
    }
    public function salida_impresoras()
    {
        return $this->hasMany(SalidaImpresora::class, 'id');
    }
    public function cartuchos()
    {
        return $this->hasMany(Cartucho::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['colore'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
