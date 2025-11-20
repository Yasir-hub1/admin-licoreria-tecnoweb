<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class InventarioController extends Controller
{
    public function index()
    {
        $inventoryService = app(\App\Services\InventoryService::class);
        $productos = Producto::with('categoria')->get()->map(function($producto) use ($inventoryService) {
            $producto->stock_actual = $inventoryService->calcularStock($producto->id);
            return $producto;
        });

        return Inertia::render('Admin/Inventario/Index', [
            'productos' => $productos
        ]);
    }

    public function movimientos()
    {
        $movimientos = Inventario::with(['usuario', 'producto', 'detalleCompra', 'detalleVenta'])
            ->orderBy('id', 'desc')
            ->paginate(20);
        return Inertia::render('Admin/Inventario/Movimientos', [
            'movimientos' => $movimientos
        ]);
    }

    public function ajuste(Request $request)
    {
        $validated = $request->validate([
            'producto_id' => 'required|exists:producto,id',
            'tipo_movimiento' => 'required|in:INGRESO,SALIDA',
            'cantidad' => 'required|numeric|min:1',
            'glosa' => 'required|string|max:200'
        ]);

        try {
            // Verificar autenticación
            if (!\Illuminate\Support\Facades\Auth::check()) {
                return back()->with('error', 'Debes estar autenticado para realizar movimientos de inventario');
            }

            /** @var \App\Models\Usuario $user */
            $user = \Illuminate\Support\Facades\Auth::user();

            if (!$user || !$user->id) {
                return back()->with('error', 'Error de autenticación. Por favor, inicia sesión nuevamente.');
            }

            $inventoryService = app(\App\Services\InventoryService::class);

            // Validar que las salidas no excedan el stock disponible
            if ($validated['tipo_movimiento'] === 'SALIDA') {
                $stockActual = $inventoryService->calcularStock($validated['producto_id']);
                if ($validated['cantidad'] > $stockActual) {
                    return back()->with('error', "No se puede registrar una salida de {$validated['cantidad']} unidades. Stock disponible: {$stockActual}");
                }
            }

            // Registrar el movimiento directamente
            $movimiento = $inventoryService->registrarMovimiento([
                'tipo_movimiento' => $validated['tipo_movimiento'],
                'cantidad' => $validated['cantidad'],
                'fecha' => now(),
                'glosa' => $validated['glosa'],
                'producto_id' => $validated['producto_id']
            ]);

            if ($movimiento && $movimiento->id) {
                $tipoTexto = $validated['tipo_movimiento'] === 'INGRESO' ? 'Ingreso' : 'Salida';
                return back()->with('success', "{$tipoTexto} de inventario registrado exitosamente. Stock actualizado.");
            } else {
                return back()->with('error', 'No se pudo registrar el movimiento de inventario');
            }
        } catch (\Exception $e) {
            Log::error('Error en movimiento de inventario: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return back()->with('error', 'Error al registrar movimiento: ' . $e->getMessage());
        }
    }

    public function kardex($producto_id)
    {
        $producto = Producto::findOrFail($producto_id);
        $inventoryService = app(\App\Services\InventoryService::class);
        $movimientos = $inventoryService->getKardex($producto_id);

        return Inertia::render('Admin/Inventario/Kardex', [
            'producto' => $producto,
            'movimientos' => $movimientos
        ]);
    }
}
