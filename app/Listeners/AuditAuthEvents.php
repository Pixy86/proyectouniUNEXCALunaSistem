<?php

namespace App\Listeners;

use App\Models\AuditLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class AuditAuthEvents
{
    /**
     * Handle user login events.
     */
    public function handleLogin(Login $event): void
    {
        // Registra en auditoría cada inicio de sesión exitoso
        AuditLog::registrar(
            accion: AuditLog::ACCION_LOGIN,
            descripcion: "Usuario {$event->user->name} inició sesión exitosamente",
            modelo: 'Autenticación'
        );
    }

    /**
     * Handle user logout events.
     */
    public function handleLogout(Logout $event): void
    {
        if ($event->user) {
            // Registra en auditoría cada cierre de sesión
            AuditLog::registrar(
                accion: AuditLog::ACCION_LOGOUT,
                descripcion: "Usuario {$event->user->name} cerró sesión",
                modelo: 'Autenticación'
            );
        }
    }
}
