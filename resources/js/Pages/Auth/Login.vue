<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 flex items-center justify-center p-4">
        <div
            v-motion-fade
            class="max-w-md w-full bg-white/90 backdrop-blur-sm rounded-2xl shadow-2xl p-8 border border-gray-100"
        >
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent mb-2">
                    Iniciar Sesión
                </h2>
                <p class="text-gray-600 text-sm">Bienvenido de nuevo</p>
            </div>

            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="transform opacity-0 scale-95"
                enter-to-class="transform opacity-100 scale-100"
            >
                <div v-if="$page.props.flash?.error" class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $page.props.flash.error }}</span>
                </div>
            </Transition>

            <form @submit.prevent="submit" class="space-y-5">
                <TextInput
                    v-model="form.email"
                    label="Email"
                    type="email"
                    name="email"
                    placeholder="tu@email.com"
                    required
                    :error="form.errors.email"
                    :validation-rules="[validateEmail]"
                />

                <div>
                    <TextInput
                        v-model="form.password"
                        label="Contraseña"
                        type="password"
                        name="password"
                        placeholder="••••••••"
                        required
                        :error="form.errors.password"
                        :min-length="6"
                        hint="Mínimo 6 caracteres"
                    />
                </div>

                <CheckboxInput
                    v-model="form.remember"
                    label="Recordarme"
                />

                <Button
                    type="submit"
                    :loading="form.processing"
                    :disabled="form.processing"
                    variant="primary"
                    full-width
                    size="lg"
                    class="w-full"
                >
                    Iniciar Sesión
                </Button>

                <div class="mt-6 text-center">
                    <Link href="/register" class="text-blue-600 hover:text-blue-700 font-medium transition-colors duration-200">
                        ¿No tienes cuenta? <span class="underline">Regístrate</span>
                    </Link>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import TextInput from '@/Components/Form/TextInput.vue';
import CheckboxInput from '@/Components/Form/CheckboxInput.vue';
import Button from '@/Components/Button.vue';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const validateEmail = (value) => {
    if (!value) return true;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(value) || 'Email inválido';
};

const submit = () => {
    form.post('/login');
};

defineOptions({
    layout: null
});
</script>

