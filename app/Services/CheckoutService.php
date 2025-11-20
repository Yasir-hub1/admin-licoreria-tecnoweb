<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Credito;
use App\Models\Cliente;
use App\Models\Inventario;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function processContado($cart, $cliente, $metodoPago = 'efectivo')
    {
        return DB::transaction(function () use ($cart, $cliente, $metodoPago) {
            // Determinar estado inicial según método de pago
            $estadoInicial = $metodoPago === 'qr' ? 'pendiente' : 'completado';
            $estadoPagoInicial = $metodoPago === 'qr' ? 'pendiente' : 'completado';

            // Crear venta
            $venta = Venta::create([
                'nro_venta' => $this->generateNroVenta(),
                'fecha' => now(),
                'tipo' => 'contado',
                'metodo_pago' => $metodoPago,
                'monto_total' => $cart['total'],
                'saldo' => 0,
                'numero_cuotas' => 0,
                'estado' => $estadoInicial,
                'estado_pago' => $estadoPagoInicial,
                'cliente_id' => $cliente->id,
                'vendedor_id' => null
            ]);

            // Crear detalles de venta
            foreach ($cart['items'] as $item) {
                $detalle = DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $item['id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio'],
                    'subtotal' => $item['precio'] * $item['cantidad']
                ]);

                // Registrar movimiento de inventario (salida)
                $this->inventoryService->registrarMovimiento([
                    'tipo_movimiento' => 'SALIDA',
                    'cantidad' => $item['cantidad'],
                    'fecha' => now(),
                    'glosa' => "Venta #{$venta->nro_venta} a Cliente {$cliente->nombre}",
                    'producto_id' => $item['id'],
                    'detalle_venta_id' => $detalle->id
                ]);
            }

            // Procesar pago según método
            $paymentGatewayService = app(\App\Services\PaymentGatewayService::class);

            if ($metodoPago === 'qr') {
                // Procesar con pasarela QR
                $resultadoPago = $paymentGatewayService->processQRPayment($venta, $cliente);
                return ['venta' => $venta, 'pago' => $resultadoPago['pago'], 'result' => $resultadoPago];
            } else {
                // Procesar efectivo
                $pago = $paymentGatewayService->processCashPayment($venta, $cliente);
                return ['venta' => $venta, 'pago' => $pago];
            }
        });
    }

    public function processCredito($cart, $cliente, $cuotas = 2, $metodoPago = 'efectivo')
    {
        return DB::transaction(function () use ($cart, $cliente, $cuotas, $metodoPago) {
            // Validar elegibilidad
            if (!$this->validateCreditEligibility($cliente)) {
                throw new \Exception('El cliente no es elegible para crédito');
            }

            // Crear venta
            $venta = Venta::create([
                'nro_venta' => $this->generateNroVenta(),
                'fecha' => now(),
                'tipo' => 'credito',
                'metodo_pago' => $metodoPago,
                'monto_total' => $cart['total'],
                'saldo' => $cart['total'],
                'numero_cuotas' => $cuotas,
                'estado' => 'pendiente',
                'estado_pago' => 'pendiente',
                'cliente_id' => $cliente->id,
                'vendedor_id' => null
            ]);

            // Crear detalles de venta
            foreach ($cart['items'] as $item) {
                $detalle = DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $item['id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio'],
                    'subtotal' => $item['precio'] * $item['cantidad']
                ]);

                // Registrar movimiento de inventario (salida)
                $this->inventoryService->registrarMovimiento([
                    'tipo_movimiento' => 'SALIDA',
                    'cantidad' => $item['cantidad'],
                    'fecha' => now(),
                    'glosa' => "Venta #{$venta->nro_venta} a Crédito - Cliente {$cliente->nombre}",
                    'producto_id' => $item['id'],
                    'detalle_venta_id' => $detalle->id
                ]);
            }

            // Crear crédito usando CreditService
            $creditService = app(\App\Services\CreditService::class);
            $credito = $creditService->createCredit($venta, $cuotas);

            // Si el método de pago requiere pasarela, procesarlo
            $paymentGatewayService = app(\App\Services\PaymentGatewayService::class);
            $pago = null;

            if ($metodoPago === 'qr') {
                $resultadoPago = $paymentGatewayService->processQRPayment($venta, $cliente);
                $pago = $resultadoPago['pago'];
            } else {
                // Para crédito, el pago se registra pero no se completa hasta pagar cuotas
                $pago = $paymentGatewayService->processCashPayment($venta, $cliente);
                $pago->estado = 'pendiente';
                $pago->save();
            }

            return ['venta' => $venta, 'credito' => $credito, 'pago' => $pago];
        });
    }

    public function validateCreditEligibility($cliente)
    {
        // Verificar si tiene documentos verificados y aprobados
        if (!$cliente->estaVerificadoParaCredito()) {
            return false;
        }

        // Verificar si tiene créditos activos en mora
        $creditosMora = Credito::whereHas('venta', function($q) use ($cliente) {
            $q->where('cliente_id', $cliente->id);
        })->where('estado', 'mora')->exists();

        if ($creditosMora) {
            return false;
        }

        // Verificar límite de crédito
        $creditosActivos = Credito::whereHas('venta', function($q) use ($cliente) {
            $q->where('cliente_id', $cliente->id);
        })->where('estado', 'activo')->sum('saldo');

        if ($creditosActivos >= $cliente->limite_credito) {
            return false;
        }

        return true;
    }

    private function generateNroVenta()
    {
        $counterService = app(\App\Services\CounterService::class);
        return $counterService->obtenerSiguienteVenta();
    }
}

