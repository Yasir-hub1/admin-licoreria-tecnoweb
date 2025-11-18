<template>
    <MainLayout>
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold mb-6">Editar Proveedor</h1>

            <div v-if="form.errors && Object.keys(form.errors).length > 0" class="mb-4 p-3 bg-red-100 border border-red-400 rounded-lg">
                <p class="text-red-700 text-sm font-medium mb-2">Errores:</p>
                <ul class="list-disc list-inside text-sm text-red-600">
                    <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
                </ul>
            </div>

            <form @submit.prevent="submit" class="bg-white shadow rounded-lg p-6 max-w-2xl">
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Nombre *</label>
                    <input
                        v-model="form.nombre"
                        type="text"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                        :class="{ 'border-red-500': form.errors.nombre }"
                        maxlength="50"
                        required
                    />
                    <span v-if="form.errors.nombre" class="text-red-500 text-xs mt-1">{{ form.errors.nombre }}</span>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Teléfono</label>
                    <input
                        v-model="form.telefono"
                        type="text"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                        :class="{ 'border-red-500': form.errors.telefono }"
                        maxlength="20"
                    />
                    <span v-if="form.errors.telefono" class="text-red-500 text-xs mt-1">{{ form.errors.telefono }}</span>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">NIT</label>
                    <input
                        v-model="form.nit"
                        type="text"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                        :class="{ 'border-red-500': form.errors.nit }"
                        maxlength="20"
                    />
                    <span v-if="form.errors.nit" class="text-red-500 text-xs mt-1">{{ form.errors.nit }}</span>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Correo</label>
                    <input
                        v-model="form.correo"
                        type="email"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                        :class="{ 'border-red-500': form.errors.correo }"
                        maxlength="20"
                    />
                    <span v-if="form.errors.correo" class="text-red-500 text-xs mt-1">{{ form.errors.correo }}</span>
                    <p class="text-xs text-gray-500 mt-1">Máximo 20 caracteres</p>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Dirección</label>
                    <input
                        v-model="form.direccion"
                        type="text"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                        :class="{ 'border-red-500': form.errors.direccion }"
                        maxlength="20"
                    />
                    <span v-if="form.errors.direccion" class="text-red-500 text-xs mt-1">{{ form.errors.direccion }}</span>
                    <p class="text-xs text-gray-500 mt-1">Máximo 20 caracteres</p>
                </div>

                <div class="flex gap-4">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg disabled:opacity-50 font-medium"
                    >
                        Actualizar
                    </button>
                    <Link
                        href="/admin/proveedores"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-medium"
                    >
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
    proveedor: Object
});

const form = useForm({
    nombre: props.proveedor.nombre || '',
    telefono: props.proveedor.telefono || '',
    nit: props.proveedor.nit || '',
    correo: props.proveedor.correo || '',
    direccion: props.proveedor.direccion || ''
});

const submit = () => {
    form.put(`/admin/proveedores/${props.proveedor.id}`, {
        onSuccess: () => {
            // El mensaje de éxito se mostrará automáticamente desde el layout
        }
    });
};
</script>

