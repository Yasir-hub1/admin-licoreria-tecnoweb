<?php

namespace App\Services;

use App\Models\Inventario;
use App\Models\Producto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function registrarMovimiento($data)
    {
        $usuarioId = Auth::id();

        // Validar que haya usuario autenticado
        if (!$usuarioId) {
            throw new \Exception('No hay usuario autenticado para registrar el movimiento de inventario');
        }

        // Validar que haya producto_id
        if (empty($data['producto_id'])) {
            throw new \Exception('El producto_id es requerido para registrar el movimiento');
        }

        // Validar cantidad (permitir 0 para ajustes que reduzcan stock a 0)
        if (!isset($data['cantidad']) || !is_numeric($data['cantidad']) || $data['cantidad'] < 0) {
            throw new \Exception('La cantidad debe ser un número mayor o igual a cero');
        }

        // Si la cantidad es 0 y es un INGRESO, no tiene sentido
        if ($data['cantidad'] == 0 && $data['tipo_movimiento'] === 'INGRESO') {
            throw new \Exception('La cantidad de un INGRESO debe ser mayor a cero');
        }

        // Calcular stock actual
        $stockActual = $this->calcularStock($data['producto_id']);

        // Ajustar según tipo de movimiento
        if ($data['tipo_movimiento'] === 'INGRESO') {
            $stockActual += abs($data['cantidad']);
        } else {
            // SALIDA - restar cantidad
            $stockActual -= abs($data['cantidad']);
            // Asegurar que no sea negativo
            if ($stockActual < 0) {
                $stockActual = 0;
            }
        }

        // Truncar glosa si es muy larga (máximo 200 caracteres)
        $glosa = isset($data['glosa']) ? mb_substr($data['glosa'], 0, 200) : '';

        try {
            $movimiento = Inventario::create([
                'tipo_movimiento' => $data['tipo_movimiento'],
                'cantidad' => abs($data['cantidad']), // Siempre positivo en la BD
                'fecha' => $data['fecha'] ?? now(),
                'stock_actual' => $stockActual,
                'glosa' => $glosa,
                'usuario_id' => $usuarioId,
                'producto_id' => $data['producto_id'],
                'detalle_compra_id' => $data['detalle_compra_id'] ?? null,
                'detalle_venta_id' => $data['detalle_venta_id'] ?? null
            ]);

            return $movimiento;
        } catch (\Exception $e) {
            throw new \Exception('Error al registrar movimiento de inventario: ' . $e->getMessage());
        }
    }

    public function calcularStock($productoId)
    {
        $ultimoMovimiento = Inventario::where('producto_id', $productoId)
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        return $ultimoMovimiento ? $ultimoMovimiento->stock_actual : 0;
    }

    public function ajustarStock($productoId, $cantidad, $glosa)
    {
        // Validar que la cantidad sea un número válido
        if (!is_numeric($cantidad) || $cantidad < 0) {
            throw new \Exception('La cantidad debe ser un número mayor o igual a cero');
        }

        $stockActual = $this->calcularStock($productoId);
        $diferencia = $cantidad - $stockActual;

        // Si no hay diferencia, no registrar movimiento
        if ($diferencia == 0) {
            throw new \Exception('No hay diferencia entre el stock actual (' . $stockActual . ') y la cantidad ingresada (' . $cantidad . '). No se registró ningún movimiento.');
        }

        // Truncar glosa si es muy larga (máximo 200 caracteres)
        $glosaTruncada = mb_substr($glosa, 0, 200);

        try {
            $movimiento = $this->registrarMovimiento([
                'tipo_movimiento' => $diferencia > 0 ? 'INGRESO' : 'SALIDA',
                'cantidad' => abs($diferencia),
                'fecha' => now(),
                'glosa' => $glosaTruncada,
                'producto_id' => $productoId
            ]);

            return $movimiento;
        } catch (\Exception $e) {
            \Log::error('Error en ajustarStock: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getKardex($productoId, $fechaInicio = null, $fechaFin = null)
    {
        $query = Inventario::where('producto_id', $productoId)
            ->with(['usuario', 'detalleCompra', 'detalleVenta'])
            ->orderBy('fecha', 'asc')
            ->orderBy('id', 'asc');

        if ($fechaInicio) {
            $query->where('fecha', '>=', $fechaInicio);
        }

        if ($fechaFin) {
            $query->where('fecha', '<=', $fechaFin);
        }

        return $query->get();
    }
}

