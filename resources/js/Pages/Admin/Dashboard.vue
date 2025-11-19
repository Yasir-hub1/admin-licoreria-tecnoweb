<template>
    <AdminLayout title="Dashboard">
        <div class="space-y-6">
                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <StatCard
                            v-for="(stat, index) in statsData"
                            :key="stat.title"
                            v-motion
                            :initial="{ opacity: 0, y: 20 }"
                            :enter="{ opacity: 1, y: 0, transition: { delay: index * 100 } }"
                            :title="stat.title"
                            :value="stat.value"
                            :icon="stat.icon"
                            :color="stat.color"
                            :link="stat.link"
                            :trend="stat.trend"
                        />
                </div>

                    <!-- Quick Actions & Modules Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Quick Actions -->
                        <div
                            v-motion-slide-bottom
                            class="lg:col-span-1 bg-white rounded-2xl shadow-xl p-6 border border-gray-100"
                        >
                            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <span class="w-1 h-6 bg-gradient-to-b from-blue-500 to-indigo-600 rounded-full"></span>
                                Accesos Rápidos
                            </h3>
                            <div class="grid grid-cols-2 gap-3">
                                <Link
                                    v-for="(action, index) in quickActions"
                                    :key="action.href"
                                    :href="action.href"
                                    v-motion
                                    :initial="{ opacity: 0, scale: 0.9 }"
                                    :enter="{ opacity: 1, scale: 1, transition: { delay: index * 50 } }"
                                    class="group p-4 bg-gradient-to-br from-gray-50 to-white border-2 border-gray-200 rounded-xl hover:border-blue-500 hover:shadow-lg transition-all duration-200"
                                >
                                    <div class="text-3xl mb-2 group-hover:scale-110 transition-transform duration-200">
                                        {{ action.icon }}
                                    </div>
                                    <div class="font-semibold text-gray-700 group-hover:text-blue-600 transition-colors">
                                        {{ action.label }}
                                    </div>
                        </Link>
                            </div>
                        </div>

                        <!-- Modules List -->
                        <div
                            v-motion-slide-bottom
                            :delay="100"
                            class="lg:col-span-2 bg-white rounded-2xl shadow-xl p-6 border border-gray-100"
                        >
                            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <span class="w-1 h-6 bg-gradient-to-b from-indigo-500 to-purple-600 rounded-full"></span>
                                Módulos del Sistema
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <Link
                                    v-for="(module, index) in modules"
                                    :key="module.href"
                                    :href="module.href"
                                    v-motion
                                    :initial="{ opacity: 0, x: -20 }"
                                    :enter="{ opacity: 1, x: 0, transition: { delay: index * 30 } }"
                                    class="group flex items-center gap-3 p-4 bg-gradient-to-r from-gray-50 to-white border-2 border-gray-200 rounded-xl hover:border-indigo-500 hover:shadow-md transition-all duration-200"
                                >
                                    <div class="text-2xl group-hover:scale-110 transition-transform duration-200">
                                        {{ module.icon }}
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-semibold text-gray-700 group-hover:text-indigo-600 transition-colors">
                                            {{ module.label }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-0.5">
                                            {{ module.description }}
                                        </div>
                                    </div>
                                    <svg class="w-5 h-5 text-gray-400 group-hover:text-indigo-600 group-hover:translate-x-1 transition-all duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                        </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity / Charts Area -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Recent Sales -->
                        <div
                            v-motion-slide-bottom
                            :delay="200"
                            class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100"
                        >
                            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <span class="w-1 h-6 bg-gradient-to-b from-green-500 to-emerald-600 rounded-full"></span>
                                Resumen de Ventas
                            </h3>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-200">
                                    <div>
                                        <p class="text-sm text-gray-600">Ventas Hoy</p>
                                        <p class="text-2xl font-bold text-green-700">{{ stats.ventas_hoy || 0 }}</p>
                                    </div>
                                    <div class="text-4xl">📈</div>
                                </div>
                                <div class="flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-200">
                                    <div>
                                        <p class="text-sm text-gray-600">Ventas del Mes</p>
                                        <p class="text-2xl font-bold text-blue-700">{{ stats.ventas_mes || 0 }}</p>
                                    </div>
                                    <div class="text-4xl">📊</div>
                                </div>
                            </div>
                        </div>

                        <!-- System Info -->
                        <div
                            v-motion-slide-bottom
                            :delay="300"
                            class="bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600 rounded-2xl shadow-xl p-6 text-white"
                        >
                            <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                                <span class="w-1 h-6 bg-white/30 rounded-full"></span>
                                Información del Sistema
                            </h3>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between p-3 bg-white/10 backdrop-blur-sm rounded-lg">
                                    <span class="text-sm">Total de Productos</span>
                                    <span class="font-bold text-lg">{{ stats.productos || 0 }}</span>
                                </div>
                                <div class="flex items-center justify-between p-3 bg-white/10 backdrop-blur-sm rounded-lg">
                                    <span class="text-sm">Total de Clientes</span>
                                    <span class="font-bold text-lg">{{ stats.clientes || 0 }}</span>
                                </div>
                                <div class="flex items-center justify-between p-3 bg-white/10 backdrop-blur-sm rounded-lg">
                                    <span class="text-sm">Créditos Activos</span>
                                    <span class="font-bold text-lg">{{ stats.creditos || 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatCard from '@/Components/StatCard.vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({})
    }
});


const statsData = computed(() => [
    {
        title: 'Productos',
        value: props.stats.productos || 0,
        icon: '📦',
        color: 'from-blue-500 to-cyan-500',
        link: '/admin/productos',
        trend: '+12%'
    },
    {
        title: 'Ventas',
        value: props.stats.ventas || 0,
        icon: '💰',
        color: 'from-green-500 to-emerald-500',
        link: '/admin/ventas',
        trend: '+8%'
    },
    {
        title: 'Clientes',
        value: props.stats.clientes || 0,
        icon: '👥',
        color: 'from-purple-500 to-pink-500',
        link: '/admin/clientes',
        trend: '+5%'
    },
    {
        title: 'Créditos Activos',
        value: props.stats.creditos || 0,
        icon: '💳',
        color: 'from-orange-500 to-red-500',
        link: '/admin/creditos',
        trend: '+3%'
    }
]);

const quickActions = [
    { href: '/admin/productos/create', icon: '➕', label: 'Nuevo Producto' },
    { href: '/admin/ventas/create', icon: '🛒', label: 'Nueva Venta' },
    { href: '/admin/compras/create', icon: '📥', label: 'Nueva Compra' },
    { href: '/admin/inventario', icon: '📊', label: 'Inventario' }
];

const modules = [
    { href: '/admin/productos', icon: '📦', label: 'Productos', description: 'Gestionar catálogo' },
    { href: '/admin/categorias', icon: '🏷️', label: 'Categorías', description: 'Organizar productos' },
    { href: '/admin/clientes', icon: '👥', label: 'Clientes', description: 'Base de datos clientes' },
    { href: '/admin/proveedores', icon: '🏢', label: 'Proveedores', description: 'Gestión proveedores' },
    { href: '/admin/ventas', icon: '💰', label: 'Ventas', description: 'Historial de ventas' },
    { href: '/admin/compras', icon: '🛒', label: 'Compras', description: 'Registro de compras' },
    { href: '/admin/inventario', icon: '📊', label: 'Inventario', description: 'Control de stock' },
    { href: '/admin/creditos', icon: '💳', label: 'Créditos', description: 'Gestión de créditos' }
];
</script>
