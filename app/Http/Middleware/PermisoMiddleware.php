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

        // Asegurar que el rol y permisos estén cargados antes de verificar
        if (!$user->relationLoaded('rol')) {
            $user->load('rol');
        }
        
        if ($user->rol && !$user->rol->relationLoaded('permisos')) {
            $user->rol->load('permisos');
        }

        // Siempre verificar el permiso asignado, incluso para propietarios
        $tienePermiso = $user->tienePermiso($permiso);
        
        \Illuminate\Support\Facades\Log::info("[PermisoMiddleware] Verificando permiso: {$permiso} para usuario {$user->id} (Rol: " . ($user->rol ? $user->rol->nombre : 'sin rol') . ")");
        \Illuminate\Support\Facades\Log::info("[PermisoMiddleware] Resultado: " . ($tienePermiso ? 'SI tiene permiso' : 'NO tiene permiso'));
        \Illuminate\Support\Facades\Log::debug("[PermisoMiddleware] Permisos disponibles: " . json_encode($user->getPermisosSlugs()));
        
        if (!$tienePermiso) {
            \Illuminate\Support\Facades\Log::warning("[PermisoMiddleware] Usuario {$user->id} (Rol: " . ($user->rol ? $user->rol->nombre : 'sin rol') . ") NO tiene permiso: {$permiso}");
            abort(403, 'No tienes permisos para realizar esta acción');
        }

        return $next($request);
    }
}
