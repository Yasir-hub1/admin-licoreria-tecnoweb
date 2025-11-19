<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BaseController extends Controller
{
    /**
     * Verificar si el usuario tiene un permiso específico
     */
    protected function verificarPermiso($permiso)
    {
        /** @var \App\Models\Usuario|null $user */
        $user = Auth::user();

        if (!$user) {
            abort(403, 'No autenticado');
        }

        // Propietario tiene todos los permisos
        if ($user->isPropietario()) {
            return true;
        }

        if (!$user->tienePermiso($permiso)) {
            abort(403, 'No tienes permisos para realizar esta acción');
        }

        return true;
    }
}

