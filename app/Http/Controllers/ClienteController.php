<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = Cliente::with('usuario')->paginate(15);
        return Inertia::render('Admin/Clientes/Index', [
            'clientes' => $clientes
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Clientes/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ci' => 'required|string|max:20|unique:cliente',
            'nombre' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:15',
            'direccion' => 'nullable|string|max:200',
            'estado' => 'required|in:A,I'
        ]);

        Cliente::create($validated);

        return redirect('/admin/clientes')
            ->with('success', 'Cliente creado exitosamente');
    }

    public function show(string $id)
    {
        $cliente = Cliente::with(['usuario', 'ventas'])->findOrFail($id);
        return Inertia::render('Admin/Clientes/Show', [
            'cliente' => $cliente
        ]);
    }

    public function edit(string $id)
    {
        $cliente = Cliente::findOrFail($id);
        return Inertia::render('Admin/Clientes/Edit', [
            'cliente' => $cliente
        ]);
    }

    public function update(Request $request, string $id)
    {
        $cliente = Cliente::findOrFail($id);

        $validated = $request->validate([
            'ci' => 'required|string|max:20|unique:cliente,ci,' . $id,
            'nombre' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:15',
            'direccion' => 'nullable|string|max:200',
            'estado' => 'required|in:A,I'
        ]);

        $cliente->update($validated);

        return redirect('/admin/clientes')
            ->with('success', 'Cliente actualizado exitosamente');
    }

    public function destroy(string $id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->delete();

        return redirect('/admin/clientes')
            ->with('success', 'Cliente eliminado exitosamente');
    }

    public function toggleCredit(string $id, Request $request)
    {
        $cliente = Cliente::findOrFail($id);

        $validated = $request->validate([
            'credito_aprobado' => 'required|boolean',
            'limite_credito' => 'required_if:credito_aprobado,true|numeric|min:0'
        ]);

        $cliente->update([
            'credito_aprobado' => $validated['credito_aprobado'],
            'limite_credito' => $validated['credito_aprobado'] ? ($validated['limite_credito'] ?? 0) : 0
        ]);

        $mensaje = $validated['credito_aprobado']
            ? "Crédito aprobado con límite de {$cliente->limite_credito}"
            : 'Crédito desaprobado';

        return back()->with('success', $mensaje);
    }
}
