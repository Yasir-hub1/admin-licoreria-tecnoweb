<template>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center py-12">
        <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
            <h2 class="text-2xl font-bold text-center mb-6">Registrarse</h2>

            <form @submit.prevent="submit">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Nombre Completo</label>
                    <input
                        v-model="form.nombre"
                        type="text"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                        required
                    />
                    <span v-if="form.errors.nombre" class="text-red-500 text-sm">{{ form.errors.nombre }}</span>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">CI</label>
                    <input
                        v-model="form.ci"
                        type="text"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                        required
                    />
                    <span v-if="form.errors.ci" class="text-red-500 text-sm">{{ form.errors.ci }}</span>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                    <input
                        v-model="form.email"
                        type="email"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                        required
                    />
                    <span v-if="form.errors.email" class="text-red-500 text-sm">{{ form.errors.email }}</span>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Teléfono</label>
                    <input
                        v-model="form.telefono"
                        type="text"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                    />
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Dirección</label>
                    <input
                        v-model="form.direccion"
                        type="text"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                    />
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Contraseña</label>
                    <input
                        v-model="form.password"
                        type="password"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                        required
                    />
                    <span v-if="form.errors.password" class="text-red-500 text-sm">{{ form.errors.password }}</span>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Confirmar Contraseña</label>
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                        required
                    />
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 disabled:opacity-50"
                >
                    Registrarse
                </button>

                <div class="mt-4 text-center">
                    <Link href="/login" class="text-blue-500 hover:underline">
                        ¿Ya tienes cuenta? Inicia sesión
                    </Link>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    nombre: '',
    ci: '',
    email: '',
    telefono: '',
    direccion: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/register');
};

defineOptions({
    layout: null
});
</script>

