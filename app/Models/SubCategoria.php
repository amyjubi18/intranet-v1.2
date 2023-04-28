<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategoria extends Model
{
    use HasFactory;
    protected $fillable =['sub_categoria'];
    public function bienes()
    {
        return $this->hasMany(Bienes::class, 'id');
    }
}
