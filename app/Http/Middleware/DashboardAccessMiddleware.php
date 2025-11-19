<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DashboardAccessMiddleware
{
    /**
     * Handle an incoming request.
     * Solo permite acceso al dashboard a propietario y empleado
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        /** @var \App\Models\Usuario $user */
        $user = Auth::user();

        // Solo propietario y empleado pueden acceder al dashboard
        if (!$user->isPropietario() && !$user->isEmpleado()) {
            // Redirigir a bienvenida si tiene acceso admin pero no es propietario/empleado
            if ($user->tieneAccesoAdmin()) {
                return redirect('/admin/bienvenida');
            }
            abort(403, 'No tienes permisos para acceder al dashboard');
        }

        return $next($request);
    }
}

