# Arquitectura del Sistema E-Commerce Licorería

## Descripción General

Sistema completo de gestión y e-commerce para una licorería con:
- **Frontend Público**: Catálogo de productos, registro, login, carrito de compras
- **Backend Administrativo**: Gestión completa del negocio
- **Sistemas de Pago**: Contado y crédito con validación
- **Control de Inventario**: Movimientos automáticos por compras y ventas
- **Sistema de Créditos**: Gestión de cuotas y pagos

---

## Módulos del Sistema

### 1. AUTENTICACIÓN Y AUTORIZACIÓN

#### Roles del Sistema:
1. **Propietario** (Admin Total)
   - Acceso completo a todas las funcionalidades
   - Gestión de usuarios, roles
   - Reportes y estadísticas

2. **Vendedor**
   - Registro de ventas presenciales
   - Gestión de clientes
   - Consulta de inventario

3. **Cliente**
   - Navegación del catálogo
   - Registro y login
   - Compras online (contado/crédito)
   - Historial de compras y créditos

#### Migraciones Necesarias:
```
- usuario (ya existe) + campos de autenticación
- rol (ya existe)
- password_resets
- sessions
```

---

### 2. MÓDULO PÚBLICO (E-COMMERCE)

#### 2.1 Catálogo de Productos

**Funcionalidades:**
- Listado de productos con paginación
- Filtros por categoría, precio, marca
- Búsqueda de productos
- Vista detallada del producto
- Visualización por: items/edades/modo día-noche

**Páginas Vue:**
```
/resources/js/Pages/Shop/
├── Index.vue (Catálogo)
├── ProductDetail.vue (Detalle producto)
├── Cart.vue (Carrito)
└── Checkout.vue (Proceso de compra)
```

#### 2.2 Sistema de Carrito

**Tablas:**
```sql
CREATE TABLE cart (
    id SERIAL PRIMARY KEY,
    session_id VARCHAR(255),
    user_id INT REFERENCES usuario(id),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE cart_items (
    id SERIAL PRIMARY KEY,
    cart_id INT REFERENCES cart(id),
    producto_id INT REFERENCES producto(id),
    cantidad INT,
    precio NUMERIC(10,2),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Funcionalidades:**
- Agregar/quitar productos
- Actualizar cantidades
- Persistencia por sesión (invitados)
- Asociación a usuario (logueados)
- Descuento automático de stock al finalizar

#### 2.3 Proceso de Checkout

**Flujo Pago al Contado:**
1. Cliente selecciona productos → carrito
2. Procede al checkout
3. Selecciona "Pago online/contado"
4. Confirma la orden
5. Sistema:
   - Crea registro en `venta` (tipo: "contado", estado: "completado")
   - Crea registros en `detalle_venta`
   - Genera movimiento de inventario (salida)
   - Descuenta stock
   - Limpia carrito
   - Genera factura/recibo

**Flujo Pago a Crédito (2 cuotas):**
1. Cliente selecciona productos → carrito
2. Procede al checkout
3. Selecciona "Pago a Crédito (2 cuotas)"
4. **Validación**: Solo clientes verificados/aprobados
5. Sistema:
   - Crea registro en `venta` (tipo: "credito", estado: "pendiente")
   - Calcula saldo y monto_total
   - Crea registro en `credito` con 2 cuotas
   - Genera las 2 cuotas con fechas
   - Crea registros en `detalle_venta`
   - Genera movimiento de inventario (salida)
   - Descuenta stock
   - Cliente puede ver crédito en "Mis créditos"

**Política de Garantía para Créditos:**
- Cliente debe estar registrado y verificado
- Límite de crédito por cliente
- Máximo 1 crédito activo a la vez
- Si no paga una cuota en fecha → marca crédito "en mora"
- Cliente en mora no puede hacer nuevas compras a crédito

---

### 3. MÓDULO DE COMPRAS A PROVEEDORES

#### Flujo de Compra:

**Paso 1: Crear Orden de Compra**
```
Propietario:
1. Va a "Compras" → "Nueva Orden de Compra"
2. Selecciona proveedor
3. Agrega productos al pedido (producto, cantidad, precio)
4. Guarda como "orden_pendiente"
```

**Tabla nueva sugerida:**
```sql
CREATE TABLE orden_compra (
    id SERIAL PRIMARY KEY,
    nro_orden VARCHAR(20),
    proveedor_id INT REFERENCES proveedor(id),
    estado VARCHAR(20), -- pendiente, validada, cancelada
    fecha_orden DATE,
    fecha_validacion DATE,
    usuario_id INT REFERENCES usuario(id),
    total NUMERIC(10,2)
);
```

**Paso 2: Validar Compra**
```
Propietario:
1. Va a "Órdenes de Compra"
2. Selecciona orden pendiente
3. Valida/aprueba la orden
4. Sistema:
   - Cambia estado a "validada"
   - Crea registro en tabla `compra`
   - Crea registros en `detalle_compra`
   - Genera movimiento en `inventario` con glosa:
     "Ingreso por compra #[nro_compra] del proveedor [nombre_proveedor]"
   - Incrementa stock_actual
```

---

### 4. MÓDULO DE INVENTARIO

#### Tipos de Movimiento:

1. **INGRESO** - Por compras a proveedores
   - Glosa: "Compra #123 de Proveedor XYZ"
   - Aumenta stock

2. **SALIDA** - Por ventas
   - Glosa: "Venta #456 a Cliente ABC"
   - Disminuye stock

3. **AJUSTE** - Correcciones manuales
   - Glosa: "Ajuste por inventario físico"

#### Estructura de Inventario:

```sql
-- Ya existe, mejorar:
ALTER TABLE inventario ADD COLUMN producto_id INT REFERENCES producto(id);
```

**Tracking de Stock por Producto:**
- Cada producto tiene un `stock_actual` virtual (calculado)
- Los movimientos de inventario registran cambios
- Método de inventario: FIFO o Promedio Ponderado

---

### 5. MÓDULO DE CRÉDITOS Y PAGOS

#### Gestión de Créditos:

**Para el Cliente:**
- Ve sus créditos activos en "Mis Créditos"
- Ve cuotas pendientes, pagadas y en mora
- Puede realizar pagos de cuotas online

**Para el Propietario:**
- Lista de todos los créditos
- Filtros: activo, pagado, en mora
- Registro manual de pagos (efectivo, transferencia)
- Marcado automático de moras

#### Tabla de Cuotas (Nueva):
```sql
CREATE TABLE cuotas (
    id SERIAL PRIMARY KEY,
    credito_id INT REFERENCES credito(id),
    numero_cuota INT,
    monto NUMERIC(10,2),
    fecha_vencimiento DATE,
    fecha_pago DATE NULL,
    estado VARCHAR(20), -- pendiente, pagada, mora
    created_at TIMESTAMP
);
```

#### Proceso de Pago de Cuota:
1. Cliente selecciona cuota pendiente
2. Realiza pago (QR, tarjeta, efectivo)
3. Sistema:
   - Crea registro en `pagos`
   - Marca cuota como "pagada"
   - Actualiza `saldo` en `credito`
   - Si saldo = 0 → marca crédito como "pagado"
   - Si fecha > vencimiento → marca "mora"

---

### 6. MÓDULO ADMINISTRATIVO (PROPIETARIO)

#### 6.1 Dashboard Principal

**Métricas:**
- Ventas del día/mes
- Productos más vendidos
- Clientes con créditos activos
- Stock bajo/agotado
- Créditos en mora

#### 6.2 Gestión de Entidades (CRUD)

1. **Productos**
   - Crear, editar, eliminar
   - Asignar categoría
   - Control de stock mínimo
   - Imágenes de producto

2. **Categorías**
   - Gestión de categorías de licores

3. **Clientes**
   - Listado completo
   - Editar información
   - Ver historial de compras
   - Ver créditos
   - Aprobar/desaprobar para crédito

4. **Proveedores**
   - CRUD proveedores
   - Historial de compras

5. **Usuarios (Propietario, Vendedores)**
   - Crear vendedores
   - Asignar roles

6. **Ventas**
   - Historial completo
   - Filtros por fecha, tipo, estado
   - Ver detalles

7. **Compras**
   - Historial de órdenes
   - Estado de órdenes

8. **Inventario**
   - Ver movimientos
   - Ajustes manuales
   - Kardex por producto

#### 6.3 Reportes y Estadísticas

**Reportes:**
1. Ventas por período
2. Productos más vendidos
3. Clientes frecuentes
4. Estado de créditos
5. Inventario actual
6. Movimientos de inventario
7. Compras a proveedores
8. Análisis de moras

---

## Estructura de Rutas

### Rutas Públicas (Sin Autenticación)

```php
// Tienda pública
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/product/{id}', [ShopController::class, 'show'])->name('shop.product');
Route::get('/shop/category/{id}', [ShopController::class, 'category'])->name('shop.category');

// Carrito (sesión)
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::put('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
```

### Rutas Autenticadas (Cliente)

```php
Route::middleware(['auth', 'role:cliente'])->group(function () {
    // Checkout
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');

    // Mis compras
    Route::get('/my-orders', [CustomerController::class, 'orders'])->name('customer.orders');
    Route::get('/my-order/{id}', [CustomerController::class, 'orderDetail'])->name('customer.order.detail');

    // Mis créditos
    Route::get('/my-credits', [CustomerController::class, 'credits'])->name('customer.credits');
    Route::post('/credit/pay/{id}', [CreditController::class, 'payCuota'])->name('credit.pay');
});
```

### Rutas Admin (Propietario/Vendedor)

```php
Route::middleware(['auth', 'role:propietario|vendedor'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // CRUDs (ya definidos)
    Route::resource('productos', ProductoController::class);
    Route::resource('categorias', CategoriaController::class);
    Route::resource('clientes', ClienteController::class);
    Route::resource('proveedores', ProveedorController::class);
    Route::resource('ventas', VentaController::class);
    Route::resource('usuarios', UsuarioController::class);

    // Compras y órdenes
    Route::resource('ordenes-compra', OrdenCompraController::class);
    Route::post('/ordenes-compra/{id}/validar', [OrdenCompraController::class, 'validar']);
    Route::resource('compras', CompraController::class);

    // Inventario
    Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario.index');
    Route::get('/inventario/movimientos', [InventarioController::class, 'movimientos']);
    Route::post('/inventario/ajuste', [InventarioController::class, 'ajuste']);
    Route::get('/inventario/kardex/{producto_id}', [InventarioController::class, 'kardex']);

    // Créditos
    Route::get('/creditos', [CreditoController::class, 'index'])->name('creditos.index');
    Route::get('/creditos/{id}', [CreditoController::class, 'show']);
    Route::post('/creditos/{id}/registrar-pago', [CreditoController::class, 'registrarPago']);

    // Reportes
    Route::get('/reportes/ventas', [ReporteController::class, 'ventas']);
    Route::get('/reportes/productos', [ReporteController::class, 'productos']);
    Route::get('/reportes/clientes', [ReporteController::class, 'clientes']);
    Route::get('/reportes/creditos', [ReporteController::class, 'creditos']);
    Route::get('/reportes/inventario', [ReporteController::class, 'inventario']);
});
```

---

## Componentes Vue Principales

### Área Pública

```
/resources/js/Pages/
├── Auth/
│   ├── Login.vue
│   ├── Register.vue
│   └── ForgotPassword.vue
│
├── Shop/
│   ├── Index.vue (Catálogo)
│   ├── ProductDetail.vue
│   ├── Cart.vue
│   ├── Checkout.vue
│   └── OrderConfirmation.vue
│
├── Customer/
│   ├── MyOrders.vue
│   ├── OrderDetail.vue
│   ├── MyCredits.vue
│   └── Profile.vue
```

### Área Admin

```
├── Admin/
│   ├── Dashboard.vue
│   │
│   ├── Productos/
│   │   ├── Index.vue
│   │   ├── Create.vue
│   │   ├── Edit.vue
│   │   └── Show.vue
│   │
│   ├── Clientes/
│   │   ├── Index.vue
│   │   ├── Show.vue (con historial)
│   │   └── Edit.vue
│   │
│   ├── Compras/
│   │   ├── OrdenesIndex.vue
│   │   ├── CreateOrden.vue
│   │   ├── ValidarOrden.vue
│   │   └── ComprasIndex.vue
│   │
│   ├── Inventario/
│   │   ├── Index.vue
│   │   ├── Movimientos.vue
│   │   ├── Ajuste.vue
│   │   └── Kardex.vue
│   │
│   ├── Ventas/
│   │   ├── Index.vue
│   │   ├── Show.vue
│   │   └── Create.vue (POS)
│   │
│   ├── Creditos/
│   │   ├── Index.vue
│   │   ├── Show.vue
│   │   └── RegistrarPago.vue
│   │
│   └── Reportes/
│       ├── Ventas.vue
│       ├── Productos.vue
│       ├── Clientes.vue
│       ├── Creditos.vue
│       └── Inventario.vue
```

### Componentes Reutilizables

```
/resources/js/Components/
├── ProductCard.vue
├── ProductGrid.vue
├── CartItem.vue
├── CategoryFilter.vue
├── SearchBar.vue
├── Pagination.vue
├── DataTable.vue
├── Modal.vue
├── Alert.vue
├── Button.vue
├── FormInput.vue
├── CreditStatus.vue
└── InventoryStatus.vue
```

---

## Modelos y Relaciones Eloquent

### Usuario (Modelo Principal de Auth)

```php
class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuario';

    protected $fillable = [
        'nombre', 'email', 'password', 'correo',
        'estado', 'id_rol'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relaciones
    public function rol() {
        return $this->belongsTo(Rol::class, 'id_rol');
    }

    public function cliente() {
        return $this->hasOne(Cliente::class, 'usuario_id');
    }

    public function vendedor() {
        return $this->hasOne(Vendedor::class, 'usuario_id');
    }

    // Helpers
    public function hasRole($role) {
        return $this->rol->nombre === $role;
    }

    public function isCliente() {
        return $this->hasRole('cliente');
    }

    public function isPropietario() {
        return $this->hasRole('propietario');
    }
}
```

---

## Servicios de Negocio

### CartService

```php
class CartService
{
    public function getCart();
    public function addItem($productId, $quantity);
    public function updateItem($itemId, $quantity);
    public function removeItem($itemId);
    public function clear();
    public function total();
    public function mergeCarts($sessionCart, $userCart);
}
```

### CheckoutService

```php
class CheckoutService
{
    public function processContado($cart, $cliente);
    public function processCredito($cart, $cliente, $cuotas = 2);
    public function validateCreditEligibility($cliente);
    private function createVenta($data);
    private function createDetalleVenta($venta, $items);
    private function updateInventory($items, $type = 'salida');
}
```

### InventoryService

```php
class InventoryService
{
    public function registrarMovimiento($data);
    public function calcularStock($productoId);
    public function ajustarStock($productoId, $cantidad, $glosa);
    public function getKardex($productoId, $fechaInicio, $fechaFin);
}
```

### CreditService

```php
class CreditService
{
    public function createCredit($venta, $cuotas);
    public function generateCuotas($credito, $numCuotas);
    public function registerPayment($cuotaId, $monto, $metodo);
    public function checkMoras();
    public function updateCreditStatus($creditoId);
}
```

---

## Plan de Implementación

### Fase 1: Base y Autenticación ✅
- [x] Migraciones base
- [x] Modelos
- [x] Configuración Inertia
- [ ] Sistema de autenticación completo
- [ ] Middleware de roles

### Fase 2: E-Commerce Público
- [ ] Catálogo de productos
- [ ] Sistema de carrito
- [ ] Checkout (contado y crédito)
- [ ] Área de cliente

### Fase 3: Admin - Gestión Básica
- [ ] CRUD Productos
- [ ] CRUD Categorías
- [ ] CRUD Clientes
- [ ] CRUD Proveedores

### Fase 4: Compras e Inventario
- [ ] Sistema de órdenes de compra
- [ ] Validación de compras
- [ ] Movimientos de inventario
- [ ] Kardex

### Fase 5: Créditos y Pagos
- [ ] Gestión de créditos
- [ ] Sistema de cuotas
- [ ] Pagos de cuotas
- [ ] Control de moras

### Fase 6: Reportes
- [ ] Dashboard con métricas
- [ ] Reportes de ventas
- [ ] Reportes de inventario
- [ ] Reportes de créditos

---

## Próximos Pasos Inmediatos

1. ✅ Instalar dependencias (Breeze, Inertia, Vue)
2. ✅ Crear migraciones base
3. Ejecutar migraciones
4. Configurar modelo Usuario como Authenticatable
5. Crear seeders para roles y usuario admin
6. Implementar autenticación
7. Crear middleware de roles
8. Comenzar con la tienda pública

---

**Nota:** Este es un proyecto de gran escala. Se recomienda desarrollo iterativo por fases.
