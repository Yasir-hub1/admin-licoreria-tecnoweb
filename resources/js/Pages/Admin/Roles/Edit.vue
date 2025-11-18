<template>
    <MainLayout>
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold mb-6">Editar Rol</h1>
            <form @submit.prevent="submit" class="bg-white shadow rounded-lg p-6 max-w-md">
                <div class="mb-4"><label class="block text-gray-700 font-bold mb-2">Nombre *</label>
                    <input v-model="form.nombre" type="text" class="w-full px-3 py-2 border rounded-lg" required />
                </div>
                <div class="mb-4"><label class="block text-gray-700 font-bold mb-2">Descripción</label>
                    <textarea v-model="form.descripcion" class="w-full px-3 py-2 border rounded-lg" rows="3"></textarea>
                </div>
                <div class="flex gap-4">
                    <button type="submit" :disabled="form.processing" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">Actualizar</button>
                    <Link href="/admin/roles" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-medium">Cancelar</Link>
                </div>
            </form>
        </div>
    </MainLayout>
</template>
<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
const props = defineProps({ rol: Object });
const form = useForm({ nombre: props.rol.nombre, descripcion: props.rol.descripcion || '' });
const submit = () => form.put(`/admin/roles/${props.rol.id}`);
</script>

