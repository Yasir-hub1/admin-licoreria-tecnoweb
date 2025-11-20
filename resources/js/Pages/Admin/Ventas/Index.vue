<template>
    <AdminLayout>
        <div class="container mx-auto px-4 py-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold">Ventas</h1>
                <Link v-if="puedeCrear" href="/admin/ventas/create" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium">
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <Link v-if="puedeVer" :href="`/admin/ventas/${venta.id}`" class="text-blue-600 hover:text-blue-900">Ver</Link>
                                <Link v-if="puedeEditar" :href="`/admin/ventas/${venta.id}/edit`" class="text-indigo-600 hover:text-indigo-900">Editar</Link>
                                <button v-if="puedeEliminar" @click="deleteItem(venta.id)" class="text-red-600 hover:text-red-900">Eliminar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="ventas.links" class="mt-4 flex justify-center">
                <nav class="flex gap-2">
                    <Link
                        v-for="(link, index) in ventas.links"
                        :key="index"
                        :href="link.url || '#'"
                        v-html="link.label"
                        :class="[
                            'px-3 py-2 border rounded',
                            link.active ? 'bg-blue-500 text-white border-blue-500' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
                            !link.url ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
                        ]"
                    />
                </nav>
            </div>
        </div>
    </AdminLayout>
</template>
<script setup>
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { usePermissions } from '@/composables/usePermissions';

defineProps({ ventas: Object });

const { tienePermiso } = usePermissions();

const puedeCrear = tienePermiso('ventas.crear');
const puedeVer = tienePermiso('ventas.ver');
const puedeEditar = tienePermiso('ventas.editar');
const puedeEliminar = tienePermiso('ventas.eliminar');

const deleteItem = (id) => {
    if(confirm('¿Está seguro de eliminar esta venta?')) {
        router.delete(`/admin/ventas/${id}`);
    }
};
</script>

