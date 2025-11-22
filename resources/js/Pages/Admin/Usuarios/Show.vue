<template>
    <AdminLayout>
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold mb-6">Usuario: {{ usuario?.nombre }}</h1>
            <div class="bg-white shadow rounded-lg p-6">
                <dl class="grid grid-cols-1 gap-4">
                    <div><dt class="text-sm font-medium text-gray-500">Nombre</dt><dd class="mt-1 text-sm text-gray-900">{{ usuario.nombre }}</dd></div>
                    <div><dt class="text-sm font-medium text-gray-500">Email</dt><dd class="mt-1 text-sm text-gray-900">{{ usuario.email }}</dd></div>
                    <div><dt class="text-sm font-medium text-gray-500">Rol</dt><dd class="mt-1 text-sm text-gray-900">{{ usuario.rol?.nombre || '-' }}</dd></div>
                    <div><dt class="text-sm font-medium text-gray-500">Estado</dt><dd class="mt-1 text-sm text-gray-900">{{ usuario.estado }}</dd></div>
                </dl>
                <div class="mt-4">
                    <Link v-if="puedeEditar" :href="`/admin/usuarios/${usuario.id}/edit`" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium mr-2">Editar</Link>
                    <Link href="/admin/usuarios" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium">Volver</Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { usePermissions } from '@/composables/usePermissions';

defineProps({ usuario: Object });

const { tienePermiso } = usePermissions();

// Hacer reactivos los permisos usando computed
const puedeEditar = computed(() => tienePermiso('usuarios.editar'));
</script>

