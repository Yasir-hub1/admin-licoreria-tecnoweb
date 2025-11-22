<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();
        
        // Asegurar que el rol esté cargado
        if (!$user->relationLoaded('rol')) {
            $user->load('rol');
        }

        \Illuminate\Support\Facades\Log::info("[RoleMiddleware] Verificando roles: " . implode(', ', $roles) . " para usuario {$user->id} (Rol: " . ($user->rol ? $user->rol->nombre : 'sin rol') . ")");
        
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                \Illuminate\Support\Facades\Log::info("[RoleMiddleware] Usuario {$user->id} tiene rol: {$role}");
                return $next($request);
            }
        }

        \Illuminate\Support\Facades\Log::warning("[RoleMiddleware] Usuario {$user->id} (Rol: " . ($user->rol ? $user->rol->nombre : 'sin rol') . ") NO tiene ninguno de los roles requeridos: " . implode(', ', $roles));
        abort(403, 'No tienes permisos para acceder a esta página');
    }
}
