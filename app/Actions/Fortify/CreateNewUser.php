<?php

namespace App\Actions\Fortify;

use App\Models\AuditLog;
use App\Models\User;
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
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
        ]);

        AuditLog::registrar(
            accion: AuditLog::ACCION_CREATE,
            descripcion: "Se ha registrado un nuevo usuario: {$user->name} ({$user->email})",
            modelo: 'User',
            modeloId: $user->id
        );

        return $user;
    }
}
