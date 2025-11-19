<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAccessMiddleware
{
    /**
     * Handle an incoming request.
     * Permite acceso si el usuario es propietario, empleado, o tiene permisos administrativos
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

        // Verificar si tiene acceso al admin
        if (!$user->tieneAccesoAdmin()) {
            abort(403, 'No tienes permisos para acceder al panel administrativo');
        }

        return $next($request);
    }
}

