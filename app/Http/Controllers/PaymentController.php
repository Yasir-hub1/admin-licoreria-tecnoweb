<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

date_default_timezone_set('America/La_Paz');

class PaymentController extends Controller
{
    /**
     * Callback desde PagoFacil - Nueva API v2
     */
    public function callback(Request $request)
    {
        try {
            Log::info('Callback recibido de PagoFacil', [
                'data' => $request->all(),
                'headers' => $request->headers->all()
            ]);

            // La nueva API puede enviar los datos de diferentes formas
            // Intentar obtener paymentNumber o PedidoID
            $nroPago = $request->input("paymentNumber")
                    ?? $request->input("PedidoID")
                    ?? $request->input("payment_number")
                    ?? $request->input("nro_pago");

            // Intentar obtener estado
            $estado = $request->input("status")
                    ?? $request->input("Estado")
                    ?? $request->input("estado")
                    ?? $request->input("state");

            // Si viene en un objeto data
            if ($request->has('data')) {
                $data = $request->input('data');
                $nroPago = $nroPago ?? ($data['paymentNumber'] ?? $data['payment_number'] ?? null);
                $estado = $estado ?? ($data['status'] ?? $data['estado'] ?? null);
            }

            Log::info('Datos extraídos del callback', [
                'nro_pago' => $nroPago,
                'estado' => $estado
            ]);

            if (!$nroPago) {
                Log::warning('Callback sin número de pago', ['request' => $request->all()]);
                return response()->json([
                    'error' => 1,
                    'status' => 0,
                    'message' => "Número de pago no encontrado en el callback",
                    'values' => false
                ], 400);
            }

            // Estados: 'completed', 'paid', 'success', 2, '2', true, 1
            $estadoCompletado = in_array($estado, ['completed', 'paid', 'success', 2, '2', true, 1, '1']);

            if ($estadoCompletado) {
                $paymentGatewayService = app(PaymentGatewayService::class);
                $paymentGatewayService->confirmPayment($nroPago);

                Log::info('Pago confirmado desde callback', ['nro_pago' => $nroPago]);
            }

            return response()->json([
                'error' => 0,
                'status' => 1,
                'message' => "Callback procesado correctamente.",
                'values' => true
            ]);
        } catch (\Throwable $th) {
            Log::error('Error en callback de pago: ' . $th->getMessage(), [
                'trace' => $th->getTraceAsString()
            ]);
            return response()->json([
                'error' => 1,
                'status' => 0,
                'message' => "Error al procesar el callback: " . $th->getMessage(),
                'values' => false
            ], 400);
        }
    }

    /**
     * Mostrar página de confirmación de pago
     */
    public function confirm($id)
    {
        $pago = Pago::with([
            'venta.cliente',
            'venta.detalles.producto',
            'cuotaPago.credito'
        ])->findOrFail($id);

        return \Inertia\Inertia::render('Shop/PaymentConfirm', [
            'pago' => $pago
        ]);
    }

    /**
     * Consultar estado de pago
     */
    public function checkStatus($id)
    {
        $pago = Pago::with([
            'venta.cliente',
            'venta.detalles.producto',
            'cuotaPago.credito'
        ])->findOrFail($id);

        $paymentGatewayService = app(PaymentGatewayService::class);
        $result = $paymentGatewayService->consultPaymentStatus($pago);

        $pago->refresh();

        // Si es una petición Inertia, redirigir a la página de confirmación
        if (request()->wantsJson() || request()->header('X-Inertia')) {
            return \Inertia\Inertia::render('Shop/PaymentConfirm', [
                'pago' => $pago->fresh([
                    'venta.cliente',
                    'venta.detalles.producto',
                    'cuotaPago.credito'
                ])
            ]);
        }

        return response()->json([
            'pago' => $pago,
            'result' => $result
        ]);
    }
}

