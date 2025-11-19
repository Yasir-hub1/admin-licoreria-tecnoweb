<template>
    <AdminLayout>
        <div class="container mx-auto px-4 py-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold">Créditos de Clientes</h1>
            </div>
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50"><tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Saldo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr></thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="credito in creditos.data" :key="credito.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ credito.id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ credito.venta?.cliente?.nombre || '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">Bs. {{ Number(credito.monto_total).toFixed(2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">Bs. {{ Number(credito.saldo).toFixed(2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span
                                    class="px-2 py-1 rounded-full text-xs font-medium"
                                    :class="{
                                        'bg-green-100 text-green-800': credito.estado === 'pagado',
                                        'bg-blue-100 text-blue-800': credito.estado === 'activo',
                                        'bg-red-100 text-red-800': credito.estado === 'mora'
                                    }"
                                >
                                    {{ credito.estado }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <Link v-if="puedeVer" :href="`/admin/creditos/${credito.id}`" class="text-blue-600 hover:text-blue-900">Ver Detalle</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { usePermissions } from '@/composables/usePermissions';

defineProps({ creditos: Object });

const { tienePermiso } = usePermissions();

const puedeVer = tienePermiso('creditos.ver');
</script>

