<template>
    <MainLayout>
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold mb-6">Nuevo Cliente</h1>
            <form @submit.prevent="submit" class="bg-white shadow rounded-lg p-6 max-w-md">
                <div class="mb-4"><label class="block text-gray-700 font-bold mb-2">CI *</label>
                    <input v-model="form.ci" type="text" class="w-full px-3 py-2 border rounded-lg" required />
                    <span v-if="form.errors.ci" class="text-red-500 text-sm">{{ form.errors.ci }}</span>
                </div>
                <div class="mb-4"><label class="block text-gray-700 font-bold mb-2">Nombre *</label>
                    <input v-model="form.nombre" type="text" class="w-full px-3 py-2 border rounded-lg" required />
                    <span v-if="form.errors.nombre" class="text-red-500 text-sm">{{ form.errors.nombre }}</span>
                </div>
                <div class="mb-4"><label class="block text-gray-700 font-bold mb-2">Teléfono</label>
                    <input v-model="form.telefono" type="text" class="w-full px-3 py-2 border rounded-lg" />
                </div>
                <div class="mb-4"><label class="block text-gray-700 font-bold mb-2">Dirección</label>
                    <input v-model="form.direccion" type="text" class="w-full px-3 py-2 border rounded-lg" />
                </div>
                <div class="mb-4"><label class="block text-gray-700 font-bold mb-2">Estado *</label>
                    <select v-model="form.estado" class="w-full px-3 py-2 border rounded-lg" required>
                        <option value="A">Activo</option>
                        <option value="I">Inactivo</option>
                    </select>
                </div>
                <div class="flex gap-4">
                    <button type="submit" :disabled="form.processing" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium">Guardar</button>
                    <Link href="/admin/clientes" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-medium">Cancelar</Link>
                </div>
            </form>
        </div>
    </MainLayout>
</template>
<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
const form = useForm({ ci: '', nombre: '', telefono: '', direccion: '', estado: 'A' });
const submit = () => form.post('/admin/clientes');
</script>

