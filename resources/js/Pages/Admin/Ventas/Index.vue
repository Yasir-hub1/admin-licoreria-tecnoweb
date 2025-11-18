<template>
    <MainLayout>
        <div class="container mx-auto px-4 py-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold">Ventas</h1>
                <Link href="/admin/ventas/create" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium">
                    ➕ Nueva Venta
                </Link>
            </div>
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50"><tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nro Venta</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr></thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="venta in ventas.data" :key="venta.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ venta.nro_venta }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ new Date(venta.fecha).toLocaleDateString('es-ES') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ venta.cliente?.nombre || '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span
                                    class="px-2 py-1 rounded-full text-xs font-medium"
                                    :class="venta.tipo === 'credito' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'"
                                >
                                    {{ venta.tipo === 'credito' ? '💰 Crédito' : '💵 Contado' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">Bs. {{ Number(venta.monto_total).toFixed(2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span
                                    class="px-2 py-1 rounded-full text-xs font-medium"
                                    :class="{
                                        'bg-green-100 text-green-800': venta.estado_real === 'completado',
                                        'bg-yellow-100 text-yellow-800': venta.estado_real === 'pendiente',
                                        'bg-orange-100 text-orange-800': venta.estado_real === 'en_credito',
                                        'bg-red-100 text-red-800': venta.estado_real === 'cancelado'
                                    }"
                                >
                                    {{ venta.estado_real === 'en_credito' ? 'En Crédito' : venta.estado_real === 'completado' ? 'Completado' : venta.estado_real === 'pendiente' ? 'Pendiente' : 'Cancelado' }}
                                </span>
                                <div v-if="venta.tipo === 'credito' && venta.saldo > 0" class="text-xs text-gray-500 mt-1">
                                    Saldo: Bs. {{ Number(venta.saldo).toFixed(2) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <Link :href="`/admin/ventas/${venta.id}`" class="text-blue-600 hover:text-blue-900">Ver</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </MainLayout>
</template>
<script setup>
import { Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
defineProps({ ventas: Object });
</script>

