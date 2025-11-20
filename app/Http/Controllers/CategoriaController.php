<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Inertia\Inertia;

class CategoriaController extends BaseController
{
    public function index()
    {
        $this->verificarPermiso('categorias.listar');

        $categorias = Categoria::withCount('productos')->orderBy('id', 'desc')->paginate(15);
        return Inertia::render('Admin/Categorias/Index', [
            'categorias' => $categorias
        ]);
    }

    public function create()
    {
        $this->verificarPermiso('categorias.crear');

        return Inertia::render('Admin/Categorias/Create');
    }

    public function store(Request $request)
    {
        $this->verificarPermiso('categorias.crear');
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:categoria'
        ]);

        Categoria::create($validated);

        return redirect('/admin/categorias')
            ->with('success', 'Categoría creada exitosamente');
    }

    public function show(string $id)
    {
        $this->verificarPermiso('categorias.ver');

        $categoria = Categoria::with('productos')->findOrFail($id);
        return Inertia::render('Admin/Categorias/Show', [
            'categoria' => $categoria
        ]);
    }

    public function edit(string $id)
    {
        $this->verificarPermiso('categorias.editar');

        $categoria = Categoria::findOrFail($id);
        return Inertia::render('Admin/Categorias/Edit', [
            'categoria' => $categoria
        ]);
    }

    public function update(Request $request, string $id)
    {
        $this->verificarPermiso('categorias.editar');

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
        $this->verificarPermiso('categorias.eliminar');

        try {
            $categoria = Categoria::findOrFail($id);

            // Verificar si la categoría tiene productos antes de intentar eliminar
            $tieneProductos = $categoria->productos()->exists();

            if ($tieneProductos) {
                $cantidadProductos = $categoria->productos()->count();
                $mensaje = 'No se puede eliminar la categoría "' . $categoria->nombre . '" porque tiene ' . $cantidadProductos . ' producto(s) asociado(s). Primero debe eliminar o reasignar los productos.';
                
                return redirect('/admin/categorias')
                    ->with('error', $mensaje);
            }

            $categoria->delete();

            return redirect('/admin/categorias')
                ->with('success', 'Categoría eliminada exitosamente');
        } catch (QueryException $e) {
            // Capturar excepciones de clave foránea por si acaso
            if ($e->getCode() == '23503' || str_contains($e->getMessage(), 'foreign key constraint')) {
                $categoriaNombre = isset($categoria) ? $categoria->nombre : 'esta categoría';
                return redirect('/admin/categorias')
                    ->with('error', 'No se puede eliminar la categoría "' . $categoriaNombre . '" porque tiene productos asociados. Primero debe eliminar o reasignar los productos.');
            }
            
            // Re-lanzar otras excepciones
            throw $e;
        }
    }
}
