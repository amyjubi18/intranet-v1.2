<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaGeneral extends Model
{
    use HasFactory;
    protected $fillable =['categoria_general'];
    public function bienes()
    {
        return $this->hasMany(Bienes::class, 'id');
    }
}
