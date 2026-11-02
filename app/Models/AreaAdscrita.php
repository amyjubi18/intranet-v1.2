<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class AreaAdscrita extends Model
{
    use HasFactory;
    use LogsActivity;
    protected $fillable =['area_adscrita'];
    public function digitalizaciones()
    {
        return $this->hasMany(Digitalizacione::class, 'id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['area_adscrita'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
