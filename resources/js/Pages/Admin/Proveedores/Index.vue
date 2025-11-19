<template>
    <AdminLayout>
        <div class="container mx-auto px-4 py-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold">Proveedores de Licores</h1>
                <Link v-if="puedeCrear" href="/admin/proveedores/create" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium">
                    ➕ Nuevo Proveedor
                </Link>
            </div>

            <div v-if="$page.props.flash?.success" class="mb-4 p-3 bg-green-100 text-green-700 rounded">
                {{ $page.props.flash.success }}
            </div>

            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Teléfono</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIT</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Correo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dirección</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Compras</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="proveedor in proveedores.data" :key="proveedor.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ proveedor.nombre || '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ proveedor.telefono || '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ proveedor.nit || '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ proveedor.correo || '-' }}</td>
                            <td class="px-6 py-4 text-sm">{{ proveedor.direccion || '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                                    {{ proveedor.compras_count || 0 }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <Link v-if="puedeVer" :href="`/admin/proveedores/${proveedor.id}`" class="text-blue-600 hover:text-blue-900">Ver</Link>
                                <Link v-if="puedeEditar" :href="`/admin/proveedores/${proveedor.id}/edit`" class="text-indigo-600 hover:text-indigo-900">Editar</Link>
                                <button v-if="puedeEliminar" @click="deleteItem(proveedor.id)" class="text-red-600 hover:text-red-900">Eliminar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div v-if="proveedores.links && proveedores.links.length > 3" class="mt-4 flex justify-center">
                <nav class="flex gap-2">
                    <Link
                        v-for="(link, index) in proveedores.links"
                        :key="index"
                        :href="link.url || '#'"
                        v-html="link.label"
                        :class="[
                            'px-3 py-2 border rounded-lg',
                            link.active ? 'bg-blue-500 text-white border-blue-500' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
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

defineProps({
    proveedores: Object
});

const { tienePermiso } = usePermissions();

const puedeCrear = tienePermiso('proveedores.crear');
const puedeVer = tienePermiso('proveedores.ver');
const puedeEditar = tienePermiso('proveedores.editar');
const puedeEliminar = tienePermiso('proveedores.eliminar');

const deleteItem = (id) => {
    if(confirm('¿Está seguro de eliminar este proveedor?')) {
        router.delete(`/admin/proveedores/${id}`);
    }
};
</script>

