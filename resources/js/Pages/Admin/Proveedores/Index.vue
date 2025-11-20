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

            <!-- Vista de Tabla para Desktop -->
            <div class="hidden lg:block bg-white shadow rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Teléfono</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIT</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Correo</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dirección</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Compras</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="proveedor in proveedores.data" :key="proveedor.id" class="hover:bg-gray-50">
                                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">{{ proveedor.nombre || '-' }}</td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm">{{ proveedor.telefono || '-' }}</td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm">{{ proveedor.nit || '-' }}</td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm">{{ proveedor.correo || '-' }}</td>
                                <td class="px-4 py-4 text-sm max-w-xs truncate">{{ proveedor.direccion || '-' }}</td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm">
                                    <div v-if="proveedor.usuario" class="flex flex-col">
                                        <span class="font-medium text-gray-900">{{ proveedor.usuario.nombre }}</span>
                                        <span class="text-xs text-gray-500">{{ proveedor.usuario.email }}</span>
                                        <span class="text-xs text-blue-600">{{ proveedor.usuario.rol?.nombre || 'Sin rol' }}</span>
                                    </div>
                                    <span v-else class="text-gray-400 italic">Sin usuario</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-center">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                                        {{ proveedor.compras_count || 0 }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <Link v-if="puedeVer" :href="`/admin/proveedores/${proveedor.id}`" class="text-blue-600 hover:text-blue-900">Ver</Link>
                                    <Link v-if="puedeEditar" :href="`/admin/proveedores/${proveedor.id}/edit`" class="text-indigo-600 hover:text-indigo-900">Editar</Link>
                                    <button v-if="puedeEliminar" @click="deleteItem(proveedor.id)" class="text-red-600 hover:text-red-900">Eliminar</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Vista de Tarjetas para Mobile/Tablet -->
            <div class="lg:hidden space-y-4">
                <div v-for="proveedor in proveedores.data" :key="proveedor.id" class="bg-white shadow rounded-lg p-4 border border-gray-200">
                    <div class="flex justify-between items-start mb-3">
                        <h3 class="text-lg font-semibold text-gray-900">{{ proveedor.nombre || '-' }}</h3>
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                            {{ proveedor.compras_count || 0 }} compras
                        </span>
                    </div>
                    
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500 font-medium min-w-[80px]">Teléfono:</span>
                            <span class="text-gray-900">{{ proveedor.telefono || '-' }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500 font-medium min-w-[80px]">NIT:</span>
                            <span class="text-gray-900">{{ proveedor.nit || '-' }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500 font-medium min-w-[80px]">Correo:</span>
                            <span class="text-gray-900 break-all">{{ proveedor.correo || '-' }}</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="text-gray-500 font-medium min-w-[80px]">Dirección:</span>
                            <span class="text-gray-900">{{ proveedor.direccion || '-' }}</span>
                        </div>
                        <div class="flex items-start gap-2 pt-2 border-t border-gray-200">
                            <span class="text-gray-500 font-medium min-w-[80px]">Usuario:</span>
                            <div v-if="proveedor.usuario" class="flex-1">
                                <div class="font-medium text-gray-900">{{ proveedor.usuario.nombre }}</div>
                                <div class="text-xs text-gray-500">{{ proveedor.usuario.email }}</div>
                                <div class="text-xs text-blue-600">{{ proveedor.usuario.rol?.nombre || 'Sin rol' }}</div>
                            </div>
                            <span v-else class="text-gray-400 italic">Sin usuario</span>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-gray-200 flex flex-wrap gap-2">
                        <Link v-if="puedeVer" :href="`/admin/proveedores/${proveedor.id}`" class="px-3 py-1.5 bg-blue-50 text-blue-600 rounded text-sm font-medium hover:bg-blue-100">
                            Ver
                        </Link>
                        <Link v-if="puedeEditar" :href="`/admin/proveedores/${proveedor.id}/edit`" class="px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded text-sm font-medium hover:bg-indigo-100">
                            Editar
                        </Link>
                        <button v-if="puedeEliminar" @click="deleteItem(proveedor.id)" class="px-3 py-1.5 bg-red-50 text-red-600 rounded text-sm font-medium hover:bg-red-100">
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="proveedores.links" class="mt-4 flex justify-center">
                <nav class="flex gap-2">
                    <Link
                        v-for="(link, index) in proveedores.links"
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

