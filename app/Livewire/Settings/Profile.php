<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Profile extends Component
{
    public string $name = '';

    public string $email = '';

    public string $telefono = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        $this->telefono = Auth::user()->telefono ?? '';
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],

            'telefono' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[0-9]+$/',
            ],
        ], [
            'telefono.regex' => 'El teléfono solo debe contener números.',
            'telefono.max'   => 'El teléfono no puede superar los 20 dígitos.',
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', PasswordRule::defaults(), 'confirmed'],
            ], [
                'current_password.current_password' => 'La contraseña es incorrecta.',
                'password.confirmed'                => 'La confirmación de la contraseña no coincide.',
                'password.min'                      => 'La contraseña debe tener al menos 8 caracteres.',
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        \Illuminate\Support\Facades\Auth::user()->update([
            'password' => $validated['password'],
            'plain_password_encrypted' => \Illuminate\Support\Facades\Crypt::encryptString($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        // CU-14.2: Cerrar sesión y redirigir al login tras cambio exitoso de contraseña
        Auth::logout();
        Session::invalidate();
        Session::regenerateToken();

        Session::flash('status', 'password-changed');

        $this->redirect(route('login'), navigate: false);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

}
