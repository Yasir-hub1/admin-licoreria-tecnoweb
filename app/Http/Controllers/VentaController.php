<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VentaController extends Controller
{
    public function index()
    {
        $ventas = Venta::with(['cliente', 'usuario', 'credito'])->orderBy('id', 'desc')->paginate(15);

        // Calcular estado real basado en saldo pendiente
        foreach ($ventas as $venta) {
            if ($venta->tipo === 'credito' && $venta->saldo > 0) {
                $venta->estado_real = 'en_credito';
            } else {
                $venta->estado_real = $venta->estado;
            }
        }

        return Inertia::render('Admin/Ventas/Index', [
            'ventas' => $ventas
        ]);
    }

    public function create()
    {
        $clientes = Cliente::where('estado', 'A')->get();
        $productos = \App\Models\Producto::with('categoria')->get();

        // Obtener el usuario autenticado
        $usuario = \Illuminate\Support\Facades\Auth::user();
        $usuarioId = $usuario ? $usuario->id : null;

        // Calcular stock para cada producto
        $inventoryService = app(\App\Services\InventoryService::class);
        $stocks = [];
        foreach ($productos as $producto) {
            $stocks[$producto->id] = $inventoryService->calcularStock($producto->id);
        }

        return Inertia::render('Admin/Ventas/Create', [
            'clientes' => $clientes,
            'usuario_id' => $usuarioId,
            'productos' => $productos,
            'stocks' => $stocks
        ]);
    }

    public function buscarClientes(Request $request)
    {
        $query = $request->get('q', '');

        $clientes = Cliente::where('estado', 'A')
            ->where(function($q) use ($query) {
                $q->where('nombre', 'ilike', "%{$query}%")
                  ->orWhere('ci', 'ilike', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'nombre', 'ci', 'telefono']);

        return response()->json($clientes);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:cliente,id',
            'usuario_id' => 'required|exists:usuario,id',
            'fecha' => 'required|date',
            'tipo' => 'required|in:contado,credito',
            'numero_cuotas' => 'nullable|integer|min:2|max:12',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:producto,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        try {
            $inventoryService = app(\App\Services\InventoryService::class);
            $checkoutService = app(\App\Services\CheckoutService::class);

            // Verificar stock
            foreach ($validated['detalles'] as $detalle) {
                $stockActual = $inventoryService->calcularStock($detalle['producto_id']);
                if ($stockActual < $detalle['cantidad']) {
                    $producto = \App\Models\Producto::find($detalle['producto_id']);
                    return back()->withInput()->with('error',
                        "El producto {$producto->nombre} no tiene stock suficiente. Disponible: {$stockActual} unidades");
                }
            }

            // Calcular total
            $total = 0;
            foreach ($validated['detalles'] as $detalle) {
                $total += $detalle['cantidad'] * $detalle['precio_unitario'];
            }

            // Generar número de venta usando CounterService
            $counterService = app(\App\Services\CounterService::class);
            $nroVenta = $counterService->obtenerSiguienteVenta();

            $cliente = Cliente::findOrFail($validated['cliente_id']);

            \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $total, $nroVenta, $cliente, $inventoryService) {
                // Crear venta
                $estadoVenta = $validated['tipo'] === 'credito' ? 'pendiente' : 'completado';
                $venta = Venta::create([
                    'nro_venta' => $nroVenta,
                    'fecha' => $validated['fecha'],
                    'tipo' => $validated['tipo'],
                    'monto_total' => $total,
                    'saldo' => $validated['tipo'] === 'credito' ? $total : 0,
                    'numero_cuotas' => $validated['tipo'] === 'credito' ? ($validated['numero_cuotas'] ?? 2) : 0,
                    'estado' => $estadoVenta,
                    'cliente_id' => $validated['cliente_id'],
                    'usuario_id' => $validated['usuario_id']
                ]);

                // Crear detalles
                foreach ($validated['detalles'] as $detalle) {
                    $detalleVenta = \App\Models\DetalleVenta::create([
                        'venta_id' => $venta->id,
                        'producto_id' => $detalle['producto_id'],
                        'cantidad' => $detalle['cantidad'],
                        'precio_unitario' => $detalle['precio_unitario'],
                        'subtotal' => $detalle['cantidad'] * $detalle['precio_unitario']
                    ]);

                    // Registrar movimiento de inventario
                    $inventoryService->registrarMovimiento([
                        'tipo_movimiento' => 'SALIDA',
                        'cantidad' => $detalle['cantidad'],
                        'fecha' => $validated['fecha'],
                        'glosa' => "Venta #{$nroVenta} - Cliente {$cliente->nombre}",
                        'producto_id' => $detalle['producto_id'],
                        'detalle_venta_id' => $detalleVenta->id
                    ]);
                }

                // Si es crédito, crear crédito
                if ($validated['tipo'] === 'credito') {
                    $creditService = app(\App\Services\CreditService::class);
                    $creditService->createCredit($venta, $validated['numero_cuotas'] ?? 2);
                }
            });

            return redirect('/admin/ventas')
                ->with('success', 'Venta creada exitosamente');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error al crear venta: ' . $e->getMessage());
        }
    }

    public function show(string $id)
    {
        $venta = Venta::with([
            'cliente',
            'usuario',
            'detalles.producto',
            'credito'
        ])->findOrFail($id);

        // Calcular total desde los detalles (más preciso)
        $totalCalculado = $venta->detalles->sum('subtotal');
        if ($totalCalculado > 0 && abs($totalCalculado - $venta->monto_total) > 0.01) {
            // Si hay diferencia, actualizar el monto_total
            $venta->monto_total = $totalCalculado;
        }

        // Si tiene crédito, calcular información desde el crédito (fuente de verdad)
        if ($venta->credito) {
            // Cargar pagos ordenados por numero_cuota
            $pagos = \App\Models\Pagos::where('credito_id', $venta->credito->id)
                ->orderBy('numero_cuota', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $venta->credito->setRelation('pagos', $pagos);

            // Calcular pagado sumando los pagos reales (con fecha_pago)
            $pagadoReal = $pagos->whereNotNull('fecha_pago')->sum('monto');

            // El saldo debe venir del crédito (fuente de verdad)
            $saldoCredito = $venta->credito->saldo;

            // Sincronizar venta.saldo con credito.saldo si es necesario
            if (abs($venta->saldo - $saldoCredito) > 0.01) {
                $venta->saldo = $saldoCredito;
            }

            // Calcular información de pago desde el crédito
            $venta->pagado = $pagadoReal;
            $venta->saldo = $saldoCredito;
            $venta->porcentaje_pagado = $venta->monto_total > 0 ? ($venta->pagado / $venta->monto_total) * 100 : 0;

            // Actualizar estado real basado en el saldo del crédito
            if ($saldoCredito > 0) {
                $venta->estado_real = 'en_credito';
            } else {
                $venta->estado_real = 'completado';
            }
        } else {
            // Para ventas de contado
            $venta->pagado = $venta->monto_total;
            $venta->saldo = 0;
            $venta->porcentaje_pagado = 100;
            $venta->estado_real = $venta->estado;
        }

        return Inertia::render('Admin/Ventas/Show', [
            'venta' => $venta
        ]);
    }

    public function edit(string $id)
    {
        $venta = Venta::with('detalles.producto')->findOrFail($id);
        $clientes = Cliente::where('estado', 'A')->get();
        $productos = \App\Models\Producto::with('categoria')->get();

        // Calcular stock para cada producto
        $inventoryService = app(\App\Services\InventoryService::class);
        $stocks = [];
        foreach ($productos as $producto) {
            $stocks[$producto->id] = $inventoryService->calcularStock($producto->id);
        }

        return Inertia::render('Admin/Ventas/Edit', [
            'venta' => $venta,
            'clientes' => $clientes,
            'productos' => $productos,
            'stocks' => $stocks
        ]);
    }

    public function update(Request $request, string $id)
    {
        $venta = Venta::findOrFail($id);

        // No permitir editar ventas completadas que ya tienen inventario
        if ($venta->detalles()->whereHas('inventarios')->exists()) {
            return back()->with('error', 'No se puede editar una venta que ya tiene movimientos de inventario');
        }

        $validated = $request->validate([
            'nro_venta' => 'required|string|max:50',
            'cliente_id' => 'required|exists:cliente,id',
            'usuario_id' => 'required|exists:usuario,id',
            'fecha' => 'required|date',
            'tipo' => 'required|in:contado,credito',
            'numero_cuotas' => 'nullable|integer|min:2|max:12',
            'estado' => 'required|in:pendiente,completado,cancelado',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:producto,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        try {
            $inventoryService = app(\App\Services\InventoryService::class);

            // Verificar stock
            foreach ($validated['detalles'] as $detalle) {
                $stockActual = $inventoryService->calcularStock($detalle['producto_id']);
                if ($stockActual < $detalle['cantidad']) {
                    $producto = \App\Models\Producto::find($detalle['producto_id']);
                    return back()->withInput()->with('error',
                        "El producto {$producto->nombre} no tiene stock suficiente. Disponible: {$stockActual} unidades");
                }
            }

            // Calcular total
            $total = 0;
            foreach ($validated['detalles'] as $detalle) {
                $total += $detalle['cantidad'] * $detalle['precio_unitario'];
            }

            \Illuminate\Support\Facades\DB::transaction(function () use ($venta, $validated, $total) {
                // Actualizar venta
                $venta->update([
                    'nro_venta' => $validated['nro_venta'],
                    'fecha' => $validated['fecha'],
                    'tipo' => $validated['tipo'],
                    'monto_total' => $total,
                    'saldo' => $validated['tipo'] === 'credito' ? $total : 0,
                    'numero_cuotas' => $validated['tipo'] === 'credito' ? ($validated['numero_cuotas'] ?? 2) : 0,
                    'estado' => $validated['estado'],
                    'cliente_id' => $validated['cliente_id'],
                    'usuario_id' => $validated['usuario_id']
                ]);

                // Eliminar detalles antiguos
                $venta->detalles()->delete();

                // Crear nuevos detalles
                foreach ($validated['detalles'] as $detalle) {
                    \App\Models\DetalleVenta::create([
                        'venta_id' => $venta->id,
                        'producto_id' => $detalle['producto_id'],
                        'cantidad' => $detalle['cantidad'],
                        'precio_unitario' => $detalle['precio_unitario'],
                        'subtotal' => $detalle['cantidad'] * $detalle['precio_unitario']
                    ]);
                }
            });

            return redirect('/admin/ventas')
                ->with('success', 'Venta actualizada exitosamente');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error al actualizar venta: ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        $venta = Venta::findOrFail($id);
        $venta->delete();

        return redirect('/admin/ventas')
            ->with('success', 'Venta eliminada exitosamente');
    }
}
