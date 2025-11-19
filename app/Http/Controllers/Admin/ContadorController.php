<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CounterService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContadorController extends Controller
{
    protected $counterService;

    public function __construct(CounterService $counterService)
    {
        $this->counterService = $counterService;
    }

    public function index()
    {
        $contadores = $this->counterService->obtenerTodos();

        return Inertia::render('Admin/Contadores/Index', [
            'contadores' => $contadores
        ]);
    }

    public function sincronizar()
    {
        try {
            $resultados = $this->counterService->sincronizarDesdeBaseDatos();

            return back()->with('success', 'Contadores sincronizados correctamente. ' .
                (empty($resultados) ? 'No se encontraron valores para sincronizar.' :
                'Valores actualizados: ' . json_encode($resultados)));
        } catch (\Exception $e) {
            return back()->with('error', 'Error al sincronizar contadores: ' . $e->getMessage());
        }
    }

    public function actualizar(Request $request, $id)
    {
        $validated = $request->validate([
            'valor_actual' => 'required|integer|min:0',
            'prefijo' => 'nullable|string|max:10',
            'longitud' => 'nullable|integer|min:1|max:10',
            'descripcion' => 'nullable|string|max:200'
        ]);

        try {
            $contador = \App\Models\Contador::findOrFail($id);

            $contador->valor_actual = $validated['valor_actual'];
            if (isset($validated['prefijo'])) {
                $contador->prefijo = $validated['prefijo'];
            }
            if (isset($validated['longitud'])) {
                $contador->longitud = $validated['longitud'];
            }
            if (isset($validated['descripcion'])) {
                $contador->descripcion = $validated['descripcion'];
            }

            $contador->save();

            return back()->with('success', 'Contador actualizado correctamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al actualizar contador: ' . $e->getMessage());
        }
    }
}
