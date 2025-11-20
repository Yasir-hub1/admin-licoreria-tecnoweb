#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Cliente;
use App\Models\Venta;
use App\Services\PaymentGatewayService;
use Illuminate\Support\Facades\DB;

$phone = '75633655';
$amount = 0.01;

echo "=== PRUEBA DE PASARELA DE PAGOS - TIGO MONEY ===\n\n";
echo "Teléfono: {$phone}\n";
echo "Monto: Bs. {$amount}\n\n";

try {
    DB::beginTransaction();

    echo "1. Buscando cliente...\n";
    $cliente = Cliente::where('telefono', $phone)->first();

    if (!$cliente) {
        echo "   Cliente no encontrado, creando...\n";
        $usuario = \App\Models\Usuario::whereHas('rol', function($q) {
            $q->where('nombre', 'cliente');
        })->first();

        if (!$usuario) {
            die("ERROR: No se encontró usuario cliente\n");
        }

        $cliente = Cliente::create([
            'ci' => '12345678',
            'nombre' => 'Cliente Prueba Tigo',
            'telefono' => $phone,
            'direccion' => 'Test',
            'estado' => 'A',
            'usuario_id' => $usuario->id
        ]);
        echo "   Cliente creado: ID {$cliente->id}\n";
    } else {
        echo "   Cliente encontrado: {$cliente->nombre} (ID: {$cliente->id})\n";
    }

    echo "\n2. Creando venta de prueba...\n";
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
    echo "   Venta creada: {$venta->nro_venta} (ID: {$venta->id})\n";

    echo "\n3. Procesando pago con Tigo Money...\n";
    echo "   Enviando solicitud a PagoFacil API...\n";

    $service = app(PaymentGatewayService::class);
    $result = $service->processPayment($venta, 'tigo_money', $cliente);

    $pago = $result['pago'];
    $resultado = $result['result'];

    echo "\n=== RESULTADO ===\n";
    echo "ID Pago: {$pago->id}\n";
    echo "Nro Pago: {$pago->nro_pago}\n";
    echo "Estado: {$pago->estado}\n";
    echo "Monto: Bs. {$pago->monto}\n";
    echo "Tipo: {$pago->tipo_pago}\n";
    if ($pago->nro_transaccion) {
        echo "Nro Transacción: {$pago->nro_transaccion}\n";
    }

    echo "\n=== RESPUESTA PAGOFACIL ===\n";
    if (isset($resultado->error)) {
        echo "Error: " . ($resultado->error == 1 ? 'Sí' : 'No') . "\n";
    }
    if (isset($resultado->message)) {
        echo "Mensaje: {$resultado->message}\n";
    }
    if (isset($resultado->values)) {
        echo "Valores/Transacción: {$resultado->values}\n";
    }

    if (isset($resultado->error) && $resultado->error == 1) {
        echo "\n❌ ERROR EN LA PASARELA\n";
        DB::rollBack();
        exit(1);
    }

    echo "\n✅ Pago procesado exitosamente!\n";
    echo "\nURLs:\n";
    echo "- Confirmación: " . url("/payment/confirm/{$pago->id}") . "\n";
    echo "- Verificar: " . url("/payment/check-status/{$pago->id}") . "\n";
    echo "- Callback: " . url('/payment/callback') . "\n";

    DB::commit();

} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nTrace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

