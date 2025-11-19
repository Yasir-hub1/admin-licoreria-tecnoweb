<template>
    <AdminLayout>
        <div class="container mx-auto px-4 py-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold">Compras</h1>
                <Link v-if="puedeCrear" href="/admin/compras/create" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium">
                    ➕ Nueva Compra
                </Link>
            </div>
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50"><tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nro Compra</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Proveedor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr></thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="compra in compras.data" :key="compra.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ compra.nro_compra }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ compra.fecha }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ compra.proveedor?.nombre || '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span
                                    class="px-2 py-1 rounded-full text-xs font-medium"
                                    :class="{
                                        'bg-green-100 text-green-800': compra.estado === 'validado',
                                        'bg-red-100 text-red-800': compra.estado === 'cancelado',
                                        'bg-yellow-100 text-yellow-800': compra.estado === 'pendiente' || !compra.estado
                                    }"
                                >
                                    {{ compra.estado === 'validado' ? 'Validado' : compra.estado === 'cancelado' ? 'Cancelado' : 'Pendiente' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <Link v-if="puedeVer" :href="`/admin/compras/${compra.id}`" class="text-blue-600 hover:text-blue-900">Ver</Link>
                                <Link
                                    v-if="puedeEditar && compra.estado !== 'validado'"
                                    :href="`/admin/compras/${compra.id}/edit`"
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
                                    Editar
                                </Link>
                                <button v-if="puedeEliminar && compra.estado !== 'validado'" @click="deleteItem(compra.id)" class="text-red-600 hover:text-red-900">Eliminar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
<script setup>
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { usePermissions } from '@/composables/usePermissions';

defineProps({ compras: Object });

const { tienePermiso } = usePermissions();

const puedeCrear = tienePermiso('compras.crear');
const puedeVer = tienePermiso('compras.ver');
const puedeEditar = tienePermiso('compras.editar');
const puedeEliminar = tienePermiso('compras.eliminar');

const deleteItem = (id) => {
    if(confirm('¿Está seguro de eliminar esta compra?')) {
        router.delete(`/admin/compras/${id}`);
    }
};
</script>

