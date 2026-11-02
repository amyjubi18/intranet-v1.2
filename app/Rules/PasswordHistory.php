<?php

namespace App\Rules;

use App\Models\User;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class PasswordHistory implements Rule
{
    protected $userId;
    protected $historyCount;

    /**
     * Create a new rule instance.
     */
    public function __construct($userId = null, $historyCount = 5)
    {
        $this->userId = $userId;
        $this->historyCount = $historyCount;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (!$this->userId) {
            return true;
        }

        $user = User::find($this->userId);

        if (!$user) {
            return true;
        }

        // Verificar si la contraseña coincide con alguna de las anteriores
        $oldPasswords = $user->passwordHistory()
            ->orderBy('created_at', 'desc')
            ->limit($this->historyCount)
            ->pluck('password');

        foreach ($oldPasswords as $oldPassword) {
            if (Hash::check($value, $oldPassword)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'No puede reutilizar ninguna de sus últimas :count contraseñas.';
    }
}

