<?php

namespace App\Services;

use App\Models\Producto;
use Illuminate\Support\Facades\Session;

class CartService
{
    private $sessionKey = 'shopping_cart';

    public function getCart()
    {
        return Session::get($this->sessionKey, []);
    }

    public function addItem($productoId, $cantidad = 1)
    {
        $producto = Producto::findOrFail($productoId);
        $cart = $this->getCart();

        if (isset($cart[$productoId])) {
            $cart[$productoId]['cantidad'] += $cantidad;
        } else {
            $cart[$productoId] = [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'precio' => $producto->precio,
                'cantidad' => $cantidad,
                'marca' => $producto->marca,
                'codigo' => $producto->codigo,
            ];
        }

        Session::put($this->sessionKey, $cart);
        return $cart;
    }

    public function updateItem($productoId, $cantidad)
    {
        $cart = $this->getCart();

        if (isset($cart[$productoId])) {
            if ($cantidad <= 0) {
                unset($cart[$productoId]);
            } else {
                $cart[$productoId]['cantidad'] = $cantidad;
            }
        }

        Session::put($this->sessionKey, $cart);
        return $cart;
    }

    public function removeItem($productoId)
    {
        $cart = $this->getCart();
        unset($cart[$productoId]);
        Session::put($this->sessionKey, $cart);
        return $cart;
    }

    public function clear()
    {
        Session::forget($this->sessionKey);
        return [];
    }

    public function getTotal()
    {
        $cart = $this->getCart();
        $total = 0;

        foreach ($cart as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }

        return $total;
    }

    public function getItemCount()
    {
        $cart = $this->getCart();
        $count = 0;

        foreach ($cart as $item) {
            $count += $item['cantidad'];
        }

        return $count;
    }

    public function getCartWithDetails()
    {
        $cart = $this->getCart();

        return [
            'items' => array_values($cart),
            'total' => $this->getTotal(),
            'itemCount' => $this->getItemCount()
        ];
    }
}
