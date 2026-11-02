<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Categoria extends Model
{
    use HasFactory;
    use LogsActivity;
    protected $fillable =['name'];
    public function documento()
    {
        return $this->hasMany(Documento::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

