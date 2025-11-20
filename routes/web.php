<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\CreditoController;
use App\Http\Controllers\PagosController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\PaymentController;

// ====================================
// RUTAS PÚBLICAS
// ====================================

// Ruta de prueba para pasarela de pagos (TEMPORAL - ELIMINAR EN PRODUCCIÓN)
Route::get('/test-payment-gateway', function () {
    $phone = request('phone', '75633655');
    $amount = (float) request('amount', 0.01);

    try {
        // Buscar o crear cliente de prueba
        $cliente = \App\Models\Cliente::where('telefono', $phone)->first();

        if (!$cliente) {
            $usuario = \App\Models\Usuario::whereHas('rol', function($q) {
                $q->where('nombre', 'cliente');
            })->first();

            if (!$usuario) {
                return response()->json(['error' => 'No se encontró ningún usuario cliente'], 400);
            }

            $cliente = \App\Models\Cliente::create([
                'ci' => '12345678',
                'nombre' => 'Cliente Prueba',
                'telefono' => $phone,
                'direccion' => 'Dirección de prueba',
                'estado' => 'A',
                'usuario_id' => $usuario->id
            ]);
        }

        // Crear venta de prueba
        $venta = \App\Models\Venta::create([
            'nro_venta' => 'TEST-' . time(),
            'fecha' => now(),
            'tipo' => 'contado',
            'metodo_pago' => 'qr',
            'monto_total' => $amount,
            'saldo' => 0,
            'numero_cuotas' => 0,
            'estado' => 'pendiente',
            'estado_pago' => 'pendiente',
            'cliente_id' => $cliente->id,
            'vendedor_id' => null
        ]);

        // Procesar pago con pasarela QR
        $paymentGatewayService = app(\App\Services\PaymentGatewayService::class);
        $resultado = $paymentGatewayService->processQRPayment($venta, $cliente);

        $pago = $resultado['pago'];
        $result = $resultado['result'];

        return response()->json([
            'success' => true,
            'message' => 'Pago procesado exitosamente',
            'data' => [
                'pago' => [
                    'id' => $pago->id,
                    'nro_pago' => $pago->nro_pago,
                    'estado' => $pago->estado,
                    'monto' => $pago->monto,
                    'tipo_pago' => $pago->tipo_pago,
                    'nro_transaccion' => $pago->nro_transaccion,
                ],
                'venta' => [
                    'id' => $venta->id,
                    'nro_venta' => $venta->nro_venta,
                ],
                'resultado_pagofacil' => [
                    'error' => $result->error ?? null,
                    'message' => $result->message ?? null,
                    'values' => $result->values ?? null,
                ],
                'urls' => [
                    'confirmacion' => url("/payment/confirm/{$pago->id}"),
                    'check_status' => url("/payment/check-status/{$pago->id}"),
                    'callback' => url('https://db17d557a193.ngrok-free.app/payment/callback'),
                ]
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->name('test.payment.gateway');

// Página principal - redirige al login
Route::get('/', function () {
    if (Auth::check()) {
        /** @var \App\Models\Usuario $user */
        $user = Auth::user();

        // Si puede acceder al dashboard (propietario o empleado)
        if ($user->puedeAccederDashboard()) {
            return redirect('/admin/dashboard');
        }

        // Si tiene acceso al admin pero no al dashboard (permisos personalizados)
        if ($user->tieneAccesoAdmin()) {
            return redirect('/admin/bienvenida');
        }

        // Si no tiene acceso al admin, redirigir al shop
        return redirect('/shop');
    }
    return redirect('/login');
});

// Autenticación
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Tienda pública
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/product/{id}', [ShopController::class, 'show'])->name('shop.product');
Route::get('/shop/category/{id}', [ShopController::class, 'category'])->name('shop.category');

// Carrito (público - usa sesión)
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::get('/cart/count', [CartController::class, 'getCount'])->name('cart.count');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::put('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

// Callback de pasarela de pagos (público)
Route::post('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');

// ====================================
// RUTAS AUTENTICADAS - CLIENTES
// ====================================

Route::middleware(['auth', 'role:cliente'])->group(function () {

    // Checkout
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');

    // Mis compras
    Route::get('/my-orders', [CustomerController::class, 'orders'])->name('customer.orders');
    Route::get('/my-order/{id}', [CustomerController::class, 'orderDetail'])->name('customer.order.detail');

    // Mis créditos
    Route::get('/my-credits', [CustomerController::class, 'credits'])->name('customer.credits');
    Route::get('/my-credit/{id}', [CustomerController::class, 'creditDetail'])->name('customer.credit.detail');
    Route::post('/credit/pay-cuota/{id}', [CustomerController::class, 'payCuota'])->name('customer.credit.pay');

    // Perfil
    Route::get('/profile', [CustomerController::class, 'profile'])->name('customer.profile');

    // Verificación de Crédito
    Route::get('/verificar-credito', [CustomerController::class, 'verificarCredito'])->name('customer.verificar-credito');
    Route::post('/verificar-credito', [CustomerController::class, 'subirDocumentos'])->name('customer.subir-documentos');

    // Pagos
    Route::get('/payment/confirm/{id}', [\App\Http\Controllers\PaymentController::class, 'confirm'])->name('payment.confirm');
    Route::get('/payment/check-status/{id}', [\App\Http\Controllers\PaymentController::class, 'checkStatus'])->name('payment.check-status');
});

// ====================================
// RUTAS ADMIN (Propietario/Empleado o usuarios con permisos administrativos)
// ====================================

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard (solo propietario y empleado)
    Route::middleware('dashboard')->group(function () {
        Route::get('/dashboard', function () {
            $stats = [
                'productos' => \App\Models\Producto::count(),
                'ventas' => \App\Models\Venta::count(),
                'clientes' => \App\Models\Cliente::count(),
                'creditos' => \App\Models\Credito::where('estado', 'activo')->count(),
                'ventas_hoy' => \App\Models\Venta::whereDate('fecha', today())->count(),
                'ventas_mes' => \App\Models\Venta::whereMonth('fecha', now()->month)->count(),
            ];
            return Inertia::render('Admin/Dashboard', ['stats' => $stats]);
        })->name('dashboard');
    });

    // Página de bienvenida para usuarios con permisos personalizados
    Route::get('/bienvenida', function () {
        /** @var \App\Models\Usuario $user */
        $user = Auth::user();

        // Obtener módulos a los que tiene acceso
        $permisos = $user->getPermisosSlugs();
        $modulosDisponibles = [];
        $modulosAdmin = [
            'productos' => ['nombre' => 'Productos', 'icon' => '📦'],
            'categorias' => ['nombre' => 'Categorías', 'icon' => '🏷️'],
            'ventas' => ['nombre' => 'Ventas', 'icon' => '💰'],
            'compras' => ['nombre' => 'Compras', 'icon' => '🛒'],
            'clientes' => ['nombre' => 'Clientes', 'icon' => '👥'],
            'proveedores' => ['nombre' => 'Proveedores', 'icon' => '🏢'],
            'inventario' => ['nombre' => 'Inventario', 'icon' => '📊'],
            'creditos' => ['nombre' => 'Créditos', 'icon' => '💳'],
            'usuarios' => ['nombre' => 'Usuarios', 'icon' => '👤'],
            'roles' => ['nombre' => 'Roles', 'icon' => '🔐'],
            'empleados' => ['nombre' => 'Empleados', 'icon' => '👔'],
            'estadisticas' => ['nombre' => 'Estadísticas', 'icon' => '📈'],
        ];

        foreach ($permisos as $permiso) {
            foreach ($modulosAdmin as $modulo => $info) {
                if (str_starts_with($permiso, $modulo . '.')) {
                    if (!in_array($modulo, array_column($modulosDisponibles, 'slug'))) {
                        $modulosDisponibles[] = [
                            'slug' => $modulo,
                            'nombre' => $info['nombre'],
                            'icon' => $info['icon'],
                            'ruta' => "/admin/{$modulo}"
                        ];
                    }
                }
            }
        }

        return Inertia::render('Admin/Bienvenida', [
            'usuario' => [
                'nombre' => $user->nombre,
                'rol' => $user->rol ? $user->rol->nombre : null,
            ],
            'modulosDisponibles' => $modulosDisponibles
        ]);
    })->name('bienvenida');

    // Estadísticas y Reportes (solo propietario)
    Route::middleware('role:propietario')->group(function () {
        Route::get('estadisticas', [\App\Http\Controllers\Admin\EstadisticasController::class, 'index'])->name('estadisticas.index');
    });

    // Gestión de Productos
    Route::resource('productos', ProductoController::class);

    // Gestión de Categorías
    Route::resource('categorias', CategoriaController::class);

    // Gestión de Clientes
    // IMPORTANTE: Las rutas específicas deben ir ANTES de Route::resource para evitar conflictos
    Route::get('clientes/verificar-documentos', [ClienteController::class, 'verificarDocumentos'])->name('clientes.verificar-documentos');
    Route::post('clientes/{id}/aprobar-documentos', [ClienteController::class, 'aprobarDocumentos'])->name('clientes.aprobar-documentos');
    Route::post('clientes/{id}/rechazar-documentos', [ClienteController::class, 'rechazarDocumentos'])->name('clientes.rechazar-documentos');
    Route::post('clientes/{id}/toggle-credit', [ClienteController::class, 'toggleCredit'])->name('clientes.toggle-credit');
    Route::resource('clientes', ClienteController::class);

    // Gestión de Proveedores
    Route::resource('proveedores', ProveedorController::class);

    // Gestión de Ventas
    Route::get('ventas/buscar-clientes', [VentaController::class, 'buscarClientes'])->name('ventas.buscar-clientes');
    Route::resource('ventas', VentaController::class);

    // Gestión de Compras
    Route::resource('compras', CompraController::class);
    Route::post('compras/{id}/validar', [CompraController::class, 'validar'])->name('compras.validar');
    Route::post('compras/{id}/cancelar', [CompraController::class, 'cancelar'])->name('compras.cancelar');

    // Gestión de Inventario
    Route::get('inventario', [InventarioController::class, 'index'])->name('inventario.index');
    Route::get('inventario/movimientos', [InventarioController::class, 'movimientos'])->name('inventario.movimientos');
    Route::post('inventario/ajuste', [InventarioController::class, 'ajuste'])->name('inventario.ajuste');
    Route::get('inventario/kardex/{producto_id}', [InventarioController::class, 'kardex'])->name('inventario.kardex');

    // Gestión de Créditos
    Route::resource('creditos', CreditoController::class);
    Route::post('creditos/{id}/registrar-pago', [CreditoController::class, 'registrarPago'])->name('creditos.registrar-pago');

    // Gestión de Usuarios (solo propietario)
    Route::middleware('role:propietario')->group(function () {
        Route::resource('usuarios', UsuarioController::class);
        Route::resource('roles', RolController::class);

        // Gestión de Contadores
        Route::get('contadores', [\App\Http\Controllers\Admin\ContadorController::class, 'index'])->name('contadores.index');
        Route::post('contadores/sincronizar', [\App\Http\Controllers\Admin\ContadorController::class, 'sincronizar'])->name('contadores.sincronizar');
        Route::put('contadores/{id}', [\App\Http\Controllers\Admin\ContadorController::class, 'actualizar'])->name('contadores.update');
    });
});
