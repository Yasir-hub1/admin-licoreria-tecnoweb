<template>
    <MainLayout>
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold mb-6">Dashboard Administrativo</h1>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <DashboardCard
                    title="Productos"
                    :value="stats.productos || 0"
                    icon="📦"
                    link="/admin/productos"
                />
                <DashboardCard
                    title="Ventas"
                    :value="stats.ventas || 0"
                    icon="💰"
                    link="/admin/ventas"
                />
                <DashboardCard
                    title="Clientes"
                    :value="stats.clientes || 0"
                    icon="👥"
                    link="/admin/clientes"
                />
                <DashboardCard
                    title="Créditos Activos"
                    :value="stats.creditos || 0"
                    icon="💳"
                    link="/admin/creditos"
                />
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Accesos Rápidos</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <Link href="/admin/productos/create" class="p-4 border rounded-lg hover:bg-gray-50">
                            <div class="text-2xl mb-2">➕</div>
                            <div class="font-medium">Nuevo Producto</div>
                        </Link>
                        <Link href="/admin/ventas/create" class="p-4 border rounded-lg hover:bg-gray-50">
                            <div class="text-2xl mb-2">🛒</div>
                            <div class="font-medium">Nueva Venta</div>
                        </Link>
                        <Link href="/admin/compras/create" class="p-4 border rounded-lg hover:bg-gray-50">
                            <div class="text-2xl mb-2">📥</div>
                            <div class="font-medium">Nueva Compra</div>
                        </Link>
                        <Link href="/admin/inventario" class="p-4 border rounded-lg hover:bg-gray-50">
                            <div class="text-2xl mb-2">📊</div>
                            <div class="font-medium">Inventario</div>
                        </Link>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Módulos</h2>
                    <div class="space-y-2">
                        <Link href="/admin/productos" class="block p-3 border rounded hover:bg-gray-50">
                            Gestión de Productos
                        </Link>
                        <Link href="/admin/categorias" class="block p-3 border rounded hover:bg-gray-50">
                            Gestión de Categorías
                        </Link>
                        <Link href="/admin/clientes" class="block p-3 border rounded hover:bg-gray-50">
                            Gestión de Clientes
                        </Link>
                        <Link href="/admin/proveedores" class="block p-3 border rounded hover:bg-gray-50">
                            Gestión de Proveedores
                        </Link>
                        <Link href="/admin/ventas" class="block p-3 border rounded hover:bg-gray-50">
                            Gestión de Ventas
                        </Link>
                        <Link href="/admin/compras" class="block p-3 border rounded hover:bg-gray-50">
                            Gestión de Compras
                        </Link>
                        <Link href="/admin/creditos" class="block p-3 border rounded hover:bg-gray-50">
                            Gestión de Créditos
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import DashboardCard from '@/Components/DashboardCard.vue';

defineProps({
    stats: {
        type: Object,
        default: () => ({})
    }
});
</script>

