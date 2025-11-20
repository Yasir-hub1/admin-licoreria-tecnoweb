<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Inertia\Inertia;

class ProductoController extends BaseController
{
    public function index()
    {
        $this->verificarPermiso('productos.listar');

        $productos = Producto::with('categoria')->paginate(15);
        return Inertia::render('Admin/Productos/Index', [
            'productos' => $productos
        ]);
    }

    public function create()
    {
        $this->verificarPermiso('productos.crear');

        $categorias = Categoria::all();
        return Inertia::render('Admin/Productos/Create', [
            'categorias' => $categorias
        ]);
    }

    public function store(Request $request)
    {
        $this->verificarPermiso('productos.crear');
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'marca' => 'nullable|string|max:50',
            'categoria_id' => 'required|exists:categoria,id'
        ]);

        // Generar código automáticamente usando CounterService
        $counterService = app(\App\Services\CounterService::class);
        $codigo = $counterService->obtenerSiguienteProducto();

        // Verificar que el código no exista (por si acaso)
        while (Producto::where('codigo', $codigo)->exists()) {
            $codigo = $counterService->obtenerSiguienteProducto();
        }

        $validated['codigo'] = $codigo;

        Producto::create($validated);

        return redirect('/admin/productos')
            ->with('success', 'Producto creado exitosamente con código: ' . $codigo);
    }

    public function show(string $id)
    {
        $this->verificarPermiso('productos.ver');

        $producto = Producto::with('categoria')->findOrFail($id);
        return Inertia::render('Admin/Productos/Show', [
            'producto' => $producto
        ]);
    }

    public function edit(string $id)
    {
        $this->verificarPermiso('productos.editar');

        $producto = Producto::findOrFail($id);
        $categorias = Categoria::all();
        return Inertia::render('Admin/Productos/Edit', [
            'producto' => $producto,
            'categorias' => $categorias
        ]);
    }

    public function update(Request $request, string $id)
    {
        $this->verificarPermiso('productos.editar');

        $producto = Producto::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'marca' => 'nullable|string|max:50',
            'categoria_id' => 'required|exists:categoria,id'
        ]);

        // Mantener el código original (no se puede modificar)
        $validated['codigo'] = $producto->codigo;

        $producto->update($validated);

        return redirect('/admin/productos')
            ->with('success', 'Producto actualizado exitosamente');
    }

    public function destroy(string $id)
    {
        $this->verificarPermiso('productos.eliminar');

        try {
            $producto = Producto::findOrFail($id);

            // Verificar si el producto tiene relaciones antes de intentar eliminar
            $tieneCompras = $producto->detallesCompra()->exists();
            $tieneVentas = $producto->detallesVenta()->exists();
            $tieneCarrito = $producto->itemsCarrito()->exists();
            $tieneInventario = $producto->inventarios()->exists();

            if ($tieneCompras || $tieneVentas || $tieneCarrito || $tieneInventario) {
                $relaciones = [];
                
                if ($tieneCompras) {
                    $relaciones[] = 'compras';
                }
                if ($tieneVentas) {
                    $relaciones[] = 'ventas';
                }
                if ($tieneCarrito) {
                    $relaciones[] = 'carrito de compras';
                }
                if ($tieneInventario) {
                    $relaciones[] = 'inventario';
                }

                $mensaje = 'No se puede eliminar el producto "' . $producto->nombre . '" porque está siendo utilizado en: ' . implode(', ', $relaciones) . '.';
                
                return redirect('/admin/productos')
                    ->with('error', $mensaje);
            }

            $producto->delete();

            return redirect('/admin/productos')
                ->with('success', 'Producto eliminado exitosamente');
        } catch (QueryException $e) {
            // Capturar excepciones de clave foránea por si acaso
            if ($e->getCode() == '23503' || str_contains($e->getMessage(), 'foreign key constraint')) {
                $productoNombre = isset($producto) ? $producto->nombre : 'este producto';
                return redirect('/admin/productos')
                    ->with('error', 'No se puede eliminar el producto "' . $productoNombre . '" porque está siendo utilizado en otras operaciones del sistema (compras, ventas o inventario).');
            }
            
            // Re-lanzar otras excepciones
            throw $e;
        }
    }
}
