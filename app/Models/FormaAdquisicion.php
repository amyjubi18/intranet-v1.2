<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormaAdquisicion extends Model
{
    use HasFactory;
    protected $fillable =['forma_adquisicion'];
    public function bienes()
    {
        return $this->hasMany(Bienes::class, 'id');
    }
}
