<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Credito;
use App\Models\Categoria;
use App\Models\DetalleVenta;
use App\Models\DetalleCompra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class EstadisticasController extends Controller
{
    public function index(Request $request)
    {
        // Filtros por defecto
        $fechaInicio = $request->get('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', Carbon::now()->format('Y-m-d'));
        $categoriaId = $request->get('categoria_id');
        $clienteId = $request->get('cliente_id');

        // KPIs Principales
        $kpis = $this->calcularKPIs($fechaInicio, $fechaFin);

        // Ventas por día
        $ventasPorDia = $this->ventasPorDia($fechaInicio, $fechaFin);

        // Ventas por mes
        $ventasPorMes = $this->ventasPorMes($fechaInicio, $fechaFin);

        // Productos más vendidos
        $productosMasVendidos = $this->productosMasVendidos($fechaInicio, $fechaFin, $categoriaId, 10);

        // Ventas por categoría
        $ventasPorCategoria = $this->ventasPorCategoria($fechaInicio, $fechaFin);

        // Ventas por tipo (contado/crédito)
        $ventasPorTipo = $this->ventasPorTipo($fechaInicio, $fechaFin);

        // Top clientes
        $topClientes = $this->topClientes($fechaInicio, $fechaFin, 10);

        // Estado de créditos
        $estadoCreditos = $this->estadoCreditos();

        // Compras vs Ventas
        $comprasVsVentas = $this->comprasVsVentas($fechaInicio, $fechaFin);

        // Ingresos por empleado
        $ingresosPorEmpleado = $this->ingresosPorEmpleado($fechaInicio, $fechaFin);

        // Productos con bajo stock
        $productosBajoStock = $this->productosBajoStock(10);

        // Datos para filtros
        $categorias = Categoria::orderBy('nombre')->get();
        $clientes = Cliente::where('estado', 'A')->orderBy('nombre')->get();

        return Inertia::render('Admin/Estadisticas/Index', [
            'kpis' => $kpis,
            'ventasPorDia' => $ventasPorDia,
            'ventasPorMes' => $ventasPorMes,
            'productosMasVendidos' => $productosMasVendidos,
            'ventasPorCategoria' => $ventasPorCategoria,
            'ventasPorTipo' => $ventasPorTipo,
            'topClientes' => $topClientes,
            'estadoCreditos' => $estadoCreditos,
            'comprasVsVentas' => $comprasVsVentas,
            'ingresosPorEmpleado' => $ingresosPorEmpleado,
            'productosBajoStock' => $productosBajoStock,
            'categorias' => $categorias,
            'clientes' => $clientes,
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'categoria_id' => $categoriaId,
                'cliente_id' => $clienteId,
            ]
        ]);
    }

    private function calcularKPIs($fechaInicio, $fechaFin)
    {
        // Total de ventas
        $totalVentas = Venta::whereBetween('fecha', [$fechaInicio, $fechaFin])->count();

        // Total de ingresos
        $totalIngresos = Venta::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->sum('monto_total');

        // Ingresos de contado
        $ingresosContado = Venta::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->where('tipo', 'contado')
            ->sum('monto_total');

        // Ingresos de crédito
        $ingresosCredito = Venta::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->where('tipo', 'credito')
            ->sum('monto_total');

        // Saldo pendiente de créditos
        $saldoPendiente = Credito::where('estado', 'activo')
            ->sum('saldo');

        // Total de compras
        $totalCompras = Compra::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->where('estado', '!=', 'cancelado')
            ->count();

        // Total gastado en compras
        $totalGastado = DB::table('compra')
            ->join('detalle_compra', 'compra.id', '=', 'detalle_compra.compra_id')
            ->whereBetween('compra.fecha', [$fechaInicio, $fechaFin])
            ->where('compra.estado', '!=', 'cancelado')
            ->sum('detalle_compra.subtotal');

        // Utilidad (Ingresos - Gastos)
        $utilidad = $totalIngresos - $totalGastado;

        // Total de clientes
        $totalClientes = Cliente::where('estado', 'A')->count();

        // Total de productos
        $totalProductos = Producto::count();

        // Ventas de hoy
        $ventasHoy = Venta::whereDate('fecha', today())->count();
        $ingresosHoy = Venta::whereDate('fecha', today())->sum('monto_total');

        return [
            'total_ventas' => $totalVentas,
            'total_ingresos' => round($totalIngresos, 2),
            'ingresos_contado' => round($ingresosContado, 2),
            'ingresos_credito' => round($ingresosCredito, 2),
            'saldo_pendiente' => round($saldoPendiente, 2),
            'total_compras' => $totalCompras,
            'total_gastado' => round($totalGastado, 2),
            'utilidad' => round($utilidad, 2),
            'total_clientes' => $totalClientes,
            'total_productos' => $totalProductos,
            'ventas_hoy' => $ventasHoy,
            'ingresos_hoy' => round($ingresosHoy, 2),
        ];
    }

    private function ventasPorDia($fechaInicio, $fechaFin)
    {
        $ventas = Venta::select(
                DB::raw("DATE(fecha) as fecha"),
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('SUM(monto_total) as total')
            )
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->groupBy(DB::raw("DATE(fecha)"))
            ->orderBy('fecha')
            ->get();

        return [
            'labels' => $ventas->pluck('fecha')->map(fn($f) => Carbon::parse($f)->format('d/m/Y'))->toArray(),
            'cantidad' => $ventas->pluck('cantidad')->toArray(),
            'total' => $ventas->pluck('total')->map(fn($t) => round($t, 2))->toArray(),
        ];
    }

    private function ventasPorMes($fechaInicio, $fechaFin)
    {
        $ventas = Venta::select(
                DB::raw("DATE_TRUNC('month', fecha) as mes"),
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('SUM(monto_total) as total')
            )
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->groupBy(DB::raw("DATE_TRUNC('month', fecha)"))
            ->orderBy('mes')
            ->get();

        return [
            'labels' => $ventas->pluck('mes')->map(fn($m) => Carbon::parse($m)->format('M Y'))->toArray(),
            'cantidad' => $ventas->pluck('cantidad')->toArray(),
            'total' => $ventas->pluck('total')->map(fn($t) => round($t, 2))->toArray(),
        ];
    }

    private function productosMasVendidos($fechaInicio, $fechaFin, $categoriaId = null, $limit = 10)
    {
        $query = DetalleVenta::select(
                'producto.id',
                'producto.nombre',
                'producto.codigo',
                'categoria.nombre as categoria',
                DB::raw('SUM(detalle_venta.cantidad) as cantidad_vendida'),
                DB::raw('SUM(detalle_venta.subtotal) as total_vendido')
            )
            ->join('venta', 'detalle_venta.venta_id', '=', 'venta.id')
            ->join('producto', 'detalle_venta.producto_id', '=', 'producto.id')
            ->leftJoin('categoria', 'producto.categoria_id', '=', 'categoria.id')
            ->whereBetween('venta.fecha', [$fechaInicio, $fechaFin])
            ->groupBy('producto.id', 'producto.nombre', 'producto.codigo', 'categoria.nombre');

        if ($categoriaId) {
            $query->where('producto.categoria_id', $categoriaId);
        }

        return $query->orderByDesc('cantidad_vendida')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nombre' => $item->nombre,
                    'codigo' => $item->codigo,
                    'categoria' => $item->categoria,
                    'cantidad_vendida' => (int) $item->cantidad_vendida,
                    'total_vendido' => round($item->total_vendido, 2),
                ];
            });
    }

    private function ventasPorCategoria($fechaInicio, $fechaFin)
    {
        $ventas = DetalleVenta::select(
                'categoria.id',
                'categoria.nombre',
                DB::raw('SUM(detalle_venta.cantidad) as cantidad'),
                DB::raw('SUM(detalle_venta.subtotal) as total')
            )
            ->join('venta', 'detalle_venta.venta_id', '=', 'venta.id')
            ->join('producto', 'detalle_venta.producto_id', '=', 'producto.id')
            ->join('categoria', 'producto.categoria_id', '=', 'categoria.id')
            ->whereBetween('venta.fecha', [$fechaInicio, $fechaFin])
            ->groupBy('categoria.id', 'categoria.nombre')
            ->orderByDesc('total')
            ->get();

        return [
            'labels' => $ventas->pluck('nombre')->toArray(),
            'cantidad' => $ventas->pluck('cantidad')->toArray(),
            'total' => $ventas->pluck('total')->map(fn($t) => round($t, 2))->toArray(),
        ];
    }

    private function ventasPorTipo($fechaInicio, $fechaFin)
    {
        $ventas = Venta::select(
                'tipo',
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('SUM(monto_total) as total')
            )
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->groupBy('tipo')
            ->get();

        return [
            'labels' => $ventas->pluck('tipo')->map(fn($t) => ucfirst($t))->toArray(),
            'cantidad' => $ventas->pluck('cantidad')->toArray(),
            'total' => $ventas->pluck('total')->map(fn($t) => round($t, 2))->toArray(),
        ];
    }

    private function topClientes($fechaInicio, $fechaFin, $limit = 10)
    {
        return Venta::select(
                'cliente.id',
                'cliente.nombre',
                'cliente.ci',
                DB::raw('COUNT(venta.id) as total_ventas'),
                DB::raw('SUM(venta.monto_total) as total_gastado')
            )
            ->join('cliente', 'venta.cliente_id', '=', 'cliente.id')
            ->whereBetween('venta.fecha', [$fechaInicio, $fechaFin])
            ->groupBy('cliente.id', 'cliente.nombre', 'cliente.ci')
            ->orderByDesc('total_gastado')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nombre' => $item->nombre,
                    'ci' => $item->ci,
                    'total_ventas' => (int) $item->total_ventas,
                    'total_gastado' => round($item->total_gastado, 2),
                ];
            });
    }

    private function estadoCreditos()
    {
        $creditos = Credito::select(
                'estado',
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('SUM(monto_total) as monto_total'),
                DB::raw('SUM(saldo) as saldo_pendiente')
            )
            ->groupBy('estado')
            ->get();

        return [
            'labels' => $creditos->pluck('estado')->map(fn($e) => ucfirst($e))->toArray(),
            'cantidad' => $creditos->pluck('cantidad')->toArray(),
            'monto_total' => $creditos->pluck('monto_total')->map(fn($m) => round($m, 2))->toArray(),
            'saldo_pendiente' => $creditos->pluck('saldo_pendiente')->map(fn($s) => round($s, 2))->toArray(),
        ];
    }

    private function comprasVsVentas($fechaInicio, $fechaFin)
    {
        $ventas = Venta::select(
                DB::raw("DATE_TRUNC('month', fecha) as mes"),
                DB::raw('SUM(monto_total) as total')
            )
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->groupBy(DB::raw("DATE_TRUNC('month', fecha)"))
            ->orderBy('mes')
            ->get();

        $compras = DB::table('compra')
            ->select(
                DB::raw("DATE_TRUNC('month', compra.fecha) as mes"),
                DB::raw('SUM(detalle_compra.subtotal) as total')
            )
            ->join('detalle_compra', 'compra.id', '=', 'detalle_compra.compra_id')
            ->whereBetween('compra.fecha', [$fechaInicio, $fechaFin])
            ->where('compra.estado', '!=', 'cancelado')
            ->groupBy(DB::raw("DATE_TRUNC('month', compra.fecha)"))
            ->orderBy('mes')
            ->get();

        $meses = collect($ventas->pluck('mes'))->merge($compras->pluck('mes'))->unique()->sort()->values();

        return [
            'labels' => $meses->map(fn($m) => Carbon::parse($m)->format('M Y'))->toArray(),
            'ventas' => $meses->map(function ($mes) use ($ventas) {
                $venta = $ventas->firstWhere('mes', $mes);
                return round($venta->total ?? 0, 2);
            })->toArray(),
            'compras' => $meses->map(function ($mes) use ($compras) {
                $compra = $compras->firstWhere('mes', $mes);
                return round($compra->total ?? 0, 2);
            })->toArray(),
        ];
    }

    private function ingresosPorEmpleado($fechaInicio, $fechaFin)
    {
        return Venta::select(
                'empleado.id',
                'empleado.nombre',
                DB::raw('COUNT(venta.id) as total_ventas'),
                DB::raw('SUM(venta.monto_total) as total_ingresos')
            )
            ->join('empleado', 'venta.vendedor_id', '=', 'empleado.id')
            ->whereBetween('venta.fecha', [$fechaInicio, $fechaFin])
            ->groupBy('empleado.id', 'empleado.nombre')
            ->orderByDesc('total_ingresos')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nombre' => $item->nombre,
                    'total_ventas' => (int) $item->total_ventas,
                    'total_ingresos' => round($item->total_ingresos, 2),
                ];
            });
    }

    private function productosBajoStock($limit = 10)
    {
        $inventoryService = app(\App\Services\InventoryService::class);

        $productos = Producto::with('categoria')->get()->map(function ($producto) use ($inventoryService) {
            $stock = $inventoryService->calcularStock($producto->id);
            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'codigo' => $producto->codigo,
                'categoria' => $producto->categoria->nombre ?? 'Sin categoría',
                'stock' => $stock,
            ];
        })->filter(fn($p) => $p['stock'] <= 10)
          ->sortBy('stock')
          ->take($limit)
          ->values();

        return $productos;
    }
}
