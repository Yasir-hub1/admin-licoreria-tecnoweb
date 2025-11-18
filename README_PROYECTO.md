# Sistema E-Commerce Licorería - Tecnoweb

## Descripción

Sistema completo de gestión y comercio electrónico para una licorería, desarrollado con Laravel 11, Inertia.js y Vue 3.

### Características Principales

- **E-commerce Público**: Catálogo de productos, carrito de compras, checkout
- **Sistema de Créditos**: Compras a crédito con cuotas y control de moras
- **Gestión Administrativa**: CRUD completo de todas las entidades
- **Control de Inventario**: Movimientos automáticos, kardex
- **Compras a Proveedores**: Órdenes de compra y validación
- **Reportes y Estadísticas**: Análisis de ventas, inventario, créditos
- **Multi-rol**: Propietario, Vendedor, Cliente

---

## Stack Tecnológico

- **Backend**: Laravel 11
- **Frontend**: Vue 3 + Inertia.js
- **Build Tool**: Vite
- **Estilos**: Tailwind CSS
- **Base de Datos**: MySQL / PostgreSQL
- **Autenticación**: Laravel Breeze (adaptado)

---

## Estructura del Proyecto

```
tecnoweb-herika/
├── app/
│   ├── Http/
│   │   ├── Controllers/        # 14 controladores + Auth
│   │   └── Middleware/         # HandleInertiaRequests, RoleMiddleware
│   └── Models/                 # 14 modelos con relaciones
├── database/
│   ├── migrations/             # 14+ migraciones
│   └── seeders/                # Seeders para datos iniciales
├── resources/
│   ├── js/
│   │   ├── Pages/              # Componentes Vue de páginas
│   │   │   ├── Auth/           # Login, Register
│   │   │   ├── Shop/           # Catálogo público
│   │   │   ├── Admin/          # Panel administrativo
│   │   │   └── Customer/       # Área de cliente
│   │   ├── Components/         # Componentes reutilizables
│   │   ├── Layouts/            # Layouts de la app
│   │   └── app.js              # Entry point
│   └── views/
│       └── app.blade.php       # Template raíz Inertia
├── routes/
│   └── web.php                 # Rutas del sistema
├── ARQUITECTURA_SISTEMA.md     # Documentación de arquitectura
├── GUIA_IMPLEMENTACION.md      # Guía paso a paso
└── INERTIA_USAGE.md            # Manual de Inertia.js
```

---

## Modelos y Entidades

### Entidades Principales

1. **Rol** - Roles del sistema (propietario, vendedor, cliente)
2. **Usuario** - Usuarios del sistema (con autenticación)
3. **Cliente** - Información de clientes
4. **Vendedor** - Información de vendedores
5. **Proveedor** - Proveedores de productos
6. **Categoria** - Categorías de productos
7. **Producto** - Productos de la licorería
8. **Compra** - Compras a proveedores
9. **DetalleCompra** - Detalle de compras
10. **Venta** - Ventas realizadas
11. **DetalleVenta** - Detalle de ventas
12. **Inventario** - Movimientos de inventario
13. **Credito** - Créditos otorgados
14. **Pagos** - Pagos de créditos

### Relaciones Principales

```
Usuario → Rol (belongsTo)
Usuario → Cliente (hasOne)
Usuario → Vendedor (hasOne)

Producto → Categoria (belongsTo)
Producto → DetalleCompra (hasMany)
Producto → DetalleVenta (hasMany)

Compra → Proveedor (belongsTo)
Compra → DetalleCompra (hasMany)

Venta → Cliente (belongsTo)
Venta → Vendedor (belongsTo)
Venta → DetalleVenta (hasMany)
Venta → Credito (hasOne)

Credito → Venta (belongsTo)
Credito → Pagos (hasMany)

DetalleCompra → Inventario (hasMany)
```

---

## Casos de Uso Implementados

### CU1: Gestión de Usuarios

El propietario puede:
- Registrar proveedores, productos, categorías, usuarios
- Gestionar toda la información del sistema

### CU2: Gestión de Productos

- CRUD completo de productos
- Asignación de categorías
- Control de precios y stock

### CU3: Gestión de Compras

- Crear orden de compra a proveedor
- Validar compra
- Generar ingreso de inventario automático con glosa

### CU4: Gestión de Inventario

- Registro automático de ingresos (compras)
- Registro automático de salidas (ventas)
- Método de inventario
- Kardex por producto
- Ajustes manuales

### CU5: Gestión de Ventas

**Pago al Contado (online):**
1. Cliente selecciona productos
2. Agrega al carrito
3. Procede al checkout
4. Selecciona pago online (QR o tarjeta)
5. Sistema confirma pago y registra venta
6. Genera factura/recibo
7. Descuenta stock automáticamente
8. Cliente ve compra en "Mis compras"

**Pago a Crédito (2 cuotas, online):**
1. Solo clientes verificados
2. Cliente selecciona productos
3. Procede al checkout
4. Selecciona "Pago a Crédito (2 cuotas)"
5. Sistema valida elegibilidad
6. Crea venta tipo "crédito"
7. Registra crédito con 2 cuotas
8. Asigna fechas de vencimiento
9. Cliente ve crédito en "Mis créditos"
10. Puede pagar cuotas online

### CU6: Gestión de Créditos

- Ver créditos activos
- Ver cuotas (pendientes, pagadas, en mora)
- Registrar pagos de cuotas
- Control automático de moras
- Cliente en mora no puede comprar a crédito

### CU7: Gestión de Pagos

- Registro de pagos de cuotas
- Métodos: efectivo, transferencia, QR, tarjeta
- Actualización automática de saldo
- Marcado automático como "pagado" cuando saldo = 0

### CU8: Reportes y Estadísticas

- Ventas por período
- Productos más vendidos
- Clientes frecuentes
- Estado de créditos y moras
- Inventario actual
- Movimientos de inventario

---

## Flujos Principales

### Flujo de Compra a Proveedor

```
1. Propietario crea Orden de Compra
   ├─ Selecciona proveedor
   ├─ Agrega productos con cantidad y precio
   └─ Guarda orden (estado: "pendiente")

2. Propietario valida la Orden
   ├─ Revisa y aprueba
   └─ Sistema:
       ├─ Cambia estado a "validada"
       ├─ Crea registro en tabla Compra
       ├─ Crea registros en Detalle_Compra
       ├─ Genera movimiento Inventario (tipo: INGRESO)
       │   └─ Glosa: "Compra #123 de Proveedor XYZ"
       └─ Incrementa stock_actual
```

### Flujo de Venta al Contado

```
Cliente → Navega catálogo → Agrega al carrito → Checkout →
Selecciona "Pago Online" → Confirma →
Sistema:
  ├─ Crea Venta (tipo: "contado", estado: "completado")
  ├─ Crea Detalle_Venta
  ├─ Genera movimiento Inventario (tipo: SALIDA)
  │   └─ Glosa: "Venta #456 a Cliente ABC"
  ├─ Descuenta stock
  ├─ Limpia carrito
  └─ Genera recibo
```

### Flujo de Venta a Crédito

```
Cliente → Checkout → Selecciona "Crédito 2 cuotas" →
Sistema valida elegibilidad →
Sistema:
  ├─ Crea Venta (tipo: "credito", estado: "pendiente")
  ├─ Crea Detalle_Venta
  ├─ Crea Credito
  │   ├─ monto_total, saldo, numero_cuotas: 2
  │   └─ Genera 2 cuotas con fechas de vencimiento
  ├─ Genera movimiento Inventario (tipo: SALIDA)
  └─ Descuenta stock

Cliente → "Mis créditos" → Ve cuotas → Paga cuota →
Sistema:
  ├─ Registra en tabla Pagos
  ├─ Marca cuota como "pagada"
  ├─ Actualiza saldo del crédito
  └─ Si saldo = 0 → marca crédito "pagado"
```

---

## Instalación y Configuración

### Requisitos

- PHP >= 8.2
- Composer
- Node.js >= 18
- MySQL >= 8.0 o PostgreSQL >= 14

### Pasos de Instalación

```bash
# 1. Clonar repositorio
git clone <repo>
cd tecnoweb-herika

# 2. Instalar dependencias PHP
composer install

# 3. Instalar dependencias Node
npm install

# 4. Configurar .env
cp .env.example .env
php artisan key:generate

# 5. Configurar base de datos en .env
DB_CONNECTION=mysql
DB_DATABASE=tecnoweb_licoreria
DB_USERNAME=root
DB_PASSWORD=

# 6. Ejecutar migraciones
php artisan migrate

# 7. Ejecutar seeders
php artisan db:seed

# 8. Compilar assets
npm run dev

# 9. Iniciar servidor
php artisan serve
```

### Acceso Inicial

- **URL**: http://localhost:8000
- **Admin**: admin@licoreria.com / password123
- **Registro de clientes**: http://localhost:8000/register

---

## Rutas Principales

### Públicas

- `GET /` - Página de bienvenida
- `GET /login` - Login
- `GET /register` - Registro de clientes
- `GET /shop` - Catálogo de productos

### Clientes (Autenticados)

- `GET /shop` - Catálogo
- `GET /cart` - Carrito
- `GET /checkout` - Proceso de compra
- `GET /my-orders` - Mis compras
- `GET /my-credits` - Mis créditos

### Admin (Propietario/Vendedor)

- `GET /admin/dashboard` - Dashboard
- `GET /admin/productos` - Gestión de productos
- `GET /admin/clientes` - Gestión de clientes
- `GET /admin/ventas` - Gestión de ventas
- `GET /admin/compras` - Gestión de compras
- `GET /admin/inventario` - Gestión de inventario
- `GET /admin/creditos` - Gestión de créditos
- `GET /admin/reportes/*` - Reportes

---

## Documentación Adicional

- **[ARQUITECTURA_SISTEMA.md](ARQUITECTURA_SISTEMA.md)** - Arquitectura completa del sistema
- **[GUIA_IMPLEMENTACION.md](GUIA_IMPLEMENTACION.md)** - Guía paso a paso de implementación
- **[INERTIA_USAGE.md](INERTIA_USAGE.md)** - Manual de uso de Inertia.js

---

## Estado del Proyecto

### ✅ Completado

- Estructura base de migraciones y modelos
- Configuración de Inertia.js + Vue 3
- Sistema de autenticación
- Middleware de roles
- Páginas de login/register
- Documentación completa

### 🚧 En Desarrollo

- Catálogo de productos (Shop)
- Sistema de carrito
- Proceso de checkout
- CRUDs administrativos
- Sistema de créditos
- Gestión de inventario
- Reportes

---

## Comandos Útiles

```bash
# Desarrollo
npm run dev                    # Compilar assets en modo desarrollo
php artisan serve              # Iniciar servidor Laravel

# Producción
npm run build                  # Compilar assets para producción

# Base de datos
php artisan migrate            # Ejecutar migraciones
php artisan migrate:fresh --seed  # Reset DB y seeders
php artisan db:seed            # Solo seeders

# Cache
php artisan config:clear       # Limpiar cache de configuración
php artisan route:clear        # Limpiar cache de rutas
php artisan view:clear         # Limpiar cache de vistas

# Herramientas
php artisan route:list         # Listar todas las rutas
php artisan make:controller X  # Crear controlador
php artisan make:model X       # Crear modelo
php artisan make:migration X   # Crear migración
```

---

## Soporte y Contacto

Para preguntas o problemas:
- Ver documentación en `/docs`
- Revisar guías de implementación
- Consultar INERTIA_USAGE.md para dudas de Inertia

---

## Licencia

Este proyecto es parte del curso de Tecnoweb.

---

**Última actualización**: 2025-11-16
**Versión**: 1.0.0
