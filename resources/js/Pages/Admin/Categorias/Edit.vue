<template>
    <MainLayout>
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold mb-6">Editar Categoría</h1>
            <form @submit.prevent="submit" class="bg-white shadow rounded-lg p-6 max-w-md">
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Nombre *</label>
                    <input v-model="form.nombre" type="text" class="w-full px-3 py-2 border rounded-lg" required />
                    <span v-if="form.errors.nombre" class="text-red-500 text-sm">{{ form.errors.nombre }}</span>
                </div>
                <div class="flex gap-4">
                    <button type="submit" :disabled="form.processing" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">Actualizar</button>
                    <Link href="/admin/categorias" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-medium">Cancelar</Link>
                </div>
            </form>
        </div>
    </MainLayout>
</template>
<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
const props = defineProps({ categoria: Object });
const form = useForm({ nombre: props.categoria.nombre });
const submit = () => form.put(`/admin/categorias/${props.categoria.id}`);
</script>

