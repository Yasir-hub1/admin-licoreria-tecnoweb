<template>
    <MainLayout>
        <div class="container mx-auto px-4 py-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold">Usuarios del Sistema</h1>
                <Link href="/admin/usuarios/create" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium">
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
                                <Link :href="`/admin/usuarios/${usuario.id}`" class="text-blue-600 hover:text-blue-900">Ver</Link>
                                <Link :href="`/admin/usuarios/${usuario.id}/edit`" class="text-indigo-600 hover:text-indigo-900">Editar</Link>
                                <button @click="deleteItem(usuario.id)" class="text-red-600 hover:text-red-900">Eliminar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </MainLayout>
</template>
<script setup>
import { Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
defineProps({ usuarios: Object });
const deleteItem = (id) => {
    if(confirm('¿Está seguro de eliminar este usuario?')) {
        router.delete(`/admin/usuarios/${id}`);
    }
};
</script>

