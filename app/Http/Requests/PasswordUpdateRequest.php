<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rules\PasswordStrength;
use App\Rules\PasswordHistory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class PasswordUpdateRequest extends FormRequest
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
            'current_password' => ['required', 'string', 'current_password'],
            'password' => [
                'required',
                'string',
                'confirmed',
                new PasswordStrength(),
                new PasswordHistory(auth()->id()),
            ],
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
            'current_password.required' => 'La contraseña actual es obligatoria.',
            'current_password.current_password' => 'La contraseña actual es incorrecta.',
            'password.required' => 'La nueva contraseña es obligatoria.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isEmpty()) {
                $this->validatePasswordNotReused();
            }
        });
    }

    /**
     * Validate that the new password is not in the history.
     */
    protected function validatePasswordNotReused(): void
    {
        $user = auth()->user();
        $newPassword = $this->input('password');

        // Check password history
        $historyCount = config('security.password.history_count', 5);
        $oldPasswords = $user->passwordHistory()
            ->orderBy('created_at', 'desc')
            ->limit($historyCount)
            ->pluck('password');

        foreach ($oldPasswords as $oldPassword) {
            if (Hash::check($newPassword, $oldPassword)) {
                $this->validator->errors()->add(
                    'password',
                    'No puede reutilizar ninguna de sus últimas ' . $historyCount . ' contraseñas.'
                );
                return;
            }
        }
    }
}

