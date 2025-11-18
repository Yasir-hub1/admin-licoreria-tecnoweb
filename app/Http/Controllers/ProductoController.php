<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::with('categoria')->paginate(15);
        return Inertia::render('Admin/Productos/Index', [
            'productos' => $productos
        ]);
    }

    public function create()
    {
        $categorias = Categoria::all();
        return Inertia::render('Admin/Productos/Create', [
            'categorias' => $categorias
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'marca' => 'nullable|string|max:50',
            'categoria_id' => 'required|exists:categoria,id'
        ]);

        // Generar código automáticamente
        $ultimoProducto = Producto::orderBy('id', 'desc')->first();
        $numero = $ultimoProducto ? ((int) preg_replace('/[^0-9]/', '', $ultimoProducto->codigo)) + 1 : 1;
        $codigo = 'PROD-' . str_pad($numero, 6, '0', STR_PAD_LEFT);

        // Verificar que el código no exista (por si acaso)
        while (Producto::where('codigo', $codigo)->exists()) {
            $numero++;
            $codigo = 'PROD-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
        }

        $validated['codigo'] = $codigo;

        Producto::create($validated);

        return redirect('/admin/productos')
            ->with('success', 'Producto creado exitosamente con código: ' . $codigo);
    }

    public function show(string $id)
    {
        $producto = Producto::with('categoria')->findOrFail($id);
        return Inertia::render('Admin/Productos/Show', [
            'producto' => $producto
        ]);
    }

    public function edit(string $id)
    {
        $producto = Producto::findOrFail($id);
        $categorias = Categoria::all();
        return Inertia::render('Admin/Productos/Edit', [
            'producto' => $producto,
            'categorias' => $categorias
        ]);
    }

    public function update(Request $request, string $id)
    {
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
        $producto = Producto::findOrFail($id);
        $producto->delete();

        return redirect('/admin/productos')
            ->with('success', 'Producto eliminado exitosamente');
    }
}
