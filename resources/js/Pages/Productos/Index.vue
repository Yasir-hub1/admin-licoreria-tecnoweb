<template>
    <MainLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-gray-800">Productos</h1>
                    <Link
                        :href="route('productos.create')"
                        class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg transition duration-200"
                    >
                        Nuevo Producto
                    </Link>
                </div>

                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Código
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nombre
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Precio
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Marca
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="producto in productos.data" :key="producto.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ producto.codigo }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ producto.nombre }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ${{ producto.precio }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ producto.marca }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <Link
                                        :href="route('productos.edit', producto.id)"
                                        class="text-blue-600 hover:text-blue-900 mr-3"
                                    >
                                        Editar
                                    </Link>
                                    <button
                                        @click="deleteProducto(producto.id)"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="productos.data.length === 0" class="text-center py-8 text-gray-500">
                        No hay productos registrados
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Link, router } from '@inertiajs/vue3';

defineProps({
    productos: Object,
});

const deleteProducto = (id) => {
    if (confirm('¿Está seguro de eliminar este producto?')) {
        router.delete(route('productos.destroy', id));
    }
};
</script>
