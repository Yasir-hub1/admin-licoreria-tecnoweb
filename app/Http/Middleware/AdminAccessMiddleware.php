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

        // Asegurar que el rol y permisos estén cargados antes de verificar
        if (!$user->relationLoaded('rol')) {
            $user->load('rol');
        }
        
        if ($user->rol && !$user->rol->relationLoaded('permisos')) {
            $user->rol->load('permisos');
        }

        // Verificar si tiene acceso al admin
        $tieneAcceso = $user->tieneAccesoAdmin();
        
        \Illuminate\Support\Facades\Log::info("[AdminAccessMiddleware] Verificando acceso admin para usuario {$user->id} (Rol: " . ($user->rol ? $user->rol->nombre : 'sin rol') . ")");
        \Illuminate\Support\Facades\Log::info("[AdminAccessMiddleware] isPropietario: " . ($user->isPropietario() ? 'SI' : 'NO'));
        \Illuminate\Support\Facades\Log::info("[AdminAccessMiddleware] isEmpleado: " . ($user->isEmpleado() ? 'SI' : 'NO'));
        \Illuminate\Support\Facades\Log::info("[AdminAccessMiddleware] tieneAccesoAdmin: " . ($tieneAcceso ? 'SI' : 'NO'));
        \Illuminate\Support\Facades\Log::debug("[AdminAccessMiddleware] Permisos disponibles: " . json_encode($user->getPermisosSlugs()));
        
        if (!$tieneAcceso) {
            \Illuminate\Support\Facades\Log::warning("[AdminAccessMiddleware] Usuario {$user->id} (Rol: " . ($user->rol ? $user->rol->nombre : 'sin rol') . ") NO tiene acceso admin");
            abort(403, 'No tienes permisos para acceder al panel administrativo');
        }

        return $next($request);
    }
}

