<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'lastname' => ['nullable', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'cedula' => ['nullable', 'string', 'max:50'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'departamento' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'direccion' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Por favor ingresa un correo electrónico válido.',
            'email.unique' => 'Este correo electrónico ya está en uso.',
            'username.unique' => 'Este nombre de usuario ya está en uso.',
            'cedula.max' => 'La cédula no puede tener más de 50 caracteres.',
            'fecha_nacimiento.date' => 'Por favor ingresa una fecha válida.',
            'departamento.max' => 'El departamento no puede tener más de 255 caracteres.',
            'telefono.max' => 'El teléfono no puede tener más de 50 caracteres.',
            'direccion.max' => 'La dirección no puede tener más de 500 caracteres.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'lastname' => 'apellido',
            'username' => 'nombre de usuario',
            'email' => 'correo electrónico',
            'cedula' => 'cédula de identidad',
            'fecha_nacimiento' => 'fecha de nacimiento',
            'departamento' => 'departamento',
            'telefono' => 'teléfono',
            'direccion' => 'dirección',
        ];
    }
}

