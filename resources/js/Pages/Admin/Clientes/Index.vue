<template>
    <MainLayout>
        <div class="container mx-auto px-4 py-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold">Clientes</h1>
                <Link href="/admin/clientes/create" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium">
                    ➕ Nuevo Cliente
                </Link>
            </div>
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50"><tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">CI</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Teléfono</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr></thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="cliente in clientes.data" :key="cliente.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ cliente.ci }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ cliente.nombre }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ cliente.telefono || '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span :class="cliente.estado === 'A' ? 'text-green-600' : 'text-red-600'">{{ cliente.estado === 'A' ? 'Activo' : 'Inactivo' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <Link :href="`/admin/clientes/${cliente.id}`" class="text-blue-600 hover:text-blue-900">Ver</Link>
                                <Link :href="`/admin/clientes/${cliente.id}/edit`" class="text-indigo-600 hover:text-indigo-900">Editar</Link>
                                <button @click="deleteItem(cliente.id)" class="text-red-600 hover:text-red-900">Eliminar</button>
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
defineProps({ clientes: Object });
const deleteItem = (id) => {
    if(confirm('¿Está seguro de eliminar este cliente?')) {
        router.delete(`/admin/clientes/${id}`);
    }
};
</script>

