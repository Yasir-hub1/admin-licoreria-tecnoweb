<?php

namespace App\Http\Controllers;

use App\Models\Credito;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CreditoController extends Controller
{
    public function index()
    {
        $creditos = Credito::with(['venta.cliente'])->orderBy('fecha_inicio', 'desc')->paginate(15);
        return Inertia::render('Admin/Creditos/Index', [
            'creditos' => $creditos
        ]);
    }

    public function create()
    {
        $ventas = \App\Models\Venta::where('tipo', 'credito')
            ->whereDoesntHave('credito')
            ->with('cliente')
            ->get();
        return Inertia::render('Admin/Creditos/Create', [
            'ventas' => $ventas
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'venta_id' => 'required|exists:venta,id',
            'monto_total' => 'required|numeric|min:0',
            'numero_cuotas' => 'required|integer|min:2|max:12',
            'fecha_inicio' => 'required|date',
            'estado' => 'required|in:activo,pagado,mora'
        ]);

        try {
            $venta = \App\Models\Venta::findOrFail($validated['venta_id']);

            // Verificar que la venta no tenga crédito ya
            if ($venta->credito) {
                return back()->withInput()->with('error', 'Esta venta ya tiene un crédito asociado');
            }

            $creditService = app(\App\Services\CreditService::class);
            $credito = $creditService->createCredit($venta, $validated['numero_cuotas']);

            // Actualizar monto y estado si es necesario
            if ($validated['monto_total'] != $credito->monto_total) {
                $credito->update([
                    'monto_total' => $validated['monto_total'],
                    'saldo' => $validated['monto_total']
                ]);
            }

            $credito->update([
                'fecha_inicio' => $validated['fecha_inicio'],
                'estado' => $validated['estado']
            ]);

            return redirect('/admin/creditos')
                ->with('success', 'Crédito creado exitosamente');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error al crear crédito: ' . $e->getMessage());
        }
    }

    public function show(string $id)
    {
        $credito = Credito::with(['venta.cliente'])->findOrFail($id);

        // Cargar pagos ordenados por numero_cuota (campo directo y confiable)
        $pagos = \App\Models\Pagos::where('credito_id', $credito->id)
            ->orderBy('numero_cuota', 'asc')
            ->orderBy('id', 'asc') // Orden secundario por ID para casos donde numero_cuota sea null
            ->get();

        $credito->setRelation('pagos', $pagos);

        return Inertia::render('Admin/Creditos/Show', [
            'credito' => $credito
        ]);
    }

    public function edit(string $id)
    {
        $credito = Credito::findOrFail($id);
        return Inertia::render('Admin/Creditos/Edit', [
            'credito' => $credito
        ]);
    }

    public function update(Request $request, string $id)
    {
        $credito = Credito::findOrFail($id);

        $validated = $request->validate([
            'monto_total' => 'required|numeric|min:0',
            'numero_cuotas' => 'required|integer|min:2|max:12',
            'fecha_inicio' => 'required|date',
            'estado' => 'required|in:activo,pagado,mora'
        ]);

        try {
            $credito->update($validated);

            return redirect('/admin/creditos')
                ->with('success', 'Crédito actualizado exitosamente');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error al actualizar crédito: ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        $credito = Credito::findOrFail($id);
        $credito->delete();

        return redirect('/admin/creditos')
            ->with('success', 'Crédito eliminado exitosamente');
    }

    public function registrarPago(string $id, Request $request)
    {
        $validated = $request->validate([
            'monto' => 'required|numeric|min:0',
            'metodo' => 'required|in:efectivo,qr',
            'nro_transaccion' => 'nullable|string',
            'observacion' => 'nullable|string'
        ]);

        try {
            $creditService = app(\App\Services\CreditService::class);
            $creditService->registerPayment(
                $id,
                $validated['monto'],
                $validated['metodo'],
                $validated['nro_transaccion'] ?? null,
                $validated['observacion'] ?? null
            );

            return back()->with('success', 'Pago registrado exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
