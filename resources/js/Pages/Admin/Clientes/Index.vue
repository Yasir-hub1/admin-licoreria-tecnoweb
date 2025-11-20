<template>
    <AdminLayout>
        <div class="container mx-auto px-4 py-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold">Clientes</h1>
                <div class="flex gap-3">
                    <Link 
                        v-if="puedeVer" 
                        href="/admin/clientes/verificar-documentos" 
                        class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg font-medium flex items-center gap-2"
                    >
                        📋 Verificar Documentos
                    </Link>
                    <Link v-if="puedeCrear" href="/admin/clientes/create" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium">
                        ➕ Nuevo Cliente
                    </Link>
                </div>
            </div>
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50"><tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">CI</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Teléfono</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Verificación Crédito</th>
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span 
                                    v-if="cliente.estado_verificacion"
                                    class="px-2 py-1 rounded-full text-xs font-medium"
                                    :class="{
                                        'bg-green-100 text-green-800': cliente.estado_verificacion === 'aprobado',
                                        'bg-blue-100 text-blue-800': cliente.estado_verificacion === 'en_revision',
                                        'bg-yellow-100 text-yellow-800': cliente.estado_verificacion === 'pendiente',
                                        'bg-red-100 text-red-800': cliente.estado_verificacion === 'rechazado'
                                    }"
                                >
                                    <span v-if="cliente.estado_verificacion === 'aprobado'">✅ Aprobado</span>
                                    <span v-else-if="cliente.estado_verificacion === 'en_revision'">⏳ En Revisión</span>
                                    <span v-else-if="cliente.estado_verificacion === 'pendiente'">📋 Pendiente</span>
                                    <span v-else-if="cliente.estado_verificacion === 'rechazado'">❌ Rechazado</span>
                                </span>
                                <span v-else class="text-gray-400 text-xs">Sin documentos</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <Link v-if="puedeVer" :href="`/admin/clientes/${cliente.id}`" class="text-blue-600 hover:text-blue-900">Ver</Link>
                                <Link v-if="puedeEditar" :href="`/admin/clientes/${cliente.id}/edit`" class="text-indigo-600 hover:text-indigo-900">Editar</Link>
                                <Link 
                                    v-if="puedeVer && (cliente.estado_verificacion === 'en_revision' || cliente.estado_verificacion === 'pendiente')" 
                                    :href="`/admin/clientes/verificar-documentos?cliente=${cliente.id}`" 
                                    class="text-purple-600 hover:text-purple-900"
                                >
                                    Verificar
                                </Link>
                                <button v-if="puedeEliminar" @click="deleteItem(cliente.id)" class="text-red-600 hover:text-red-900">Eliminar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="clientes.links" class="mt-4 flex justify-center">
                <nav class="flex gap-2">
                    <Link
                        v-for="(link, index) in clientes.links"
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

defineProps({ clientes: Object });

const { tienePermiso } = usePermissions();

const puedeCrear = tienePermiso('clientes.crear');
const puedeVer = tienePermiso('clientes.ver');
const puedeEditar = tienePermiso('clientes.editar');
const puedeEliminar = tienePermiso('clientes.eliminar');

const deleteItem = (id) => {
    if(confirm('¿Está seguro de eliminar este cliente?')) {
        router.delete(`/admin/clientes/${id}`);
    }
};
</script>

