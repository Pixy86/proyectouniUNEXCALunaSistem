<?php

namespace App\Actions\Fortify;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\-\']+$/u'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
            'telefono' => ['required', 'string', 'regex:/^[0-9]+$/', 'max:20'],
            'security_answer_1' => ['required', 'string', 'regex:/^[\pL\s]+$/u'],
            'security_answer_2' => ['required', 'string', 'regex:/^[\pL\s]+$/u'],
            'security_answer_3' => ['required', 'string', 'regex:/^[\pL\s]+$/u'],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.regex' => 'El nombre no puede contener números ni caracteres especiales.',
            'name.string' => 'El nombre debe ser un texto válido.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.regex'    => 'El teléfono solo debe contener números.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'security_answer_1.required' => 'La respuesta 1 es obligatoria.',
            'security_answer_1.regex' => 'La respuesta 1 solo debe contener letras.',
            'security_answer_2.required' => 'La respuesta 2 es obligatoria.',
            'security_answer_2.regex' => 'La respuesta 2 solo debe contener letras.',
            'security_answer_3.required' => 'La respuesta 3 es obligatoria.',
            'security_answer_3.regex' => 'La respuesta 3 solo debe contener letras.',
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'telefono' => $input['telefono'],
            'password' => $input['password'],
            'security_answer_1' => trim($input['security_answer_1']),
            'security_answer_2' => trim($input['security_answer_2']),
            'security_answer_3' => trim($input['security_answer_3']),
            'plain_password_encrypted' => Crypt::encryptString($input['password']),
        ]);

        AuditLog::registrar(
            accion: AuditLog::ACCION_CREATE,
            descripcion: "Se ha registrado un nuevo usuario: {$user->name} ({$user->email}) con preguntas de seguridad.",
            modelo: 'User',
            modeloId: $user->id
        );

        return $user;
    }
}
