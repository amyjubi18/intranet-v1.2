<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaEspecifica extends Model
{
    use HasFactory;
    protected $fillable =['categoria_especifica'];
    public function bienes()
    {
        return $this->hasMany(Bienes::class, 'id');
    }
}
