<template>
    <AdminLayout>
        <div class="container mx-auto px-4 py-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold">Empleados</h1>
                <Link v-if="puedeCrear" href="/admin/empleados/create" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium">
                    ➕ Nuevo Empleado
                </Link>
            </div>
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50"><tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">CI</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr></thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="empleado in empleados.data" :key="empleado.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ empleado.ci }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ empleado.nombre }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ empleado.usuario?.nombre || '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <Link v-if="puedeVer" :href="`/admin/empleados/${empleado.id}`" class="text-blue-600 hover:text-blue-900">Ver</Link>
                                <Link v-if="puedeEditar" :href="`/admin/empleados/${empleado.id}/edit`" class="text-indigo-600 hover:text-indigo-900">Editar</Link>
                                <button v-if="puedeEliminar" @click="deleteItem(empleado.id)" class="text-red-600 hover:text-red-900">Eliminar</button>
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

defineProps({ empleados: Object });

const { tienePermiso } = usePermissions();

const puedeCrear = tienePermiso('empleados.crear');
const puedeVer = tienePermiso('empleados.ver');
const puedeEditar = tienePermiso('empleados.editar');
const puedeEliminar = tienePermiso('empleados.eliminar');

const deleteItem = (id) => {
    if(confirm('¿Está seguro de eliminar este empleado?')) {
        router.delete(`/admin/empleados/${id}`);
    }
};
</script>

