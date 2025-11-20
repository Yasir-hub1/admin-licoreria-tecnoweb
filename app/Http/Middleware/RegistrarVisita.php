<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Visita;

class RegistrarVisita
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Solo registrar visitas en rutas admin
        if ($request->is('admin/*') && $request->method() === 'GET') {
            try {
                $ruta = $request->path();
                $nombrePagina = $this->obtenerNombrePagina($ruta);

                Visita::create([
                    'ruta' => $ruta,
                    'nombre_pagina' => $nombrePagina,
                    'usuario_id' => Auth::id(),
                    'ip' => $request->ip(),
                    'user_agent' => substr($request->userAgent(), 0, 500)
                ]);
            } catch (\Exception $e) {
                // No interrumpir la petición si hay error al registrar visita
                Log::warning('Error al registrar visita: ' . $e->getMessage());
            }
        }

        return $next($request);
    }

    /**
     * Obtener nombre descriptivo de la página
     */
    private function obtenerNombrePagina(string $ruta): string
    {
        $nombres = [
            'admin/dashboard' => 'Dashboard',
            'admin/productos' => 'Productos',
            'admin/productos/create' => 'Nuevo Producto',
            'admin/categorias' => 'Categorías',
            'admin/clientes' => 'Clientes',
            'admin/ventas' => 'Ventas',
            'admin/compras' => 'Compras',
            'admin/proveedores' => 'Proveedores',
            'admin/inventario' => 'Inventario',
            'admin/creditos' => 'Créditos',
            'admin/usuarios' => 'Usuarios',
            'admin/roles' => 'Roles',
            'admin/contadores' => 'Contadores',
        ];

        // Buscar coincidencia exacta
        if (isset($nombres[$ruta])) {
            return $nombres[$ruta];
        }

        // Buscar coincidencia parcial
        foreach ($nombres as $key => $nombre) {
            if (str_contains($ruta, $key)) {
                return $nombre;
            }
        }

        // Si no hay coincidencia, extraer el nombre de la ruta
        $partes = explode('/', $ruta);
        return ucfirst(end($partes));
    }
}
