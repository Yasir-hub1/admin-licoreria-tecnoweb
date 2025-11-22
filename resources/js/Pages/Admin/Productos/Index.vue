<template>
    <AdminLayout title="Productos" subtitle="Gestiona el catálogo de productos">
        <div class="space-y-6">
            <div class="flex justify-between items-center" v-if="puedeCrear">
                <Link href="/admin/productos/create">
                    <Button variant="primary" size="md">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nuevo Producto
                    </Button>
                </Link>
            </div>

            <div v-if="$page.props.flash?.success" class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg flex items-start gap-3">
                <svg class="w-5 h-5 text-green-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p>{{ $page.props.flash.success }}</p>
            </div>

            <div v-if="$page.props.flash?.error" class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg flex items-start gap-3">
                <svg class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p>{{ $page.props.flash.error }}</p>
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
                                <Link v-if="puedeVer" :href="`/admin/productos/${producto.id}`" class="text-blue-600 hover:text-blue-900">Ver</Link>
                                <Link v-if="puedeEditar" :href="`/admin/productos/${producto.id}/edit`" class="text-indigo-600 hover:text-indigo-900">Editar</Link>
                                <button v-if="puedeEliminar" @click="deleteProducto(producto.id)" class="text-red-600 hover:text-red-900">Eliminar</button>
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
    </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/Button.vue';
import { usePermissions } from '@/composables/usePermissions';

defineProps({
    productos: Object
});

const { tienePermiso } = usePermissions();

// Hacer reactivos los permisos usando computed
const puedeCrear = computed(() => tienePermiso('productos.crear'));
const puedeVer = computed(() => tienePermiso('productos.ver'));
const puedeEditar = computed(() => tienePermiso('productos.editar'));
const puedeEliminar = computed(() => tienePermiso('productos.eliminar'));

const deleteProducto = (id) => {
    if (confirm('¿Está seguro de eliminar este producto?')) {
        router.delete(`/admin/productos/${id}`);
    }
};
</script>

