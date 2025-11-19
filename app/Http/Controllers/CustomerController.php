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

    public function verificarCredito()
    {
        $user = Auth::user();
        $cliente = $user->cliente;

        if (!$cliente) {
            return redirect('/shop')
                ->with('error', 'No se encontró información de cliente');
        }

        return Inertia::render('Customer/VerificarCredito', [
            'cliente' => $cliente
        ]);
    }

    public function subirDocumentos(Request $request)
    {
        $user = Auth::user();
        $cliente = $user->cliente;

        if (!$cliente) {
            return redirect('/shop')
                ->with('error', 'No se encontró información de cliente');
        }

        $request->validate([
            'carnet_anverso' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
            'carnet_reverso' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
            'foto_luz' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
            'foto_agua' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
            'foto_garantia' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
        ]);

        $updateData = [];

        // Guardar archivos si se subieron
        if ($request->hasFile('carnet_anverso')) {
            $path = $request->file('carnet_anverso')->store('documentos/clientes', 'public');
            $updateData['carnet_anverso'] = $path;
        }

        if ($request->hasFile('carnet_reverso')) {
            $path = $request->file('carnet_reverso')->store('documentos/clientes', 'public');
            $updateData['carnet_reverso'] = $path;
        }

        if ($request->hasFile('foto_luz')) {
            $path = $request->file('foto_luz')->store('documentos/clientes', 'public');
            $updateData['foto_luz'] = $path;
        }

        if ($request->hasFile('foto_agua')) {
            $path = $request->file('foto_agua')->store('documentos/clientes', 'public');
            $updateData['foto_agua'] = $path;
        }

        if ($request->hasFile('foto_garantia')) {
            $path = $request->file('foto_garantia')->store('documentos/clientes', 'public');
            $updateData['foto_garantia'] = $path;
        }

        $cliente->update($updateData);
        
        // Recargar el modelo para verificar documentos actualizados
        $cliente->refresh();
        
        // Si todos los documentos están completos y el estado es pendiente o null, cambiar a en_revision
        // Solo cambiar si no está ya en revisión, aprobado o rechazado
        if ($cliente->tieneDocumentosCompletos() && 
            (!$cliente->estado_verificacion || $cliente->estado_verificacion === 'pendiente')) {
            $cliente->update(['estado_verificacion' => 'en_revision']);
        }
        
        // Si se actualizaron documentos y estaba rechazado, volver a pendiente
        if (!empty($updateData) && $cliente->estado_verificacion === 'rechazado') {
            $cliente->update([
                'estado_verificacion' => 'pendiente',
                'observaciones_verificacion' => null
            ]);
        }

        return back()->with('success', 'Documentos subidos exitosamente. Serán revisados por un administrador.');
    }
}
