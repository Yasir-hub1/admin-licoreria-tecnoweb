<template>
    <MainLayout>
        <div class="container mx-auto px-4 py-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold">Categorías de Licores</h1>
                <Link href="/admin/categorias/create" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium">
                    ➕ Nueva Categoría
                </Link>
            </div>
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50"><tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Productos</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr></thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="cat in categorias.data" :key="cat.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ cat.nombre }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ cat.productos_count || 0 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <Link :href="`/admin/categorias/${cat.id}/edit`" class="text-indigo-600 hover:text-indigo-900">Editar</Link>
                                <button @click="deleteItem(cat.id)" class="text-red-600 hover:text-red-900">Eliminar</button>
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
defineProps({ categorias: Object });
const deleteItem = (id) => {
    if(confirm('¿Está seguro de eliminar esta categoría?')) {
        router.delete(`/admin/categorias/${id}`);
    }
};
</script>

