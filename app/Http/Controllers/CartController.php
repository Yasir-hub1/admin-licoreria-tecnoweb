<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index()
    {
        $cart = $this->cartService->getCartWithDetails();

        return Inertia::render('Shop/Cart', [
            'cart' => $cart
        ]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:producto,id',
            'cantidad' => 'required|integer|min:1'
        ]);

        try {
            // Verificar stock disponible
            $producto = \App\Models\Producto::findOrFail($request->producto_id);
            $inventoryService = app(\App\Services\InventoryService::class);
            $stockActual = $inventoryService->calcularStock($producto->id);

            if ($stockActual < $request->cantidad) {
                return back()->with('error', "Stock insuficiente. Disponible: {$stockActual} unidades");
            }

            $this->cartService->addItem($request->producto_id, $request->cantidad);

            return back()->with('success', 'Producto agregado al carrito exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al agregar producto: ' . $e->getMessage());
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'producto_id' => 'required',
            'cantidad' => 'required|integer|min:0'
        ]);

        $this->cartService->updateItem($request->producto_id, $request->cantidad);

        return back()->with('success', 'Carrito actualizado');
    }

    public function remove($id)
    {
        $this->cartService->removeItem($id);

        return back()->with('success', 'Producto eliminado del carrito');
    }

    public function clear()
    {
        $this->cartService->clear();

        return back()->with('success', 'Carrito vaciado');
    }

    public function getCount()
    {
        $count = $this->cartService->getItemCount();
        return response()->json([
            'count' => $count
        ]);
    }
}
