<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Permiso;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RolController extends Controller
{
    public function index()
    {
        $roles = Rol::withCount(['usuarios', 'permisos'])->orderBy('id', 'desc')->paginate(15);
        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles
        ]);
    }

    public function create()
    {
        $permisos = Permiso::orderBy('modulo')->orderBy('nombre')->get()->groupBy('modulo');
        return Inertia::render('Admin/Roles/Create', [
            'permisos' => $permisos
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:50|unique:rol',
            'descripcion' => 'nullable|string|max:200',
            'permisos' => 'nullable|array',
            'permisos.*' => 'exists:permiso,id'
        ]);

        $rol = Rol::create([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null
        ]);

        // Sincronizar permisos si se proporcionaron
        if (isset($validated['permisos'])) {
            $rol->sincronizarPermisos($validated['permisos']);
        }

        return redirect('/admin/roles')
            ->with('success', 'Rol creado exitosamente');
    }

    public function show(string $id)
    {
        $rol = Rol::with(['usuarios', 'permisos'])->findOrFail($id);
        return Inertia::render('Admin/Roles/Show', [
            'rol' => $rol
        ]);
    }

    public function edit(string $id)
    {
        $rol = Rol::with('permisos')->findOrFail($id);
        $permisos = Permiso::orderBy('modulo')->orderBy('nombre')->get()->groupBy('modulo');

        return Inertia::render('Admin/Roles/Edit', [
            'rol' => $rol,
            'permisos' => $permisos,
            'permisosSeleccionados' => $rol->permisos->pluck('id')->toArray()
        ]);
    }

    public function update(Request $request, string $id)
    {
        $rol = Rol::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:50|unique:rol,nombre,' . $id,
            'descripcion' => 'nullable|string|max:200',
            'permisos' => 'nullable|array',
            'permisos.*' => 'exists:permiso,id'
        ]);

        $rol->update([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null
        ]);

        // Sincronizar permisos
        $permisosIds = $validated['permisos'] ?? [];
        $rol->sincronizarPermisos($permisosIds);

        // Nota: Los usuarios autenticados necesitarán cerrar sesión y volver a iniciar
        // para obtener los nuevos permisos, o podemos forzar una recarga en el siguiente request
        // El middleware HandleInertiaRequests ya recarga los permisos en cada request

        return redirect('/admin/roles')
            ->with('success', 'Rol actualizado exitosamente. Los usuarios con este rol verán los cambios en su próxima solicitud.');
    }

    public function destroy(string $id)
    {
        $rol = Rol::findOrFail($id);

        // Verificar si hay usuarios con este rol
        if ($rol->usuarios()->count() > 0) {
            return redirect('/admin/roles')
                ->with('error', 'No se puede eliminar el rol porque tiene usuarios asignados');
        }

        $rol->delete();

        return redirect('/admin/roles')
            ->with('success', 'Rol eliminado exitosamente');
    }
}
