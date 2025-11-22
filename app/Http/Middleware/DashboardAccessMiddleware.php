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

        // Asegurar que el rol y permisos estén cargados antes de verificar
        if (!$user->relationLoaded('rol')) {
            $user->load('rol');
        }
        
        if ($user->rol && !$user->rol->relationLoaded('permisos')) {
            $user->rol->load('permisos');
        }

        // Verificar si puede acceder al dashboard
        // Puede acceder si es propietario, empleado, o tiene el permiso dashboard.ver
        if (!$user->puedeAccederDashboard() && !$user->tienePermiso('dashboard.ver')) {
            // Redirigir a bienvenida si tiene acceso admin pero no puede acceder al dashboard
            if ($user->tieneAccesoAdmin()) {
                return redirect('/admin/bienvenida');
            }
            abort(403, 'No tienes permisos para acceder al dashboard');
        }

        return $next($request);
    }
}

