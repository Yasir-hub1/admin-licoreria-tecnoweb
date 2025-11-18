<template>
    <MainLayout>
        <div class="container mx-auto px-4 py-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold">Detalle del Producto</h1>
                <div class="space-x-2">
                    <Link :href="`/admin/productos/${producto.id}/edit`" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium">
                        Editar
                    </Link>
                    <Link href="/admin/productos" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium">
                        Volver
                    </Link>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-6 max-w-2xl">
                <dl class="grid grid-cols-1 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Código</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ producto.codigo }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nombre</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ producto.nombre }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Descripción</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ producto.descripcion || '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Precio</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-semibold text-green-600">Bs. {{ Number(producto.precio).toFixed(2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Marca</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ producto.marca || '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Categoría</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ producto.categoria?.nombre || '-' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

defineProps({
    producto: Object
});
</script>

