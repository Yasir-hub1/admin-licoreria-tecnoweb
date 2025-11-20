<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Cliente;
use App\Models\Venta;
use App\Services\PaymentGatewayService;

$phone = '75633655';
$amount = 0.01;

echo "Testing Payment Gateway\n";
echo "Phone: {$phone}\n";
echo "Amount: {$amount}\n\n";

try {
    // Buscar cliente
    $cliente = Cliente::where('telefono', $phone)->first();
    
    if (!$cliente) {
        $usuario = \App\Models\Usuario::whereHas('rol', function($q) {
            $q->where('nombre', 'cliente');
        })->first();
        
        if (!$usuario) {
            die("No cliente user found\n");
        }
        
        $cliente = Cliente::create([
            'ci' => '12345678',
            'nombre' => 'Test Client',
            'telefono' => $phone,
            'direccion' => 'Test',
            'estado' => 'A',
            'usuario_id' => $usuario->id
        ]);
    }
    
    // Crear venta
    $venta = Venta::create([
        'nro_venta' => 'TEST-' . time(),
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
    
    echo "Venta created: {$venta->id}\n";
    
    // Procesar pago
    $service = app(PaymentGatewayService::class);
    $result = $service->processPayment($venta, 'tigo_money', $cliente);
    
    echo "Payment ID: {$result['pago']->id}\n";
    echo "Payment Number: {$result['pago']->nro_pago}\n";
    echo "Status: {$result['pago']->estado}\n";
    echo "Transaction: " . ($result['pago']->nro_transaccion ?? 'N/A') . "\n";
    echo "Result Error: " . ($result['result']->error ?? 'N/A') . "\n";
    echo "Result Message: " . ($result['result']->message ?? 'N/A') . "\n";
    echo "Result Values: " . ($result['result']->values ?? 'N/A') . "\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

