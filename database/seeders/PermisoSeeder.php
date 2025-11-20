<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permiso;

class PermisoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permisos = [
            // ============================================
            // MÓDULO: PRODUCTOS
            // ============================================
            ['nombre' => 'Listar Productos', 'slug' => 'productos.listar', 'descripcion' => 'Ver la lista de productos', 'modulo' => 'Productos'],
            ['nombre' => 'Ver Producto', 'slug' => 'productos.ver', 'descripcion' => 'Ver detalles de un producto', 'modulo' => 'Productos'],
            ['nombre' => 'Crear Producto', 'slug' => 'productos.crear', 'descripcion' => 'Crear nuevos productos', 'modulo' => 'Productos'],
            ['nombre' => 'Editar Producto', 'slug' => 'productos.editar', 'descripcion' => 'Editar productos existentes', 'modulo' => 'Productos'],
            ['nombre' => 'Eliminar Producto', 'slug' => 'productos.eliminar', 'descripcion' => 'Eliminar productos', 'modulo' => 'Productos'],

            // ============================================
            // MÓDULO: CATEGORÍAS
            // ============================================
            ['nombre' => 'Listar Categorías', 'slug' => 'categorias.listar', 'descripcion' => 'Ver la lista de categorías', 'modulo' => 'Categorías'],
            ['nombre' => 'Ver Categoría', 'slug' => 'categorias.ver', 'descripcion' => 'Ver detalles de una categoría', 'modulo' => 'Categorías'],
            ['nombre' => 'Crear Categoría', 'slug' => 'categorias.crear', 'descripcion' => 'Crear nuevas categorías', 'modulo' => 'Categorías'],
            ['nombre' => 'Editar Categoría', 'slug' => 'categorias.editar', 'descripcion' => 'Editar categorías existentes', 'modulo' => 'Categorías'],
            ['nombre' => 'Eliminar Categoría', 'slug' => 'categorias.eliminar', 'descripcion' => 'Eliminar categorías', 'modulo' => 'Categorías'],

            // ============================================
            // MÓDULO: VENTAS
            // ============================================
            ['nombre' => 'Listar Ventas', 'slug' => 'ventas.listar', 'descripcion' => 'Ver la lista de ventas', 'modulo' => 'Ventas'],
            ['nombre' => 'Ver Venta', 'slug' => 'ventas.ver', 'descripcion' => 'Ver detalles de una venta', 'modulo' => 'Ventas'],
            ['nombre' => 'Crear Venta', 'slug' => 'ventas.crear', 'descripcion' => 'Registrar nuevas ventas', 'modulo' => 'Ventas'],
            ['nombre' => 'Editar Venta', 'slug' => 'ventas.editar', 'descripcion' => 'Editar ventas existentes', 'modulo' => 'Ventas'],
            ['nombre' => 'Eliminar Venta', 'slug' => 'ventas.eliminar', 'descripcion' => 'Eliminar ventas', 'modulo' => 'Ventas'],

            // ============================================
            // MÓDULO: COMPRAS
            // ============================================
            ['nombre' => 'Listar Compras', 'slug' => 'compras.listar', 'descripcion' => 'Ver la lista de compras', 'modulo' => 'Compras'],
            ['nombre' => 'Ver Compra', 'slug' => 'compras.ver', 'descripcion' => 'Ver detalles de una compra', 'modulo' => 'Compras'],
            ['nombre' => 'Crear Compra', 'slug' => 'compras.crear', 'descripcion' => 'Registrar nuevas compras', 'modulo' => 'Compras'],
            ['nombre' => 'Editar Compra', 'slug' => 'compras.editar', 'descripcion' => 'Editar compras existentes', 'modulo' => 'Compras'],
            ['nombre' => 'Eliminar Compra', 'slug' => 'compras.eliminar', 'descripcion' => 'Eliminar compras', 'modulo' => 'Compras'],
            ['nombre' => 'Validar Compra', 'slug' => 'compras.validar', 'descripcion' => 'Validar compras pendientes', 'modulo' => 'Compras'],
            ['nombre' => 'Cancelar Compra', 'slug' => 'compras.cancelar', 'descripcion' => 'Cancelar compras', 'modulo' => 'Compras'],
            ['nombre' => 'Ver Compras Propias', 'slug' => 'compras.ver_propias', 'descripcion' => 'Ver solo las compras donde el proveedor está involucrado', 'modulo' => 'Compras'],

            // ============================================
            // MÓDULO: CLIENTES
            // ============================================
            ['nombre' => 'Listar Clientes', 'slug' => 'clientes.listar', 'descripcion' => 'Ver la lista de clientes', 'modulo' => 'Clientes'],
            ['nombre' => 'Ver Cliente', 'slug' => 'clientes.ver', 'descripcion' => 'Ver detalles de un cliente', 'modulo' => 'Clientes'],
            ['nombre' => 'Crear Cliente', 'slug' => 'clientes.crear', 'descripcion' => 'Registrar nuevos clientes', 'modulo' => 'Clientes'],
            ['nombre' => 'Editar Cliente', 'slug' => 'clientes.editar', 'descripcion' => 'Editar clientes existentes', 'modulo' => 'Clientes'],
            ['nombre' => 'Eliminar Cliente', 'slug' => 'clientes.eliminar', 'descripcion' => 'Eliminar clientes', 'modulo' => 'Clientes'],

            // ============================================
            // MÓDULO: PROVEEDORES
            // ============================================
            ['nombre' => 'Listar Proveedores', 'slug' => 'proveedores.listar', 'descripcion' => 'Ver la lista de proveedores', 'modulo' => 'Proveedores'],
            ['nombre' => 'Ver Proveedor', 'slug' => 'proveedores.ver', 'descripcion' => 'Ver detalles de un proveedor', 'modulo' => 'Proveedores'],
            ['nombre' => 'Crear Proveedor', 'slug' => 'proveedores.crear', 'descripcion' => 'Registrar nuevos proveedores', 'modulo' => 'Proveedores'],
            ['nombre' => 'Editar Proveedor', 'slug' => 'proveedores.editar', 'descripcion' => 'Editar proveedores existentes', 'modulo' => 'Proveedores'],
            ['nombre' => 'Eliminar Proveedor', 'slug' => 'proveedores.eliminar', 'descripcion' => 'Eliminar proveedores', 'modulo' => 'Proveedores'],

            // ============================================
            // MÓDULO: INVENTARIO
            // ============================================
            ['nombre' => 'Listar Inventario', 'slug' => 'inventario.listar', 'descripcion' => 'Ver el inventario de productos', 'modulo' => 'Inventario'],
            ['nombre' => 'Ver Movimientos', 'slug' => 'inventario.ver', 'descripcion' => 'Ver movimientos de inventario', 'modulo' => 'Inventario'],
            ['nombre' => 'Registrar Movimiento', 'slug' => 'inventario.crear', 'descripcion' => 'Registrar movimientos de inventario (ingreso/salida)', 'modulo' => 'Inventario'],
            ['nombre' => 'Ajustar Stock', 'slug' => 'inventario.ajustar', 'descripcion' => 'Ajustar stock de productos', 'modulo' => 'Inventario'],

            // ============================================
            // MÓDULO: CRÉDITOS
            // ============================================
            ['nombre' => 'Listar Créditos', 'slug' => 'creditos.listar', 'descripcion' => 'Ver la lista de créditos', 'modulo' => 'Créditos'],
            ['nombre' => 'Ver Crédito', 'slug' => 'creditos.ver', 'descripcion' => 'Ver detalles de un crédito', 'modulo' => 'Créditos'],
            ['nombre' => 'Registrar Pago', 'slug' => 'creditos.pagos', 'descripcion' => 'Registrar pagos de créditos', 'modulo' => 'Créditos'],
            ['nombre' => 'Gestionar Cuotas', 'slug' => 'creditos.cuotas', 'descripcion' => 'Gestionar cuotas de créditos', 'modulo' => 'Créditos'],

            // ============================================
            // MÓDULO: USUARIOS
            // ============================================
            ['nombre' => 'Listar Usuarios', 'slug' => 'usuarios.listar', 'descripcion' => 'Ver la lista de usuarios', 'modulo' => 'Usuarios'],
            ['nombre' => 'Ver Usuario', 'slug' => 'usuarios.ver', 'descripcion' => 'Ver detalles de un usuario', 'modulo' => 'Usuarios'],
            ['nombre' => 'Crear Usuario', 'slug' => 'usuarios.crear', 'descripcion' => 'Crear nuevos usuarios', 'modulo' => 'Usuarios'],
            ['nombre' => 'Editar Usuario', 'slug' => 'usuarios.editar', 'descripcion' => 'Editar usuarios existentes', 'modulo' => 'Usuarios'],
            ['nombre' => 'Eliminar Usuario', 'slug' => 'usuarios.eliminar', 'descripcion' => 'Eliminar usuarios', 'modulo' => 'Usuarios'],

            // ============================================
            // MÓDULO: ROLES
            // ============================================
            ['nombre' => 'Listar Roles', 'slug' => 'roles.listar', 'descripcion' => 'Ver la lista de roles', 'modulo' => 'Roles'],
            ['nombre' => 'Ver Rol', 'slug' => 'roles.ver', 'descripcion' => 'Ver detalles de un rol', 'modulo' => 'Roles'],
            ['nombre' => 'Crear Rol', 'slug' => 'roles.crear', 'descripcion' => 'Crear nuevos roles', 'modulo' => 'Roles'],
            ['nombre' => 'Editar Rol', 'slug' => 'roles.editar', 'descripcion' => 'Editar roles existentes y asignar permisos', 'modulo' => 'Roles'],
            ['nombre' => 'Eliminar Rol', 'slug' => 'roles.eliminar', 'descripcion' => 'Eliminar roles', 'modulo' => 'Roles'],

            // ============================================
            // MÓDULO: ESTADÍSTICAS
            // ============================================
            ['nombre' => 'Ver Estadísticas', 'slug' => 'estadisticas.ver', 'descripcion' => 'Ver estadísticas y reportes del sistema', 'modulo' => 'Estadísticas'],
            ['nombre' => 'Exportar Reportes', 'slug' => 'estadisticas.exportar', 'descripcion' => 'Exportar reportes a Excel', 'modulo' => 'Estadísticas'],

            // ============================================
            // MÓDULO: DASHBOARD
            // ============================================
            ['nombre' => 'Ver Dashboard', 'slug' => 'dashboard.ver', 'descripcion' => 'Acceder al panel de control', 'modulo' => 'Dashboard'],
        ];

        foreach ($permisos as $permiso) {
            Permiso::updateOrCreate(
                ['slug' => $permiso['slug']],
                $permiso
            );
        }

        $this->command->info('✅ ' . count($permisos) . ' permisos creados/actualizados exitosamente.');
    }
}
