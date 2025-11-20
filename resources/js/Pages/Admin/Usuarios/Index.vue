<template>
    <AdminLayout>
        <div class="container mx-auto px-4 py-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold">Usuarios del Sistema</h1>
                <Link v-if="puedeCrear" href="/admin/usuarios/create" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium">
                    ➕ Nuevo Usuario
                </Link>
            </div>
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50"><tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rol</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr></thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="usuario in usuarios.data" :key="usuario.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ usuario.nombre }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ usuario.email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ usuario.rol?.nombre || '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span
                                    class="px-2 py-1 rounded-full text-xs font-medium"
                                    :class="usuario.estado === 'activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                >
                                    {{ usuario.estado }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <Link v-if="puedeVer" :href="`/admin/usuarios/${usuario.id}`" class="text-blue-600 hover:text-blue-900">Ver</Link>
                                <Link v-if="puedeEditar" :href="`/admin/usuarios/${usuario.id}/edit`" class="text-indigo-600 hover:text-indigo-900">Editar</Link>
                                <button v-if="puedeEliminar" @click="deleteItem(usuario.id)" class="text-red-600 hover:text-red-900">Eliminar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="usuarios.links" class="mt-4 flex justify-center">
                <nav class="flex gap-2">
                    <Link
                        v-for="(link, index) in usuarios.links"
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

defineProps({ usuarios: Object });

const { tienePermiso } = usePermissions();

const puedeCrear = tienePermiso('usuarios.crear');
const puedeVer = tienePermiso('usuarios.ver');
const puedeEditar = tienePermiso('usuarios.editar');
const puedeEliminar = tienePermiso('usuarios.eliminar');

const deleteItem = (id) => {
    if(confirm('¿Está seguro de eliminar este usuario?')) {
        router.delete(`/admin/usuarios/${id}`);
    }
};
</script>

