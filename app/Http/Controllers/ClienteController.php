<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ClienteController extends BaseController
{
    public function index()
    {
        $this->verificarPermiso('clientes.listar');

        $clientes = Cliente::with(['usuario', 'verificador'])
            ->select('cliente.*')
            ->addSelect(DB::raw("CASE 
                WHEN estado_verificacion = 'aprobado' THEN 'Aprobado'
                WHEN estado_verificacion = 'en_revision' THEN 'En Revisión'
                WHEN estado_verificacion = 'rechazado' THEN 'Rechazado'
                WHEN estado_verificacion = 'pendiente' AND carnet_anverso IS NOT NULL AND carnet_reverso IS NOT NULL AND foto_luz IS NOT NULL AND foto_agua IS NOT NULL AND foto_garantia IS NOT NULL THEN 'Pendiente'
                WHEN carnet_anverso IS NOT NULL OR carnet_reverso IS NOT NULL OR foto_luz IS NOT NULL OR foto_agua IS NOT NULL OR foto_garantia IS NOT NULL THEN 'Documentos Incompletos'
                ELSE 'Sin Documentos'
            END as estado_verificacion_texto"))
            ->paginate(15);
        return Inertia::render('Admin/Clientes/Index', [
            'clientes' => $clientes
        ]);
    }

    public function create()
    {
        $this->verificarPermiso('clientes.crear');

        return Inertia::render('Admin/Clientes/Create');
    }

    public function store(Request $request)
    {
        $this->verificarPermiso('clientes.crear');
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
        $this->verificarPermiso('clientes.ver');

        $cliente = Cliente::with(['usuario', 'ventas'])->findOrFail($id);
        return Inertia::render('Admin/Clientes/Show', [
            'cliente' => $cliente
        ]);
    }

    public function edit(string $id)
    {
        $this->verificarPermiso('clientes.editar');

        $cliente = Cliente::findOrFail($id);
        return Inertia::render('Admin/Clientes/Edit', [
            'cliente' => $cliente
        ]);
    }

    public function update(Request $request, string $id)
    {
        $this->verificarPermiso('clientes.editar');

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
        $this->verificarPermiso('clientes.eliminar');

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

    public function verificarDocumentos()
    {
        $this->verificarPermiso('clientes.ver');

        $clientes = Cliente::whereIn('estado_verificacion', ['en_revision', 'pendiente'])
            ->where(function($q) {
                $q->whereNotNull('carnet_anverso')
                  ->whereNotNull('carnet_reverso')
                  ->whereNotNull('foto_luz')
                  ->whereNotNull('foto_agua')
                  ->whereNotNull('foto_garantia');
            })
            ->with(['usuario', 'verificador'])
            ->orderBy('estado_verificacion', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return Inertia::render('Admin/Clientes/VerificarDocumentos', [
            'clientes' => $clientes
        ]);
    }

    public function aprobarDocumentos(string $id, Request $request)
    {
        $this->verificarPermiso('clientes.editar');

        $cliente = Cliente::findOrFail($id);

        $validated = $request->validate([
            'limite_credito' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:500'
        ]);

        $cliente->update([
            'estado_verificacion' => 'aprobado',
            'credito_aprobado' => true,
            'limite_credito' => $validated['limite_credito'],
            'observaciones_verificacion' => $validated['observaciones'],
            'fecha_verificacion' => now(),
            'verificado_por' => auth()->id()
        ]);

        return back()->with('success', 'Documentos aprobados y crédito habilitado exitosamente');
    }

    public function rechazarDocumentos(string $id, Request $request)
    {
        $this->verificarPermiso('clientes.editar');

        $cliente = Cliente::findOrFail($id);

        $validated = $request->validate([
            'observaciones' => 'required|string|max:500'
        ]);

        $cliente->update([
            'estado_verificacion' => 'rechazado',
            'observaciones_verificacion' => $validated['observaciones'],
            'fecha_verificacion' => now(),
            'verificado_por' => auth()->id()
        ]);

        return back()->with('success', 'Documentos rechazados. El cliente ha sido notificado.');
    }
}
