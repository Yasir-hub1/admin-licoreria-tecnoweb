<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VentaController extends BaseController
{
    public function index()
    {
        // Verificar permiso para listar ventas
        $this->verificarPermiso('ventas.listar');
        
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
        // Verificar permiso para crear ventas
        $this->verificarPermiso('ventas.crear');
        
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
        // Verificar permiso para crear ventas
        $this->verificarPermiso('ventas.crear');
        
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
        // Verificar permiso para ver ventas
        $this->verificarPermiso('ventas.ver');
        
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
        // Verificar permiso para editar ventas
        $this->verificarPermiso('ventas.editar');
        
        $venta = Venta::with(['detalles.producto', 'usuario', 'cliente'])->findOrFail($id);
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
        // Verificar permiso para editar ventas
        $this->verificarPermiso('ventas.editar');
        
        $venta = Venta::findOrFail($id);

        // No permitir editar ventas a crédito que ya tienen pagos realizados
        if ($venta->tipo === 'credito' && $venta->credito) {
            $pagosRealizados = \App\Models\Pagos::where('credito_id', $venta->credito->id)
                ->whereNotNull('fecha_pago')
                ->exists();
            
            if ($pagosRealizados) {
                return back()->with('error', 'No se puede editar una venta a crédito que ya tiene pagos realizados');
            }
        }

        // Preparar datos para validación
        $data = $request->all();
        
        // Si el tipo es contado, forzar numero_cuotas a null
        if (($data['tipo'] ?? '') === 'contado') {
            $data['numero_cuotas'] = null;
        }

        $validated = validator($data, [
            'nro_venta' => 'required|string|max:50',
            'cliente_id' => 'required|exists:cliente,id',
            'usuario_id' => 'required|exists:usuario,id',
            'fecha' => 'required|date',
            'tipo' => 'required|in:contado,credito',
            'numero_cuotas' => [
                'nullable',
                function ($attribute, $value, $fail) use ($data) {
                    if (($data['tipo'] ?? '') === 'credito') {
                        if (is_null($value) || $value === '') {
                            $fail('El número de cuotas es requerido para ventas a crédito.');
                        } elseif (!is_numeric($value) || (int)$value < 2 || (int)$value > 12) {
                            $fail('El número de cuotas debe ser un número entre 2 y 12.');
                        }
                    }
                },
            ],
            'estado' => 'required|in:pendiente,completado,cancelado',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:producto,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
        ])->validate();

        try {
            $inventoryService = app(\App\Services\InventoryService::class);
            $cliente = Cliente::findOrFail($validated['cliente_id']);

            // Obtener detalles antiguos con sus inventarios antes de eliminarlos
            $detallesAntiguos = $venta->detalles()->with('inventarios')->get();

            // Verificar stock (considerando que vamos a revertir los movimientos antiguos)
            foreach ($validated['detalles'] as $detalle) {
                // Calcular stock disponible considerando que revertiremos los movimientos antiguos
                $stockActual = $inventoryService->calcularStock($detalle['producto_id']);
                
                // Sumar las cantidades que se revertirán de los detalles antiguos del mismo producto
                $cantidadARevertir = $detallesAntiguos
                    ->where('producto_id', $detalle['producto_id'])
                    ->sum('cantidad');
                
                $stockDisponible = $stockActual + $cantidadARevertir;
                
                if ($stockDisponible < $detalle['cantidad']) {
                    $producto = \App\Models\Producto::find($detalle['producto_id']);
                    return back()->withInput()->with('error',
                        "El producto {$producto->nombre} no tiene stock suficiente. Disponible: {$stockDisponible} unidades");
                }
            }

            // Calcular total
            $total = 0;
            foreach ($validated['detalles'] as $detalle) {
                $total += $detalle['cantidad'] * $detalle['precio_unitario'];
            }

            \Illuminate\Support\Facades\DB::transaction(function () use ($venta, $validated, $total, $detallesAntiguos, $inventoryService, $cliente) {
                // Revertir movimientos de inventario antiguos (crear INGRESO para compensar SALIDAS)
                // Esto debe hacerse ANTES de eliminar los detalles porque tienen onDelete('cascade')
                foreach ($detallesAntiguos as $detalleAntiguo) {
                    // Obtener todos los movimientos de inventario relacionados a este detalle
                    $movimientosInventario = \App\Models\Inventario::where('detalle_venta_id', $detalleAntiguo->id)->get();
                    
                    foreach ($movimientosInventario as $movimiento) {
                        // Si es una SALIDA, crear un INGRESO para revertirla
                        if ($movimiento->tipo_movimiento === 'SALIDA') {
                            $inventoryService->registrarMovimiento([
                                'tipo_movimiento' => 'INGRESO',
                                'cantidad' => $movimiento->cantidad,
                                'fecha' => $validated['fecha'],
                                'glosa' => "Reversión de venta #{$venta->nro_venta} - Edición",
                                'producto_id' => $movimiento->producto_id
                            ]);
                        }
                    }
                }

                // Eliminar detalles antiguos (los movimientos de inventario se eliminarán automáticamente por cascade)
                $venta->detalles()->delete();

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

                // Crear nuevos detalles y sus movimientos de inventario
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
                        'glosa' => "Venta #{$venta->nro_venta} - Cliente {$cliente->nombre}",
                        'producto_id' => $detalle['producto_id'],
                        'detalle_venta_id' => $detalleVenta->id
                    ]);
                }

                // Si es crédito y ya existe un crédito, actualizarlo
                if ($validated['tipo'] === 'credito' && $venta->credito) {
                    $venta->credito->update([
                        'monto_total' => $total,
                        'saldo' => $total,
                        'numero_cuotas' => $validated['numero_cuotas'] ?? 2
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
        // Verificar permiso para eliminar ventas
        $this->verificarPermiso('ventas.eliminar');
        
        $venta = Venta::findOrFail($id);
        $venta->delete();

        return redirect('/admin/ventas')
            ->with('success', 'Venta eliminada exitosamente');
    }
}
