<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoDocumento extends Model
{
    use HasFactory;
    protected $fillable =['tipo_documento'];
    public function digitalizaciones()
    {
        return $this->hasMany(Digitalizacione::class, 'id');
    }
}
