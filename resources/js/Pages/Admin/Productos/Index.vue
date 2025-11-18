<template>
    <MainLayout>
        <div class="container mx-auto px-4 py-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold">Productos</h1>
                <Link href="/admin/productos/create" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium">
                    ➕ Nuevo Producto
                </Link>
            </div>

            <div v-if="$page.props.flash?.success" class="mb-4 p-3 bg-green-100 text-green-700 rounded">
                {{ $page.props.flash.success }}
            </div>

            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoría</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Precio</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Marca</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="producto in productos.data" :key="producto.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ producto.codigo }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ producto.nombre }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ producto.categoria?.nombre || '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">Bs. {{ Number(producto.precio).toFixed(2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ producto.marca || '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <Link :href="`/admin/productos/${producto.id}`" class="text-blue-600 hover:text-blue-900">Ver</Link>
                                <Link :href="`/admin/productos/${producto.id}/edit`" class="text-indigo-600 hover:text-indigo-900">Editar</Link>
                                <button @click="deleteProducto(producto.id)" class="text-red-600 hover:text-red-900">Eliminar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="productos.links" class="mt-4 flex justify-center">
                <nav class="flex gap-2">
                    <Link
                        v-for="(link, index) in productos.links"
                        :key="index"
                        :href="link.url || '#'"
                        v-html="link.label"
                        :class="[
                            'px-3 py-2 border rounded',
                            link.active ? 'bg-blue-500 text-white border-blue-500' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                        ]"
                    />
                </nav>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

defineProps({
    productos: Object
});

const deleteProducto = (id) => {
    if (confirm('¿Está seguro de eliminar este producto?')) {
        router.delete(`/admin/productos/${id}`);
    }
};
</script>

