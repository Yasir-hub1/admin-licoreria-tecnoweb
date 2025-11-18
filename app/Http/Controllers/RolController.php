<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RolController extends Controller
{
    public function index()
    {
        $roles = Rol::withCount('usuarios')->paginate(15);
        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Roles/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:50|unique:rol',
            'descripcion' => 'nullable|string|max:200'
        ]);

        Rol::create($validated);

        return redirect('/admin/roles')
            ->with('success', 'Rol creado exitosamente');
    }

    public function show(string $id)
    {
        $rol = Rol::with('usuarios')->findOrFail($id);
        return Inertia::render('Admin/Roles/Show', [
            'rol' => $rol
        ]);
    }

    public function edit(string $id)
    {
        $rol = Rol::findOrFail($id);
        return Inertia::render('Admin/Roles/Edit', [
            'rol' => $rol
        ]);
    }

    public function update(Request $request, string $id)
    {
        $rol = Rol::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:50|unique:rol,nombre,' . $id,
            'descripcion' => 'nullable|string|max:200'
        ]);

        $rol->update($validated);

        return redirect('/admin/roles')
            ->with('success', 'Rol actualizado exitosamente');
    }

    public function destroy(string $id)
    {
        $rol = Rol::findOrFail($id);
        $rol->delete();

        return redirect('/admin/roles')
            ->with('success', 'Rol eliminado exitosamente');
    }
}
