<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProveedorController extends Controller
{
    public function index()
    {
        $proveedores = Proveedor::withCount('compras')->paginate(15);
        return Inertia::render('Admin/Proveedores/Index', [
            'proveedores' => $proveedores
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Proveedores/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:50',
            'telefono' => 'nullable|string|max:20',
            'nit' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:20',
            'direccion' => 'nullable|string|max:20'
        ]);

        Proveedor::create($validated);

        return redirect('/admin/proveedores')
            ->with('success', 'Proveedor creado exitosamente');
    }

    public function show(string $id)
    {
        $proveedor = Proveedor::with('compras')->findOrFail($id);
        return Inertia::render('Admin/Proveedores/Show', [
            'proveedor' => $proveedor
        ]);
    }

    public function edit(string $id)
    {
        $proveedor = Proveedor::findOrFail($id);
        return Inertia::render('Admin/Proveedores/Edit', [
            'proveedor' => $proveedor
        ]);
    }

    public function update(Request $request, string $id)
    {
        $proveedor = Proveedor::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:50',
            'telefono' => 'nullable|string|max:20',
            'nit' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:20',
            'direccion' => 'nullable|string|max:20'
        ]);

        $proveedor->update($validated);

        return redirect('/admin/proveedores')
            ->with('success', 'Proveedor actualizado exitosamente');
    }

    public function destroy(string $id)
    {
        $proveedor = Proveedor::findOrFail($id);
        $proveedor->delete();

        return redirect('/admin/proveedores')
            ->with('success', 'Proveedor eliminado exitosamente');
    }
}
