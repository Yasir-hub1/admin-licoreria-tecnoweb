<template>
    <AdminLayout>
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold mb-6">Categoría: {{ categoria.nombre }}</h1>
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-gray-700"><strong>Productos:</strong> {{ categoria.productos?.length || 0 }}</p>
                <div class="mt-4">
                    <Link v-if="puedeEditar" :href="`/admin/categorias/${categoria.id}/edit`" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium mr-2">Editar</Link>
                    <Link href="/admin/categorias" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium">Volver</Link>
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

defineProps({ categoria: Object });

const { tienePermiso } = usePermissions();

// Hacer reactivos los permisos usando computed
const puedeEditar = computed(() => tienePermiso('categorias.editar'));
</script>

