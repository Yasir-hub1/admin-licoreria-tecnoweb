<?php

namespace App\Services;

use App\Models\Credito;
use App\Models\Pagos;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreditService
{
    public function createCredit($venta, $numCuotas = 2)
    {
        $montoCuota = $venta->monto_total / $numCuotas;
        $fechaInicio = now();

        $credito = Credito::create([
            'venta_id' => $venta->id,
            'monto_total' => $venta->monto_total,
            'saldo' => $venta->monto_total,
            'numero_cuotas' => $numCuotas,
            'fecha_inicio' => $fechaInicio,
            'estado' => 'activo'
        ]);

        // Generar cuotas (se almacenan en tabla pagos como registros de cuotas pendientes)
        $this->generateCuotas($credito, $numCuotas);

        return $credito;
    }

    public function generateCuotas($credito, $numCuotas)
    {
        // Verificar si ya existen cuotas para este crédito (evitar duplicados)
        $cuotasExistentes = Pagos::where('credito_id', $credito->id)->count();
        if ($cuotasExistentes > 0) {
            Log::warning("Intento de generar cuotas duplicadas para crédito ID: {$credito->id}. Cuotas existentes: {$cuotasExistentes}");
            return; // No generar cuotas si ya existen
        }

        $montoCuota = $credito->monto_total / $numCuotas;
        $fechaVencimiento = Carbon::parse($credito->fecha_inicio);

        for ($i = 1; $i <= $numCuotas; $i++) {
            $fechaVencimiento->addMonth();

            // Generar observación y truncar si es necesario (máximo 200 caracteres)
            $observacion = "Cuota {$i} de {$numCuotas} - Vence: {$fechaVencimiento->format('Y-m-d')}";
            $observacion = mb_substr($observacion, 0, 200);

            // Verificar que no exista ya una cuota con este número para este crédito
            $cuotaExistente = Pagos::where('credito_id', $credito->id)
                ->where('numero_cuota', $i)
                ->first();

            if ($cuotaExistente) {
                Log::warning("Cuota {$i} ya existe para crédito ID: {$credito->id}. Omitiendo creación.");
                continue; // Saltar esta cuota si ya existe
            }

            // Las cuotas se registran como pagos con fecha_pago null
            // IMPORTANTE: numero_cuota es el identificador único y directo de cada cuota
            Pagos::create([
                'credito_id' => $credito->id,
                'numero_cuota' => $i, // Campo directo para identificar la cuota (único por crédito)
                'fecha_pago' => null,
                'monto' => $montoCuota,
                'metodo' => 'pendiente',
                'nro_transaccion' => "CUOTA-{$i}",
                'observacion' => $observacion
            ]);
        }
    }

    public function registerPayment($creditoId, $monto, $metodo, $nroTransaccion = null, $observacion = null)
    {
        return DB::transaction(function () use ($creditoId, $monto, $metodo, $nroTransaccion, $observacion) {
            $credito = Credito::findOrFail($creditoId);

            // Buscar la primera cuota pendiente (ordenada por numero_cuota)
            // Usar numero_cuota directamente para garantizar orden correcto
            $cuotaPendiente = Pagos::where('credito_id', $creditoId)
                ->whereNull('fecha_pago')
                ->whereNotNull('numero_cuota')
                ->orderBy('numero_cuota', 'asc')
                ->first();

            if (!$cuotaPendiente) {
                throw new \Exception('No hay cuotas pendientes para este crédito');
            }

            // Verificar que el monto no exceda el saldo pendiente
            if ($monto > $credito->saldo) {
                throw new \Exception('El monto del pago no puede ser mayor al saldo pendiente');
            }

            // Truncar observación si es necesario (máximo 200 caracteres)
            $observacionTruncada = $observacion ? mb_substr($observacion, 0, 200) : null;

            // Guardar el nro_transaccion original de la cuota (formato CUOTA-X)
            $nroTransaccionOriginal = $cuotaPendiente->nro_transaccion;

            // Si el usuario proporciona un nro_transaccion nuevo, combinarlo con el original
            // Mantener siempre el formato CUOTA-X para identificar la cuota
            $nroTransaccionFinal = $nroTransaccionOriginal;
            if ($nroTransaccion && !empty($nroTransaccion) && $nroTransaccion !== $nroTransaccionOriginal) {
                // Si hay un número de transacción nuevo, agregarlo a la observación pero mantener CUOTA-X
                $observacionTruncada = ($observacionTruncada ? $observacionTruncada . ' - ' : '') .
                                      "Nro. Transacción: {$nroTransaccion}";
                $observacionTruncada = mb_substr($observacionTruncada, 0, 200);
            }

            // Actualizar la cuota pendiente con el pago
            // SIEMPRE mantener el nro_transaccion original (CUOTA-X) para identificar la cuota
            $cuotaPendiente->update([
                'fecha_pago' => now(),
                'monto' => $monto,
                'metodo' => $metodo,
                'nro_transaccion' => $nroTransaccionOriginal, // SIEMPRE mantener el formato CUOTA-X
                'observacion' => $observacionTruncada ?: $cuotaPendiente->observacion
            ]);

            // Actualizar saldo del crédito
            $credito->saldo -= $monto;

            if ($credito->saldo <= 0) {
                $credito->saldo = 0;
                $credito->estado = 'pagado';

                // Actualizar estado de la venta a completado si el crédito está pagado
                if ($credito->venta) {
                    $credito->venta->update([
                        'saldo' => 0,
                        'estado' => 'completado'
                    ]);
                }
            }

            $credito->save();

            // Actualizar estado del crédito
            $this->updateCreditStatus($creditoId);

            return $cuotaPendiente;
        });
    }

    public function checkMoras()
    {
        $creditos = Credito::where('estado', 'activo')->get();

        foreach ($creditos as $credito) {
            $pagosPendientes = Pagos::where('credito_id', $credito->id)
                ->whereNull('fecha_pago')
                ->where('observacion', 'like', '%Vence:%')
                ->get();

            foreach ($pagosPendientes as $pago) {
                // Extraer fecha de vencimiento de la observación
                if (preg_match('/Vence: (\d{4}-\d{2}-\d{2})/', $pago->observacion, $matches)) {
                    $fechaVencimiento = Carbon::parse($matches[1]);

                    if (now()->greaterThan($fechaVencimiento)) {
                        $credito->estado = 'mora';
                        $credito->save();
                        break;
                    }
                }
            }
        }
    }

    public function updateCreditStatus($creditoId)
    {
        $credito = Credito::findOrFail($creditoId);

        if ($credito->saldo <= 0) {
            $credito->estado = 'pagado';
        } else {
            // Verificar si hay cuotas vencidas
            $pagosPendientes = Pagos::where('credito_id', $creditoId)
                ->whereNull('fecha_pago')
                ->where('observacion', 'like', '%Vence:%')
                ->get();

            foreach ($pagosPendientes as $pago) {
                if (preg_match('/Vence: (\d{4}-\d{2}-\d{2})/', $pago->observacion, $matches)) {
                    $fechaVencimiento = Carbon::parse($matches[1]);
                    if (now()->greaterThan($fechaVencimiento)) {
                        $credito->estado = 'mora';
                        $credito->save();
                        return;
                    }
                }
            }

            $credito->estado = 'activo';
        }

        $credito->save();
    }
}

