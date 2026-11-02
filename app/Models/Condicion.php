<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Condicion extends Model
{
    use HasFactory;
    use LogsActivity;
    protected $fillable =['condicion'];
    public function bienes()
    {
        return $this->hasMany(Biene::class, 'condicion_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['condicion'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
