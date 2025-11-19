<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Services\CartService;
use App\Services\CheckoutService;

class CheckoutController extends Controller
{
    protected $cartService;
    protected $checkoutService;

    public function __construct(CartService $cartService, CheckoutService $checkoutService)
    {
        $this->cartService = $cartService;
        $this->checkoutService = $checkoutService;
    }

    public function index()
    {
        // Verificar autenticación
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Debes iniciar sesión para realizar una compra');
        }

        /** @var \App\Models\Usuario $user */
        $user = Auth::user();

        // Verificar que sea cliente
        if (!$user->isCliente()) {
            return redirect('/shop')->with('error', 'Solo los clientes pueden realizar compras');
        }

        $cliente = $user->cliente;

        if (!$cliente) {
            return redirect('/profile')->with('error', 'Completa tu información de cliente para continuar');
        }

        $cart = $this->cartService->getCartWithDetails();

        if (!$cart || count($cart['items']) === 0) {
            return redirect('/cart')
                ->with('error', 'El carrito está vacío');
        }

        // Verificar stock de todos los productos
        $inventoryService = app(\App\Services\InventoryService::class);
        $itemsConStock = [];
        foreach ($cart['items'] as $item) {
            $stockActual = $inventoryService->calcularStock($item['id']);
            if ($stockActual < $item['cantidad']) {
                return redirect('/cart')->with('error',
                    "El producto {$item['nombre']} no tiene stock suficiente. Disponible: {$stockActual} unidades");
            }
            $itemsConStock[] = $item;
        }

        // Calcular opciones de crédito dinámicas
        $opcionesCredito = $this->calcularOpcionesCredito($cliente, $cart['total']);

        // Cargar información de verificación
        $cliente->load('verificador');
        
        return Inertia::render('Shop/Checkout', [
            'cart' => $cart,
            'cliente' => [
                ...$cliente->toArray(),
                'tieneDocumentosCompletos' => $cliente->tieneDocumentosCompletos()
            ],
            'puedeCredito' => $this->checkoutService->validateCreditEligibility($cliente),
            'opcionesCredito' => $opcionesCredito
        ]);
    }

    private function calcularOpcionesCredito($cliente, $montoTotal)
    {
        // Verificar que el cliente esté verificado para crédito
        if (!$cliente->estaVerificadoParaCredito()) {
            return [];
        }

        $opciones = [];
        $creditosActivos = \App\Models\Credito::whereHas('venta', function($q) use ($cliente) {
            $q->where('cliente_id', $cliente->id);
        })->where('estado', 'activo')->sum('saldo');

        $creditoDisponible = max(0, $cliente->limite_credito - $creditosActivos);

        if ($montoTotal <= $creditoDisponible) {
            // Opciones de cuotas basadas en el monto
            if ($montoTotal <= 500) {
                $opciones = [2, 3];
            } elseif ($montoTotal <= 2000) {
                $opciones = [2, 3, 4];
            } else {
                $opciones = [2, 3, 4, 6];
            }
        }

        return $opciones;
    }

    public function process(Request $request)
    {
        // Verificar autenticación
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Debes iniciar sesión para realizar una compra');
        }

        /** @var \App\Models\Usuario $user */
        $user = Auth::user();

        if (!$user->isCliente()) {
            return redirect('/shop')->with('error', 'Solo los clientes pueden realizar compras');
        }

        $cliente = $user->cliente;

        if (!$cliente) {
            return redirect('/profile')->with('error', 'Completa tu información de cliente');
        }

        $request->validate([
            'tipo_pago' => 'required|in:contado,credito',
            'metodo_pago' => 'required|string',
            'numero_cuotas' => 'required_if:tipo_pago,credito|integer|min:2|max:12'
        ]);

        $cart = $this->cartService->getCartWithDetails();

        if (!$cart || count($cart['items']) === 0) {
            return back()->with('error', 'El carrito está vacío');
        }

        // Verificar stock nuevamente antes de procesar
        $inventoryService = app(\App\Services\InventoryService::class);
        foreach ($cart['items'] as $item) {
            $stockActual = $inventoryService->calcularStock($item['id']);
            if ($stockActual < $item['cantidad']) {
                return back()->with('error',
                    "El producto {$item['nombre']} no tiene stock suficiente. Disponible: {$stockActual} unidades");
            }
        }

        try {
            if ($request->tipo_pago === 'contado') {
                $venta = $this->checkoutService->processContado($cart, $cliente);
                $this->cartService->clear();

                return redirect('/my-orders')
                    ->with('success', 'Compra realizada exitosamente');
            } else {
                // Validar elegibilidad de crédito
                if (!$this->checkoutService->validateCreditEligibility($cliente)) {
                    return back()->with('error', 'No eres elegible para compra a crédito');
                }

                $numeroCuotas = $request->numero_cuotas ?? 2;
                $resultado = $this->checkoutService->processCredito($cart, $cliente, $numeroCuotas);
                $this->cartService->clear();

                return redirect('/my-credits')
                    ->with('success', "Compra a crédito realizada exitosamente. {$numeroCuotas} cuotas generadas.");
            }
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
