<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;
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

        $producto = Producto::findOrFail($id);
        $producto->delete();

        return redirect('/admin/productos')
            ->with('success', 'Producto eliminado exitosamente');
    }
}
