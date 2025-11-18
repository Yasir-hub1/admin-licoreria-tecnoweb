<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::with('categoria');

        // Filtro por categoría
        if ($request->categoria_id) {
            $query->where('categoria_id', $request->categoria_id);
        }

        // Búsqueda
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->search . '%')
                  ->orWhere('descripcion', 'like', '%' . $request->search . '%')
                  ->orWhere('marca', 'like', '%' . $request->search . '%');
            });
        }

        // Filtro por precio
        if ($request->precio_min) {
            $query->where('precio', '>=', $request->precio_min);
        }
        if ($request->precio_max) {
            $query->where('precio', '<=', $request->precio_max);
        }

        $productos = $query->paginate(12);
        $categorias = Categoria::all();

        return Inertia::render('Shop/Index', [
            'productos' => $productos,
            'categorias' => $categorias,
            'filters' => $request->only(['categoria_id', 'search', 'precio_min', 'precio_max'])
        ]);
    }

    public function show($id)
    {
        $producto = Producto::with('categoria')->findOrFail($id);

        // Productos relacionados (misma categoría)
        $relacionados = Producto::where('categoria_id', $producto->categoria_id)
            ->where('id', '!=', $id)
            ->limit(4)
            ->get();

        return Inertia::render('Shop/ProductDetail', [
            'producto' => $producto,
            'relacionados' => $relacionados
        ]);
    }

    public function category($id)
    {
        $categoria = Categoria::findOrFail($id);
        $productos = Producto::where('categoria_id', $id)
            ->paginate(12);

        return Inertia::render('Shop/Index', [
            'productos' => $productos,
            'categorias' => Categoria::all(),
            'categoria_actual' => $categoria,
            'filters' => ['categoria_id' => $id]
        ]);
    }
}
