<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DocsEditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:500',
            'description' => 'required|string|max:500',
            'documento'=> 'required|file|max:2000|'
        ];
    }
}
