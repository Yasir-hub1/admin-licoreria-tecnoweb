<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CompraController extends BaseController
{
    public function index()
    {
        $usuario = Auth::user();
        
        // Verificar si tiene permiso para ver todas las compras (admin)
        $puedeVerTodas = $usuario->tienePermiso('compras.listar');
        
        // Verificar si tiene permiso para ver solo sus compras (proveedor)
        $puedeVerPropias = $usuario->tienePermiso('compras.ver_propias');
        
        // Si no tiene ningún permiso, denegar acceso
        if (!$puedeVerTodas && !$puedeVerPropias) {
            abort(403, 'No tiene permiso para ver compras');
        }
        
        $query = Compra::with('proveedor');
        
        // Si solo tiene permiso para ver propias, filtrar por proveedor
        if ($puedeVerPropias && !$puedeVerTodas) {
            // Obtener el proveedor asociado al usuario
            $proveedor = $usuario->proveedor;
            
            if (!$proveedor) {
                // Si el usuario no tiene proveedor asociado, no puede ver compras
                $compras = Compra::where('id', 0)->paginate(15); // Query vacío
            } else {
                // Filtrar solo las compras de este proveedor
                $query->where('proveedor_id', $proveedor->id);
            }
        }
        
        $compras = $query->orderBy('id', 'desc')->paginate(15);
        
        return Inertia::render('Admin/Compras/Index', [
            'compras' => $compras,
            'esProveedor' => $puedeVerPropias && !$puedeVerTodas
        ]);
    }

    public function create()
    {
        $this->verificarPermiso('compras.crear');
        
        $proveedores = Proveedor::all();
        $productos = \App\Models\Producto::with('categoria')->get();
        return Inertia::render('Admin/Compras/Create', [
            'proveedores' => $proveedores,
            'productos' => $productos
        ]);
    }

    public function store(Request $request)
    {
        $this->verificarPermiso('compras.crear');
        
        $validated = $request->validate([
            'nro_compra' => 'nullable|string|max:50',
            'descripcion' => 'nullable|string|max:200',
            'proveedor_id' => 'required|exists:proveedor,id',
            'fecha' => 'required|date',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:producto,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                // Generar número de compra si no se proporciona usando CounterService
                if (empty($validated['nro_compra'])) {
                    $counterService = app(\App\Services\CounterService::class);
                    $validated['nro_compra'] = $counterService->obtenerSiguienteCompra();
                }

                // Crear compra con estado pendiente por defecto
                $compra = Compra::create([
                    'nro_compra' => $validated['nro_compra'],
                    'descripcion' => $validated['descripcion'] ?? null,
                    'proveedor_id' => $validated['proveedor_id'],
                    'fecha' => $validated['fecha'],
                    'estado' => 'pendiente',
                ]);

                // Crear detalles de compra
                foreach ($validated['detalles'] as $detalle) {
                    \App\Models\DetalleCompra::create([
                        'compra_id' => $compra->id,
                        'producto_id' => $detalle['producto_id'],
                        'cantidad' => $detalle['cantidad'],
                        'precio_unitario' => $detalle['precio_unitario'],
                        'subtotal' => $detalle['cantidad'] * $detalle['precio_unitario'],
                    ]);
                }
            });

            return redirect('/admin/compras')
                ->with('success', 'Compra creada exitosamente');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error al crear compra: ' . $e->getMessage());
        }
    }

    public function show(string $id)
    {
        $usuario = Auth::user();
        
        // Verificar permisos
        $puedeVerTodas = $usuario->tienePermiso('compras.ver');
        $puedeVerPropias = $usuario->tienePermiso('compras.ver_propias');
        
        if (!$puedeVerTodas && !$puedeVerPropias) {
            abort(403, 'No tiene permiso para ver esta compra');
        }
        
        $compra = Compra::with(['proveedor', 'detalles.producto'])->findOrFail($id);
        
        // Si solo tiene permiso para ver propias, verificar que la compra sea de su proveedor
        if ($puedeVerPropias && !$puedeVerTodas) {
            $proveedor = $usuario->proveedor;
            
            if (!$proveedor || $compra->proveedor_id !== $proveedor->id) {
                abort(403, 'No tiene permiso para ver esta compra. Solo puede ver las compras donde está involucrado como proveedor.');
            }
        }

        // Calcular el total sumando los subtotales de los detalles
        $total = $compra->detalles->sum('subtotal');

        // Agregar el total al objeto compra
        $compra->total = $total;

        return Inertia::render('Admin/Compras/Show', [
            'compra' => $compra,
            'esProveedor' => $puedeVerPropias && !$puedeVerTodas
        ]);
    }

    public function edit(string $id)
    {
        $this->verificarPermiso('compras.editar');
        
        $compra = Compra::with('detalles.producto', 'detalles.inventarios')->findOrFail($id);
        $proveedores = Proveedor::all();
        $productos = \App\Models\Producto::with('categoria')->get();
        return Inertia::render('Admin/Compras/Edit', [
            'compra' => $compra,
            'proveedores' => $proveedores,
            'productos' => $productos
        ]);
    }

    public function update(Request $request, string $id)
    {
        $this->verificarPermiso('compras.editar');
        
        $compra = Compra::findOrFail($id);

        // No permitir editar compras canceladas
        if ($compra->estado === 'cancelado') {
            return back()->with('error', 'No se puede editar una compra cancelada');
        }

        // No permitir editar compras validadas (que ya tienen inventario)
        if ($compra->estado === 'validado' || $compra->detalles()->whereHas('inventarios')->exists()) {
            return back()->with('error', 'No se puede editar una compra que ya fue validada');
        }

        $validated = $request->validate([
            'nro_compra' => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:200',
            'proveedor_id' => 'required|exists:proveedor,id',
            'fecha' => 'required|date',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:producto,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($compra, $validated) {
                // Actualizar compra
                $compra->update([
                    'nro_compra' => $validated['nro_compra'],
                    'descripcion' => $validated['descripcion'] ?? null,
                    'proveedor_id' => $validated['proveedor_id'],
                    'fecha' => $validated['fecha'],
                ]);

                // Eliminar detalles antiguos
                $compra->detalles()->delete();

                // Crear nuevos detalles
                foreach ($validated['detalles'] as $detalle) {
                    \App\Models\DetalleCompra::create([
                        'compra_id' => $compra->id,
                        'producto_id' => $detalle['producto_id'],
                        'cantidad' => $detalle['cantidad'],
                        'precio_unitario' => $detalle['precio_unitario'],
                        'subtotal' => $detalle['cantidad'] * $detalle['precio_unitario'],
                    ]);
                }
            });

            return redirect('/admin/compras')
                ->with('success', 'Compra actualizada exitosamente');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error al actualizar compra: ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        $this->verificarPermiso('compras.eliminar');
        
        $compra = Compra::findOrFail($id);

        // No permitir eliminar compras validadas
        if ($compra->estado === 'validado') {
            return back()->with('error', 'No se puede eliminar una compra que ya fue validada. Use la opción de cancelar si es necesario.');
        }

        $compra->delete();

        return redirect('/admin/compras')
            ->with('success', 'Compra eliminada exitosamente');
    }

    public function validar(string $id)
    {
        $this->verificarPermiso('compras.validar');
        
        $compra = Compra::with('detalles.producto', 'proveedor')->findOrFail($id);

        // No permitir validar compras canceladas
        if ($compra->estado === 'cancelado') {
            return back()->with('error', 'No se puede validar una compra cancelada');
        }

        $inventoryService = app(\App\Services\InventoryService::class);

        try {
            DB::transaction(function () use ($compra, $inventoryService) {
                // Registrar movimientos de inventario para cada detalle
                foreach ($compra->detalles as $detalle) {
                    $inventoryService->registrarMovimiento([
                        'tipo_movimiento' => 'INGRESO',
                        'cantidad' => $detalle->cantidad,
                        'fecha' => now(),
                        'glosa' => "Compra #{$compra->nro_compra} del proveedor {$compra->proveedor->nombre}",
                        'producto_id' => $detalle->producto_id,
                        'detalle_compra_id' => $detalle->id
                    ]);
                }

                // Actualizar estado a validado
                $compra->update(['estado' => 'validado']);
            });

            return back()->with('success', 'Compra validada exitosamente. Inventario actualizado.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al validar compra: ' . $e->getMessage());
        }
    }

    public function cancelar(string $id)
    {
        $this->verificarPermiso('compras.cancelar');
        
        $compra = Compra::with('detalles.inventarios')->findOrFail($id);

        // No permitir cancelar compras ya validadas (que tienen inventario)
        if ($compra->estado === 'validado') {
            return back()->with('error', 'No se puede cancelar una compra que ya fue validada. El inventario ya fue actualizado.');
        }

        // No permitir cancelar compras ya canceladas
        if ($compra->estado === 'cancelado') {
            return back()->with('error', 'Esta compra ya está cancelada');
        }

        try {
            $compra->update(['estado' => 'cancelado']);

            return back()->with('success', 'Compra cancelada exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al cancelar compra: ' . $e->getMessage());
        }
    }
}
