<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cliente;
use App\Models\Venta;
use App\Services\PaymentGatewayService;
use Illuminate\Support\Facades\DB;

class TestPaymentGateway extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:payment-gateway {--phone=75633655} {--amount=0.01}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar la pasarela de pagos con Tigo Money';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $phone = $this->option('phone');
        $amount = (float) $this->option('amount');

        $this->info("🧪 Iniciando prueba de pasarela de pagos");
        $this->info("📱 Teléfono: {$phone}");
        $this->info("💰 Monto: Bs. {$amount}");

        try {
            // Buscar o crear cliente de prueba
            $cliente = Cliente::where('telefono', $phone)->first();

            if (!$cliente) {
                $this->warn("⚠️  Cliente no encontrado con teléfono {$phone}");
                $this->info("📝 Creando cliente de prueba...");

                // Buscar un usuario cliente o crear uno de prueba
                $usuario = \App\Models\Usuario::whereHas('rol', function($q) {
                    $q->where('nombre', 'cliente');
                })->first();

                if (!$usuario) {
                    $this->error("❌ No se encontró ningún usuario cliente. Por favor crea uno primero.");
                    return 1;
                }

                $cliente = Cliente::create([
                    'ci' => '12345678',
                    'nombre' => 'Cliente Prueba',
                    'telefono' => $phone,
                    'direccion' => 'Dirección de prueba',
                    'estado' => 'A',
                    'usuario_id' => $usuario->id
                ]);

                $this->info("✅ Cliente creado: ID {$cliente->id}");
            } else {
                $this->info("✅ Cliente encontrado: {$cliente->nombre} (ID: {$cliente->id})");
            }

            // Crear venta de prueba
            $this->info("📦 Creando venta de prueba...");

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

            $this->info("✅ Venta creada: {$venta->nro_venta} (ID: {$venta->id})");

            // Procesar pago con pasarela
            $this->info("💳 Procesando pago con Tigo Money...");

            $paymentGatewayService = app(PaymentGatewayService::class);
            $resultado = $paymentGatewayService->processPayment($venta, 'tigo_money', $cliente);

            $pago = $resultado['pago'];
            $result = $resultado['result'];

            $this->info("✅ Pago procesado:");
            $this->line("   - ID Pago: {$pago->id}");
            $this->line("   - Nro Pago: {$pago->nro_pago}");
            $this->line("   - Estado: {$pago->estado}");
            $this->line("   - Monto: Bs. {$pago->monto}");

            if (isset($result->error) && $result->error == 1) {
                $this->error("❌ Error en la pasarela:");
                $this->error("   Mensaje: " . ($result->message ?? 'Error desconocido'));
                return 1;
            }

            if (isset($result->values)) {
                $this->info("📲 Respuesta de PagoFacil:");
                $this->line("   - Transacción: " . ($result->values ?? 'N/A'));
            }

            $this->info("\n🔗 URLs importantes:");
            $this->line("   - Verificar estado: /payment/check-status/{$pago->id}");
            $this->line("   - Página de confirmación: /payment/confirm/{$pago->id}");
            $this->line("   - Callback URL: " . url('/payment/callback'));

            $this->info("\n✅ Prueba completada exitosamente!");
            $this->info("📝 Revisa el estado del pago en la base de datos o en la página de confirmación");

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error durante la prueba:");
            $this->error("   " . $e->getMessage());
            $this->error("   " . $e->getTraceAsString());
            return 1;
        }
    }
}
