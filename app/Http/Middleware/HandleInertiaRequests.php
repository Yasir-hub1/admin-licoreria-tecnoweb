<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        // Obtener contador de visitas de la página actual
        $rutaActual = $request->path();
        $contadorPaginaActual = \App\Models\Visita::where('ruta', $rutaActual)->count();
        $contadorPaginaActualHoy = \App\Models\Visita::where('ruta', $rutaActual)
            ->whereDate('created_at', today())
            ->count();

        // Obtener todos los contadores por página para el footer (opcional, para futuras funcionalidades)
        $contadoresPorPagina = \App\Models\Visita::select('ruta', 'nombre_pagina')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COUNT(CASE WHEN DATE(created_at) = CURRENT_DATE THEN 1 END) as hoy')
            ->groupBy('ruta', 'nombre_pagina')
            ->orderBy('ruta')
            ->get()
            ->map(function ($item) {
                return [
                    'ruta' => $item->ruta,
                    'nombre' => $item->nombre_pagina,
                    'total' => (int) $item->total,
                    'hoy' => (int) $item->hoy
                ];
            });

        // Obtener permisos del usuario si está autenticado
        $permisos = [];
        if ($user) {
            try {
                // Recargar siempre el rol y permisos para asegurar que estén actualizados
                // Esto es importante porque los permisos pueden cambiar mientras el usuario está autenticado
                $user->load(['rol.permisos']);
                
                // Verificar que el rol existe
                if (!$user->rol) {
                    Log::warning('Usuario ' . $user->id . ' no tiene rol asignado');
                    $permisos = [];
                } else {
                    // Asegurar que los permisos estén cargados
                    if (!$user->rol->relationLoaded('permisos')) {
                        $user->rol->load('permisos');
                    }
                    
                    // Usar el método del modelo que ya maneja la carga correctamente
                    $permisos = $user->getPermisosSlugs();
                    
                    Log::info('Permisos cargados para usuario ' . $user->id . ' (Rol: ' . $user->rol->nombre . '): ' . count($permisos) . ' permisos');
                    Log::debug('Permisos: ' . json_encode($permisos));
                }
            } catch (\Exception $e) {
                Log::error('Error cargando permisos del usuario ' . $user->id . ': ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());
                $permisos = [];
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'nombre' => $user->nombre,
                    'email' => $user->email ?? $user->correo,
                    'rol' => $user->rol ? [
                        'id' => $user->rol->id,
                        'nombre' => $user->rol->nombre,
                    ] : null,
                    'permisos' => $permisos,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'visitas' => [
                'pagina_actual' => [
                    'ruta' => $rutaActual,
                    'nombre' => $this->obtenerNombrePagina($rutaActual),
                    'total' => $contadorPaginaActual,
                    'hoy' => $contadorPaginaActualHoy,
                ],
                'todas_las_paginas' => $contadoresPorPagina,
            ],
        ];
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
