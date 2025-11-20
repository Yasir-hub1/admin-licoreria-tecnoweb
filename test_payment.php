<?php

/**
 * Script de prueba para la pasarela de pagos Tigo Money
 * 
 * Uso: php test_payment.php
 * 
 * Este script prueba el procesamiento de un pago de 0.01 Bs. con Tigo Money
 * usando el teléfono 75633655
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Cliente;
use App\Models\Venta;
use App\Services\PaymentGatewayService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "🧪 PRUEBA DE PASARELA DE PAGOS - TIGO MONEY\n";
echo "==========================================\n\n";

$phone = '75633655';
$amount = 0.01;

echo "📱 Teléfono: {$phone}\n";
echo "💰 Monto: Bs. {$amount}\n\n";

// Verificar configuración
echo "🔍 Verificando configuración...\n";
$commerceId = env('PAGO_FACIL_COMERCE_ID');
if (empty($commerceId)) {
    echo "⚠️  ADVERTENCIA: PAGO_FACIL_COMERCE_ID no está configurado en .env\n";
    echo "   El pago fallará si no está configurado.\n\n";
} else {
    echo "✅ PAGO_FACIL_COMERCE_ID: {$commerceId}\n\n";
}

try {
    DB::beginTransaction();

    // Buscar o crear cliente de prueba
    echo "👤 Buscando cliente...\n";
    $cliente = Cliente::where('telefono', $phone)->first();

    if (!$cliente) {
        echo "⚠️  Cliente no encontrado, creando cliente de prueba...\n";
        
        $usuario = \App\Models\Usuario::whereHas('rol', function($q) {
            $q->where('nombre', 'cliente');
        })->first();

        if (!$usuario) {
            echo "❌ ERROR: No se encontró ningún usuario cliente.\n";
            echo "   Por favor crea un usuario con rol 'cliente' primero.\n";
            exit(1);
        }

        $cliente = Cliente::create([
            'ci' => '12345678',
            'nombre' => 'Cliente Prueba Tigo Money',
            'telefono' => $phone,
            'direccion' => 'Dirección de prueba',
            'estado' => 'A',
            'usuario_id' => $usuario->id
        ]);

        echo "✅ Cliente creado: {$cliente->nombre} (ID: {$cliente->id})\n\n";
    } else {
        echo "✅ Cliente encontrado: {$cliente->nombre} (ID: {$cliente->id})\n\n";
    }

    // Crear venta de prueba
    echo "📦 Creando venta de prueba...\n";
    $venta = Venta::create([
        'nro_venta' => 'TEST-TIGO-' . time(),
        'fecha' => now(),
        'tipo' => 'contado',
        'metodo_pago' => 'tigo_money',
        'monto_total' => $amount,
        'saldo' => 0,
        'numero_cuotas' => 0,
        'estado' => 'pendiente',
        'estado_pago' => 'pendiente',
        'cliente_id' => $cliente->id,
        'vendedor_id' => null
    ]);

    echo "✅ Venta creada: {$venta->nro_venta} (ID: {$venta->id})\n\n";

    // Procesar pago con pasarela
    echo "💳 Procesando pago con Tigo Money...\n";
    echo "   Enviando solicitud a PagoFacil...\n\n";

    $paymentGatewayService = app(PaymentGatewayService::class);
    $resultado = $paymentGatewayService->processPayment($venta, 'tigo_money', $cliente);

    $pago = $resultado['pago'];
    $result = $resultado['result'];

    echo "📊 RESULTADO DEL PAGO:\n";
    echo "=====================\n";
    echo "ID Pago: {$pago->id}\n";
    echo "Nro Pago: {$pago->nro_pago}\n";
    echo "Estado: {$pago->estado}\n";
    echo "Monto: Bs. {$pago->monto}\n";
    echo "Tipo: {$pago->tipo_pago}\n";
    if ($pago->nro_transaccion) {
        echo "Nro Transacción: {$pago->nro_transaccion}\n";
    }
    echo "\n";

    echo "📲 RESPUESTA DE PAGOFACIL:\n";
    echo "==========================\n";
    if (isset($result->error)) {
        echo "Error: " . ($result->error == 1 ? 'Sí' : 'No') . "\n";
    }
    if (isset($result->message)) {
        echo "Mensaje: {$result->message}\n";
    }
    if (isset($result->values)) {
        echo "Valores/Transacción: {$result->values}\n";
    }
    echo "\n";

    if (isset($result->error) && $result->error == 1) {
        echo "❌ ERROR EN LA PASARELA:\n";
        echo "   {$result->message}\n\n";
        DB::rollBack();
        exit(1);
    }

    echo "✅ Pago procesado exitosamente!\n\n";

    echo "🔗 URLs IMPORTANTES:\n";
    echo "===================\n";
    echo "Página de confirmación: " . url("/payment/confirm/{$pago->id}") . "\n";
    echo "Verificar estado: " . url("/payment/check-status/{$pago->id}") . "\n";
    echo "Callback URL: " . url('/payment/callback') . "\n";
    echo "\n";

    echo "📝 PRÓXIMOS PASOS:\n";
    echo "==================\n";
    echo "1. Revisa tu teléfono {$phone} para confirmar el pago en Tigo Money\n";
    echo "2. Una vez confirmado, PagoFacil enviará un callback a:\n";
    echo "   " . url('/payment/callback') . "\n";
    echo "3. Puedes verificar el estado del pago visitando:\n";
    echo "   " . url("/payment/confirm/{$pago->id}") . "\n";
    echo "\n";

    DB::commit();

    echo "✅ Prueba completada exitosamente!\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ ERROR DURANTE LA PRUEBA:\n";
    echo "===========================\n";
    echo "Mensaje: {$e->getMessage()}\n";
    echo "\nTrace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

