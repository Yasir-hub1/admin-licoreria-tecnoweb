<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 flex items-center justify-center p-4 py-12">
        <div
            v-motion-fade
            class="max-w-md w-full bg-white/90 backdrop-blur-sm rounded-2xl shadow-2xl p-8 border border-gray-100"
        >
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent mb-2">
                    Crear Cuenta
                </h2>
                <p class="text-gray-600 text-sm">Únete a nuestra plataforma</p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <TextInput
                    v-model="form.nombre"
                    label="Nombre Completo"
                    type="text"
                    name="nombre"
                    placeholder="Juan Pérez"
                    required
                    :error="form.errors.nombre"
                    :min-length="3"
                    hint="Mínimo 3 caracteres"
                />

                <TextInput
                    v-model="form.ci"
                    label="CI"
                    type="text"
                    name="ci"
                    placeholder="12345678"
                    required
                    :error="form.errors.ci"
                    :validation-rules="[validateCI]"
                />

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

                <TextInput
                    v-model="form.telefono"
                    label="Teléfono"
                    type="tel"
                    name="telefono"
                    placeholder="+591 12345678"
                    :validation-rules="[validateTelefono]"
                />

                <TextInput
                    v-model="form.direccion"
                    label="Dirección"
                    type="text"
                    name="direccion"
                    placeholder="Calle, Número, Ciudad"
                />

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

                <TextInput
                    v-model="form.password_confirmation"
                    label="Confirmar Contraseña"
                    type="password"
                    name="password_confirmation"
                    placeholder="••••••••"
                    required
                    :error="passwordMatchError"
                    :validation-rules="[validatePasswordMatch]"
                />

                <Button
                    type="submit"
                    :loading="form.processing"
                    :disabled="form.processing"
                    variant="primary"
                    full-width
                    size="lg"
                    class="w-full mt-6"
                >
                    Registrarse
                </Button>

                <div class="mt-6 text-center">
                    <Link href="/login" class="text-blue-600 hover:text-blue-700 font-medium transition-colors duration-200">
                        ¿Ya tienes cuenta? <span class="underline">Inicia sesión</span>
                    </Link>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import TextInput from '@/Components/Form/TextInput.vue';
import Button from '@/Components/Button.vue';

const form = useForm({
    nombre: '',
    ci: '',
    email: '',
    telefono: '',
    direccion: '',
    password: '',
    password_confirmation: '',
});

const validateEmail = (value) => {
    if (!value) return true;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(value) || 'Email inválido';
};

const validateCI = (value) => {
    if (!value) return true;
    const ciRegex = /^\d{5,10}$/;
    return ciRegex.test(value) || 'CI debe tener entre 5 y 10 dígitos';
};

const validateTelefono = (value) => {
    if (!value) return true;
    const telefonoRegex = /^[\d\s\+\-\(\)]{7,15}$/;
    return telefonoRegex.test(value) || 'Teléfono inválido';
};

const validatePasswordMatch = (value) => {
    if (!value) return true;
    return value === form.password || 'Las contraseñas no coinciden';
};

const passwordMatchError = computed(() => {
    if (form.password_confirmation && form.password_confirmation !== form.password) {
        return 'Las contraseñas no coinciden';
    }
    return '';
});

const submit = () => {
    form.post('/register');
};

defineOptions({
    layout: null
});
</script>

