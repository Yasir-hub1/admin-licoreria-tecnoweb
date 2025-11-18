<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Credito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function orders()
    {
        $user = Auth::user();
        $cliente = $user->cliente;

        if (!$cliente) {
            return redirect('/shop')
                ->with('error', 'No se encontró información de cliente');
        }

        $ventas = Venta::where('cliente_id', $cliente->id)
            ->with(['detalles.producto'])
            ->orderBy('fecha', 'desc')
            ->paginate(10);

        return Inertia::render('Customer/MyOrders', [
            'ventas' => $ventas
        ]);
    }

    public function orderDetail($id)
    {
        $user = Auth::user();
        $cliente = $user->cliente;

        $venta = Venta::where('cliente_id', $cliente->id)
            ->where('id', $id)
            ->with(['detalles.producto', 'credito'])
            ->firstOrFail();

        return Inertia::render('Customer/OrderDetail', [
            'venta' => $venta
        ]);
    }

    public function credits()
    {
        $user = Auth::user();
        $cliente = $user->cliente;

        if (!$cliente) {
            return redirect('/shop')
                ->with('error', 'No se encontró información de cliente');
        }

        $creditos = Credito::whereHas('venta', function($q) use ($cliente) {
            $q->where('cliente_id', $cliente->id);
        })->with(['venta', 'pagos'])
        ->orderBy('fecha_inicio', 'desc')
        ->paginate(10);

        return Inertia::render('Customer/MyCredits', [
            'creditos' => $creditos
        ]);
    }

    public function creditDetail($id)
    {
        $user = Auth::user();
        $cliente = $user->cliente;

        $credito = Credito::whereHas('venta', function($q) use ($cliente) {
            $q->where('cliente_id', $cliente->id);
        })->with(['venta.detalles.producto', 'pagos'])
        ->findOrFail($id);

        return Inertia::render('Customer/CreditDetail', [
            'credito' => $credito
        ]);
    }

    public function payCuota($id, Request $request)
    {
        $request->validate([
            'monto' => 'required|numeric|min:0',
            'metodo' => 'required|string',
            'nro_transaccion' => 'nullable|string',
            'observacion' => 'nullable|string'
        ]);

        $user = Auth::user();
        $cliente = $user->cliente;

        $credito = Credito::whereHas('venta', function($q) use ($cliente) {
            $q->where('cliente_id', $cliente->id);
        })->findOrFail($id);

        try {
            $creditService = app(\App\Services\CreditService::class);
            $creditService->registerPayment(
                $credito->id,
                $request->monto,
                $request->metodo,
                $request->nro_transaccion,
                $request->observacion
            );

            return back()->with('success', 'Pago registrado exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function profile()
    {
        $user = Auth::user();
        $cliente = $user->cliente;

        return Inertia::render('Customer/Profile', [
            'user' => $user,
            'cliente' => $cliente
        ]);
    }
}
