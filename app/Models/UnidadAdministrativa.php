<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadAdministrativa extends Model
{
    use HasFactory;
    protected $fillable =['unidad_administrativa'];
    public function bienes()
    {
        return $this->hasMany(Bienes::class, 'id');
    }
}
