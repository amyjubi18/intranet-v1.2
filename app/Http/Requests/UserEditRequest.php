<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserEditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
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
        $user = $this->route('user') ?? $this->route('id') ?? $this->user ?? $this->id;
        if (is_object($user)) {
            $userId = $user->id;
        } elseif (is_numeric($user) && $user > 0) {
            $userId = (int) $user;
        } else {
            $segments = $this->segments();
            $userId = !empty($segments) ? (int) end($segments) : 0;
        }
        return [
            'name' => 'required|string|min:2|max:50|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'lastname' => 'required|string|min:2|max:50|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'cedula' => 'required|regex:/^\d{1,2}\.\d{3}\.\d{3}$/|unique:users,cedula,' . $userId,
            'fecha_nacimiento' => 'sometimes|nullable|date|before:today',
            'username' => 'required|string|min:3|max:20|unique:users,username,' . $userId . '|regex:/^[a-zA-Z0-9_]+$/',
            'email' => 'required|email|unique:users,email,' . $userId,
            'departamento' => 'required|string',
            'roles' => 'required',
            'password' => 'sometimes|nullable|string|min:8|confirmed',
            'password_confirmation' => 'sometimes|nullable|string|min:8',
            'status' => 'nullable|boolean',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre es obligatorio',
            'name.min' => 'El nombre debe tener al menos 2 caracteres',
            'name.max' => 'El nombre no puede exceder 50 caracteres',
            'name.regex' => 'El nombre solo puede contener letras y espacios',

            'lastname.required' => 'El apellido es obligatorio',
            'lastname.min' => 'El apellido debe tener al menos 2 caracteres',
            'lastname.max' => 'El apellido no puede exceder 50 caracteres',
            'lastname.regex' => 'El apellido solo puede contener letras y espacios',

            'cedula.required' => 'La cédula es obligatoria',
            'cedula.regex' => 'Formato de cédula inválido. Ej: 12.345.678',
            'cedula.unique' => 'Esta cédula ya está registrada por otro usuario',

            'fecha_nacimiento.date' => 'Fecha de nacimiento inválida',
            'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy',

            'username.required' => 'El nombre de usuario es obligatorio',
            'username.min' => 'El usuario debe tener al menos 3 caracteres',
            'username.max' => 'El usuario no puede exceder 20 caracteres',
            'username.unique' => 'Este usuario ya está en uso por otro',
            'username.regex' => 'El usuario solo puede contener letras, números y guiones bajos',

            'email.required' => 'El correo electrónico es obligatorio',
            'email.email' => 'El correo electrónico debe ser válido',
            'email.unique' => 'Este correo electrónico ya está registrado por otro usuario',

            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',

            'password_confirmation.min' => 'La confirmación debe tener al menos 8 caracteres',

            'roles.required' => 'Debe seleccionar al menos un rol',
            'roles.integer' => 'Debe seleccionar un rol válido',

            'departamento.required' => 'El departamento es obligatorio',
            'profile_photo.image' => 'El archivo debe ser una imagen válida',
            'profile_photo.mimes' => 'La foto de perfil debe ser de formato JPG, JPEG, PNG o WEBP',
            'profile_photo.max' => 'La foto de perfil no debe superar los 2MB',
        ];
    }
}
