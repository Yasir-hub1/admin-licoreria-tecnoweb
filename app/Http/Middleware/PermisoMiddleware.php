<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermisoMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permiso): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        /** @var \App\Models\Usuario $user */
        $user = auth()->user();

        // Si es propietario, tiene todos los permisos
        if ($user->isPropietario()) {
            return $next($request);
        }

        // Verificar si el usuario tiene el permiso
        if (!$user->tienePermiso($permiso)) {
            abort(403, 'No tienes permisos para realizar esta acción');
        }

        return $next($request);
    }
}
