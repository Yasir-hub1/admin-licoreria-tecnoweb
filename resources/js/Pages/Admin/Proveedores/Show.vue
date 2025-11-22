<template>
    <AdminLayout>
        <div class="container mx-auto px-4 py-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold">Detalle del Proveedor</h1>
                <div class="space-x-2">
                    <Link v-if="puedeEditar" :href="`/admin/proveedores/${proveedor.id}/edit`" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium">
                        Editar
                    </Link>
                    <Link href="/admin/proveedores" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium">
                        Volver
                    </Link>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-6 max-w-2xl mb-6">
                <h2 class="text-xl font-bold mb-4 text-gray-800">Información del Proveedor</h2>
                <dl class="grid grid-cols-1 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nombre</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ proveedor.nombre || '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Teléfono</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ proveedor.telefono || '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">NIT</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ proveedor.nit || '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Correo</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ proveedor.correo || '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Dirección</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ proveedor.direccion || '-' }}</dd>
                    </div>
                    <div v-if="proveedor.usuario">
                        <dt class="text-sm font-medium text-gray-500">Usuario del Sistema</dt>
                        <dd class="mt-1">
                            <div class="flex items-center gap-2">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ proveedor.usuario.nombre }}</p>
                                    <p class="text-xs text-gray-600">{{ proveedor.usuario.email }}</p>
                                    <p class="text-xs text-blue-600">Rol: {{ proveedor.usuario.rol?.nombre || 'Sin rol' }}</p>
                                </div>
                                <Link v-if="puedeVerUsuario" :href="`/admin/usuarios/${proveedor.usuario.id}`" class="ml-2 text-blue-600 hover:text-blue-900 text-sm">
                                    Ver Usuario →
                                </Link>
                            </div>
                        </dd>
                    </div>
                    <div v-else>
                        <dt class="text-sm font-medium text-gray-500">Usuario del Sistema</dt>
                        <dd class="mt-1 text-sm text-gray-400 italic">Sin usuario asignado</dd>
                    </div>
                </dl>
            </div>

            <div v-if="proveedor.compras && proveedor.compras.length > 0" class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-bold mb-4 text-gray-800">Compras Realizadas</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="compra in proveedor.compras" :key="compra.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm">#{{ compra.id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ compra.fecha || '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                                    Bs. {{ Number(compra.total).toFixed(2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <Link v-if="puedeVerCompras" :href="`/admin/compras/${compra.id}`" class="text-blue-600 hover:text-blue-900">
                                        Ver Detalle
                                    </Link>
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-else class="bg-white shadow rounded-lg p-6">
                <p class="text-gray-500 text-center">Este proveedor no tiene compras registradas</p>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { usePermissions } from '@/composables/usePermissions';

defineProps({
    proveedor: Object
});

const { tienePermiso } = usePermissions();

// Hacer reactivos los permisos usando computed
const puedeEditar = computed(() => tienePermiso('proveedores.editar'));
const puedeVerCompras = computed(() => tienePermiso('compras.ver'));
const puedeVerUsuario = computed(() => tienePermiso('usuarios.ver'));
</script>

