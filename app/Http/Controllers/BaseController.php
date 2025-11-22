<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BaseController extends Controller
{
    /**
     * Verificar si el usuario tiene un permiso específico
     * Ahora siempre verifica los permisos asignados, incluso para propietarios
     */
    protected function verificarPermiso($permiso)
    {
        /** @var \App\Models\Usuario|null $user */
        $user = Auth::user();

        if (!$user) {
            abort(403, 'No autenticado');
        }

        // Asegurar que el rol y permisos estén cargados antes de verificar
        if (!$user->relationLoaded('rol')) {
            $user->load('rol');
        }
        
        if ($user->rol && !$user->rol->relationLoaded('permisos')) {
            $user->rol->load('permisos');
        }

        // Siempre verificar el permiso asignado, incluso para propietarios
        if (!$user->tienePermiso($permiso)) {
            abort(403, 'No tienes permisos para realizar esta acción');
        }

        return true;
    }
}

