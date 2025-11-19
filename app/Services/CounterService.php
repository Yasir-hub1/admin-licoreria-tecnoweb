<?php

namespace App\Services;

use App\Models\Contador;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CounterService
{
    /**
     * Obtener el siguiente número para un tipo de contador
     * Thread-safe usando bloqueo de fila en la base de datos
     */
    public function obtenerSiguiente(string $tipo): string
    {
        return DB::transaction(function () use ($tipo) {
            // Bloquear la fila para evitar condiciones de carrera
            $contador = Contador::where('tipo', $tipo)
                ->lockForUpdate()
                ->first();

            // Si no existe, crearlo
            if (!$contador) {
                $contador = Contador::obtenerPorTipo($tipo);
            }

            // Incrementar el valor
            $contador->incrementar();

            // Generar el número formateado
            $numero = $contador->prefijo . str_pad(
                $contador->valor_actual,
                $contador->longitud,
                '0',
                STR_PAD_LEFT
            );

            Log::info("Contador {$tipo} incrementado a {$contador->valor_actual}", [
                'tipo' => $tipo,
                'numero_generado' => $numero,
                'valor_actual' => $contador->valor_actual
            ]);

            return $numero;
        });
    }

    /**
     * Obtener el siguiente número de venta
     */
    public function obtenerSiguienteVenta(): string
    {
        return $this->obtenerSiguiente('venta');
    }

    /**
     * Obtener el siguiente número de compra
     */
    public function obtenerSiguienteCompra(): string
    {
        return $this->obtenerSiguiente('compra');
    }

    /**
     * Obtener el siguiente código de producto
     */
    public function obtenerSiguienteProducto(): string
    {
        return $this->obtenerSiguiente('producto');
    }

    /**
     * Obtener el valor actual de un contador sin incrementarlo
     */
    public function obtenerValorActual(string $tipo): int
    {
        $contador = Contador::obtenerPorTipo($tipo);
        return $contador->valor_actual;
    }

    /**
     * Establecer el valor de un contador manualmente
     */
    public function establecerValor(string $tipo, int $valor): bool
    {
        try {
            $contador = Contador::obtenerPorTipo($tipo);
            $contador->valor_actual = $valor;
            $contador->save();

            Log::info("Contador {$tipo} establecido a {$valor}", [
                'tipo' => $tipo,
                'valor_anterior' => $contador->getOriginal('valor_actual'),
                'valor_nuevo' => $valor
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Error al establecer contador {$tipo}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todos los contadores
     */
    public function obtenerTodos(): \Illuminate\Database\Eloquent\Collection
    {
        return Contador::orderBy('tipo')->get();
    }

    /**
     * Sincronizar contadores con valores existentes en la base de datos
     * Útil para migrar de un sistema antiguo
     */
    public function sincronizarDesdeBaseDatos(): array
    {
        $resultados = [];

        // Sincronizar contador de ventas
        try {
            $ultimaVenta = \App\Models\Venta::orderBy('id', 'desc')->first();
            if ($ultimaVenta && $ultimaVenta->nro_venta) {
                $numero = (int) preg_replace('/[^0-9]/', '', $ultimaVenta->nro_venta);
                $contador = Contador::obtenerPorTipo('venta');
                if ($contador->valor_actual < $numero) {
                    $contador->valor_actual = $numero;
                    $contador->save();
                    $resultados['venta'] = $numero;
                }
            }
        } catch (\Exception $e) {
            Log::error("Error al sincronizar contador de ventas: " . $e->getMessage());
        }

        // Sincronizar contador de compras
        try {
            $ultimaCompra = \App\Models\Compra::orderBy('id', 'desc')->first();
            if ($ultimaCompra && $ultimaCompra->nro_compra) {
                $numero = (int) preg_replace('/[^0-9]/', '', $ultimaCompra->nro_compra);
                $contador = Contador::obtenerPorTipo('compra');
                if ($contador->valor_actual < $numero) {
                    $contador->valor_actual = $numero;
                    $contador->save();
                    $resultados['compra'] = $numero;
                }
            }
        } catch (\Exception $e) {
            Log::error("Error al sincronizar contador de compras: " . $e->getMessage());
        }

        // Sincronizar contador de productos
        try {
            $ultimoProducto = \App\Models\Producto::orderBy('id', 'desc')->first();
            if ($ultimoProducto && $ultimoProducto->codigo) {
                $numero = (int) preg_replace('/[^0-9]/', '', $ultimoProducto->codigo);
                $contador = Contador::obtenerPorTipo('producto');
                if ($contador->valor_actual < $numero) {
                    $contador->valor_actual = $numero;
                    $contador->save();
                    $resultados['producto'] = $numero;
                }
            }
        } catch (\Exception $e) {
            Log::error("Error al sincronizar contador de productos: " . $e->getMessage());
        }

        return $resultados;
    }
}

