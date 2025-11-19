<template>
    <AdminLayout>
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold mb-6">Cliente: {{ cliente.nombre }}</h1>
            <div class="bg-white shadow rounded-lg p-6">
                <dl class="grid grid-cols-1 gap-4">
                    <div><dt class="text-sm font-medium text-gray-500">CI</dt><dd class="mt-1 text-sm text-gray-900">{{ cliente.ci }}</dd></div>
                    <div><dt class="text-sm font-medium text-gray-500">Nombre</dt><dd class="mt-1 text-sm text-gray-900">{{ cliente.nombre }}</dd></div>
                    <div><dt class="text-sm font-medium text-gray-500">Teléfono</dt><dd class="mt-1 text-sm text-gray-900">{{ cliente.telefono || '-' }}</dd></div>
                    <div><dt class="text-sm font-medium text-gray-500">Dirección</dt><dd class="mt-1 text-sm text-gray-900">{{ cliente.direccion || '-' }}</dd></div>
                    <div><dt class="text-sm font-medium text-gray-500">Estado</dt><dd class="mt-1 text-sm text-gray-900">{{ cliente.estado === 'A' ? 'Activo' : 'Inactivo' }}</dd></div>
                </dl>
                <div class="mt-4">
                    <Link v-if="puedeEditar" :href="`/admin/clientes/${cliente.id}/edit`" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium mr-2">Editar</Link>
                    <Link href="/admin/clientes" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium">Volver</Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { usePermissions } from '@/composables/usePermissions';

defineProps({ cliente: Object });

const { tienePermiso } = usePermissions();

const puedeEditar = tienePermiso('clientes.editar');
</script>

