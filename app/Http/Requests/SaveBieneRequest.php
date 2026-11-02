<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveDocumentoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [

            'sede' =>'required',
            'unidad_administrativa_id' => 'required',
            'n_interno_bien' => 'required',
            'descripcion' => 'required',
            'forma_adquisicion_id' => 'required',
            'fecha_adquisicion' => 'required',
            'n_factura' => 'required',
            'valor_factura' => 'required',
            'moneda' => 'required',
            'estado_del_uso_id' => 'required',
            'condicion_id' => 'required',
            'marca' => 'required',
            'modelo' => 'required',
            'color_id' => 'required',
            'categoria_general_id' => 'required',
            'sub_categoria_id' => 'required',
            'categoria_especifica_id' => 'required',
            'serial' => 'required',
        ];
    }
}
