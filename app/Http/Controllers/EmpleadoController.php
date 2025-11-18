<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmpleadoController extends Controller
{
    public function index()
    {
        $empleados = Empleado::with('usuario')->paginate(15);
        return Inertia::render('Admin/Empleados/Index', [
            'empleados' => $empleados
        ]);
    }

    public function create()
    {
        $usuarios = Usuario::whereDoesntHave('empleado')->get();
        return Inertia::render('Admin/Empleados/Create', [
            'usuarios' => $usuarios
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ci' => 'required|string|max:20|unique:empleado',
            'nombre' => 'required|string|max:100',
            'usuario_id' => 'required|exists:usuario,id'
        ]);

        Empleado::create($validated);

        return redirect('/admin/empleados')
            ->with('success', 'Empleado creado exitosamente');
    }

    public function show(string $id)
    {
        $empleado = Empleado::with(['usuario', 'ventas'])->findOrFail($id);
        return Inertia::render('Admin/Empleados/Show', [
            'empleado' => $empleado
        ]);
    }

    public function edit(string $id)
    {
        $empleado = Empleado::findOrFail($id);
        $usuarios = Usuario::whereDoesntHave('empleado')->orWhere('id', $empleado->usuario_id)->get();
        return Inertia::render('Admin/Empleados/Edit', [
            'empleado' => $empleado,
            'usuarios' => $usuarios
        ]);
    }

    public function update(Request $request, string $id)
    {
        $empleado = Empleado::findOrFail($id);

        $validated = $request->validate([
            'ci' => 'required|string|max:20|unique:empleado,ci,' . $id,
            'nombre' => 'required|string|max:100',
            'usuario_id' => 'required|exists:usuario,id'
        ]);

        $empleado->update($validated);

        return redirect('/admin/empleados')
            ->with('success', 'Empleado actualizado exitosamente');
    }

    public function destroy(string $id)
    {
        $empleado = Empleado::findOrFail($id);
        $empleado->delete();

        return redirect('/admin/empleados')
            ->with('success', 'Empleado eliminado exitosamente');
    }
}
