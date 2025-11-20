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
                // Asegurar que el rol esté cargado con eager loading
                $user->loadMissing('rol.permisos');

                // Debug: Log para verificar que el rol se cargó
                Log::info('Usuario ID: ' . $user->id . ', Rol ID: ' . ($user->rol ? $user->rol->id : 'null'));

                // Si el usuario tiene rol, obtener los slugs de los permisos
                if ($user->rol) {
                    // Cargar permisos si no están cargados
                    if (!$user->rol->relationLoaded('permisos')) {
                        $user->rol->load('permisos');
                    }

                    if ($user->rol->permisos) {
                        $permisos = $user->rol->permisos->pluck('slug')->toArray();
                        Log::info('Permisos cargados para usuario ' . $user->id . ': ' . json_encode($permisos));
                    } else {
                        Log::warning('Usuario ' . $user->id . ' tiene rol pero no tiene permisos cargados');
                    }
                } else {
                    Log::warning('Usuario ' . $user->id . ' no tiene rol asignado');
                }
            } catch (\Exception $e) {
                // En caso de error, intentar método alternativo
                Log::error('Error cargando permisos del usuario ' . $user->id . ': ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());
                if ($user->rol) {
                    $permisos = $user->getPermisosSlugs();
                    Log::info('Permisos obtenidos por método alternativo: ' . json_encode($permisos));
                }
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
