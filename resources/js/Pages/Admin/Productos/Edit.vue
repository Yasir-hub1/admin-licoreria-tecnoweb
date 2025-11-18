<template>
    <MainLayout>
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold mb-6">Editar Producto</h1>

            <form @submit.prevent="submit" class="bg-white shadow rounded-lg p-6 max-w-2xl">
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Código</label>
                    <input
                        :value="producto.codigo"
                        type="text"
                        class="w-full px-3 py-2 border rounded-lg bg-gray-100 cursor-not-allowed"
                        readonly
                        disabled
                    />
                    <p class="text-xs text-gray-500 mt-1">El código del producto no puede ser modificado</p>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Nombre *</label>
                    <input v-model="form.nombre" type="text" class="w-full px-3 py-2 border rounded-lg" required />
                    <span v-if="form.errors.nombre" class="text-red-500 text-sm">{{ form.errors.nombre }}</span>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Descripción</label>
                    <textarea v-model="form.descripcion" class="w-full px-3 py-2 border rounded-lg" rows="3"></textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Precio *</label>
                    <input v-model="form.precio" type="number" step="0.01" min="0" class="w-full px-3 py-2 border rounded-lg" required />
                    <span v-if="form.errors.precio" class="text-red-500 text-sm">{{ form.errors.precio }}</span>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Marca</label>
                    <input v-model="form.marca" type="text" class="w-full px-3 py-2 border rounded-lg" />
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Categoría *</label>
                    <select v-model="form.categoria_id" class="w-full px-3 py-2 border rounded-lg" required>
                        <option value="">Seleccione una categoría</option>
                        <option v-for="cat in categorias" :key="cat.id" :value="cat.id">{{ cat.nombre }}</option>
                    </select>
                    <span v-if="form.errors.categoria_id" class="text-red-500 text-sm">{{ form.errors.categoria_id }}</span>
                </div>

                <div class="flex gap-4">
                    <button type="submit" :disabled="form.processing" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded disabled:opacity-50">
                        Actualizar
                    </button>
                    <Link href="/admin/productos" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg">
                        Cancelar
                    </Link>
                </div>
            </form>
        </div>
    </MainLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

const props = defineProps({
    producto: Object,
    categorias: Array
});

const form = useForm({
    nombre: props.producto.nombre,
    descripcion: props.producto.descripcion || '',
    precio: props.producto.precio,
    marca: props.producto.marca || '',
    categoria_id: props.producto.categoria_id
});

const submit = () => {
    form.put(`/admin/productos/${props.producto.id}`);
};
</script>

