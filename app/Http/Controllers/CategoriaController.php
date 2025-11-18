<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoriaController extends Controller
{
    public function index()
    {
        $categorias = Categoria::withCount('productos')->paginate(15);
        return Inertia::render('Admin/Categorias/Index', [
            'categorias' => $categorias
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Categorias/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:categoria'
        ]);

        Categoria::create($validated);

        return redirect('/admin/categorias')
            ->with('success', 'Categoría creada exitosamente');
    }

    public function show(string $id)
    {
        $categoria = Categoria::with('productos')->findOrFail($id);
        return Inertia::render('Admin/Categorias/Show', [
            'categoria' => $categoria
        ]);
    }

    public function edit(string $id)
    {
        $categoria = Categoria::findOrFail($id);
        return Inertia::render('Admin/Categorias/Edit', [
            'categoria' => $categoria
        ]);
    }

    public function update(Request $request, string $id)
    {
        $categoria = Categoria::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:categoria,nombre,' . $id
        ]);

        $categoria->update($validated);

        return redirect('/admin/categorias')
            ->with('success', 'Categoría actualizada exitosamente');
    }

    public function destroy(string $id)
    {
        $categoria = Categoria::findOrFail($id);
        $categoria->delete();

        return redirect('/admin/categorias')
            ->with('success', 'Categoría eliminada exitosamente');
    }
}
