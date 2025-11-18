<template>
    <MainLayout>
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold mb-6">Rol: {{ rol?.nombre }}</h1>
            <div class="bg-white shadow rounded-lg p-6">
                <p class="mb-2"><strong>Descripción:</strong> {{ rol.descripcion || '-' }}</p>
                <p class="mb-4"><strong>Usuarios:</strong> {{ rol.usuarios?.length || 0 }}</p>
                <div class="mt-4">
                    <Link :href="`/admin/roles/${rol.id}/edit`" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium mr-2">Editar</Link>
                    <Link href="/admin/roles" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium">Volver</Link>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
<script setup>
import { Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
defineProps({ rol: Object });
</script>

