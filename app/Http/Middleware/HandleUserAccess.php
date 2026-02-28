<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Symfony\Component\HttpFoundation\Response;

class HandleUserAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // 1. Verificar si el usuario está activo (estado)
        // Se asume que 1/true es activo y 0/false es inactivo.
        if (!$user->estado) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Tu cuenta ha sido desactivada. Por favor, contacta al administrador.');
        }

        // 2. Verificar roles (si se especificaron en el middleware de la ruta)
        if (!empty($roles) && !in_array($user->role, $roles)) {
            
            Notification::make()
                ->title('Acceso Denegado')
                ->body('Tus permisos actuales no te permiten acceder a esta sección.')
                ->danger()
                ->send();

            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
