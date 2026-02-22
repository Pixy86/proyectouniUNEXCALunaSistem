<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Livewire\Component;

class RecoverPassword extends Component
{
    public string $email = '';
    public string $security_answer_1 = '';
    public string $security_answer_2 = '';
    public string $security_answer_3 = '';

    public bool $showPasswordModal = false;
    public string $recoveredPassword = '';
    public bool $emailVerified = false;
    public string $errorMessage = '';

    /**
     * Las 3 preguntas de seguridad predefinidas por el sistema.
     */
    public static function getSecurityQuestions(): array
    {
        return [
            1 => '¿Cuál es el nombre de tu primera mascota?',
            2 => '¿En qué ciudad naciste?',
            3 => '¿Cuál es el nombre de tu mejor amigo de la infancia?',
        ];
    }

    public function verifyEmail(): void
    {
        $this->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El formato del correo electrónico no es válido.',
        ]);

        $user = User::where('email', $this->email)->first();

        if (!$user) {
            $this->addError('email', 'No se encontró una cuenta con este correo electrónico.');
            return;
        }

        if (!$user->security_answer_1 || !$user->security_answer_2 || !$user->security_answer_3) {
            $this->addError('email', 'Esta cuenta no tiene preguntas de seguridad configuradas.');
            return;
        }

        $this->emailVerified = true;
        $this->errorMessage = '';
    }

    public function verifyAnswers(): void
    {
        $this->validate([
            'security_answer_1' => ['required', 'string', 'regex:/^[\pL\s]+$/u'],
            'security_answer_2' => ['required', 'string', 'regex:/^[\pL\s]+$/u'],
            'security_answer_3' => ['required', 'string', 'regex:/^[\pL\s]+$/u'],
        ], [
            'security_answer_1.required' => 'La respuesta es obligatoria.',
            'security_answer_1.regex' => 'Solo se permiten letras y espacios.',
            'security_answer_2.required' => 'La respuesta es obligatoria.',
            'security_answer_2.regex' => 'Solo se permiten letras y espacios.',
            'security_answer_3.required' => 'La respuesta es obligatoria.',
            'security_answer_3.regex' => 'Solo se permiten letras y espacios.',
        ]);

        $user = User::where('email', $this->email)->first();

        if (!$user) {
            $this->errorMessage = 'No se encontró la cuenta.';
            return;
        }

        // Comparar respuestas (case insensitive)
        $match1 = mb_strtolower(trim($this->security_answer_1)) === mb_strtolower(trim($user->security_answer_1));
        $match2 = mb_strtolower(trim($this->security_answer_2)) === mb_strtolower(trim($user->security_answer_2));
        $match3 = mb_strtolower(trim($this->security_answer_3)) === mb_strtolower(trim($user->security_answer_3));

        if ($match1 && $match2 && $match3) {
            // Descifrar la contraseña almacenada
            try {
                $this->recoveredPassword = Crypt::decryptString($user->plain_password_encrypted);
            } catch (\Exception $e) {
                $this->errorMessage = 'Error al recuperar la contraseña. Contacte al administrador.';
                return;
            }
            $this->showPasswordModal = true;
            $this->errorMessage = '';
        } else {
            $this->errorMessage = 'Las respuestas de seguridad no coinciden. Intenta nuevamente.';
        }
    }

    public function closeModal(): void
    {
        $this->showPasswordModal = false;
        $this->recoveredPassword = '';
        $this->redirect(route('login'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.recover-password')
            ->layout('components.layouts.auth');
    }
}
