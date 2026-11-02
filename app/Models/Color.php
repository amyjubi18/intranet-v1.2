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
}
