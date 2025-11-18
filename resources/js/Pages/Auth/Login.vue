<template>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center">
        <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
            <h2 class="text-2xl font-bold text-center mb-6">Iniciar Sesión</h2>

            <div v-if="$page.props.flash?.error" class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                {{ $page.props.flash.error }}
            </div>

            <form @submit.prevent="submit">
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
                    <label class="block text-gray-700 text-sm font-bold mb-2">Contraseña</label>
                    <input
                        v-model="form.password"
                        type="password"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                        required
                    />
                    <span v-if="form.errors.password" class="text-red-500 text-sm">{{ form.errors.password }}</span>
                </div>

                <div class="mb-4 flex items-center">
                    <input v-model="form.remember" type="checkbox" class="mr-2" />
                    <label class="text-sm text-gray-700">Recordarme</label>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 disabled:opacity-50"
                >
                    Iniciar Sesión
                </button>

                <div class="mt-4 text-center">
                    <Link href="/register" class="text-blue-500 hover:underline">
                        ¿No tienes cuenta? Regístrate
                    </Link>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm, Link, router } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login');
};

defineOptions({
    layout: null
});
</script>

