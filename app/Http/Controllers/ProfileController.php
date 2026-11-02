<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\PasswordUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
            'passwordExpirationDays' => $request->user()->getDaysUntilPasswordExpiration(),
            'passwordExpired' => $request->user()->isPasswordExpired(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Actualizar datos básicos
        $user->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        // Manejar la carga de la foto de perfil
        if ($request->hasFile('profile_photo')) {
            $photo = $request->file('profile_photo');

            // Validar archivo usando validación de Laravel
            $request->validate([
                'profile_photo' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Eliminar foto anterior si existe
            if ($user->profile_photo && file_exists(public_path('archivos/avatars/' . $user->profile_photo))) {
                unlink(public_path('archivos/avatars/' . $user->profile_photo));
            }

            // Generar nombre único para la nueva foto
            $photoName = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();

            // Mover la foto al directorio de avatars
            $photo->move(public_path('archivos/avatars'), $photoName);

            // Actualizar el nombre de la foto en la base de datos
            $user->profile_photo = $photoName;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(PasswordUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $newPassword = $request->input('password');

        // Save old password to history before updating
        $historyCount = config('security.password.history_count', 5);
        $user->passwordHistory()->create([
            'password' => $user->password,
        ]);

        // Keep only last N passwords
        $user->passwordHistory()
            ->orderBy('created_at', 'desc')
            ->skip($historyCount)
            ->take(PHP_INT_MAX)
            ->delete();

        // Update password with security tracking
        $user->password = bcrypt($newPassword);
        $user->password_changed_at = now();
        $user->password_expires_at = now()->addDays(config('security.password.expiration_days', 90));
        $user->save();

        return Redirect::route('profile.edit')->with('status', 'password-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current-password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
