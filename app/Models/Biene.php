<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Biene extends Model
{
    use HasFactory;
    protected $table ='bienes';
    protected $fillable = ['sede',
    'unidad_administrativa_id',
    'n_interno_bien',
     'descripcion',
     'forma_adquisicion_id',
     'fecha_adquisicion',
      'n_factura',
       'valor_factura',
       'moneda',
        'estado_del_uso_id',
         'condicion_fisica',
          'marca',
          'modelo',
          'serial',
           'categoria_general_id',
            'sub_categoria_id',
             'categoria_especifica_id',
             'codigo_contable'];
    public function unidad_administrativas()
    {
        return $this->belongsTo(UnidadAdministrativa::class, 'unidad_administrativa_id' );
    }
    public function categoria_especificas()
    {
        return $this->belongsTo(CategoriaEspecifica::class, 'categoria_especifica_id');
    }

    public function categoria_generals()
    {
        return $this->belongsTo(CategoriaGeneral::class, 'categoria_general_id');
    }
    public function estado_del_usos()
    {
        return $this->belongsTo(EstadoDelUso::class, 'estado_del_uso_id');
    }
    public function forma_adquisicions()
    {
        return $this->belongsTo(FormaAdquisicion::class,'forma_adquisicion_id');
    }
    public function sub_categorias()
    {
        return $this->belongsTo(SubCategoria::class, 'sub_categoria_id');
    }
    /* public function users()
    {
        return $this->belongsTo(User::class);
    } */

}
