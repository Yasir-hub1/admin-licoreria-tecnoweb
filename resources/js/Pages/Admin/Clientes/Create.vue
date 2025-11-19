<template>
    <AdminLayout title="Nuevo Cliente" subtitle="Registra un nuevo cliente en el sistema">
        <div class="space-y-6">

            <div v-motion-slide-bottom class="bg-white shadow-xl rounded-2xl p-8 max-w-2xl border border-gray-100">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <TextInput
                            v-model="form.telefono"
                            label="Teléfono"
                            type="tel"
                            name="telefono"
                            placeholder="+591 12345678"
                            :validation-rules="[validateTelefono]"
                        />

                        <SelectInput
                            v-model="form.estado"
                            label="Estado"
                            name="estado"
                            required
                            :options="estadoOptions"
                            option-value="value"
                            option-label="label"
                        />
                    </div>

                    <TextInput
                        v-model="form.direccion"
                        label="Dirección"
                        type="text"
                        name="direccion"
                        placeholder="Calle, Número, Ciudad"
                    />

                    <div class="flex gap-4 pt-4">
                        <Button
                            type="submit"
                            :loading="form.processing"
                            :disabled="form.processing"
                            variant="primary"
                            size="lg"
                        >
                            Guardar Cliente
                        </Button>
                        <Link href="/admin/clientes">
                            <Button
                                type="button"
                                variant="outline"
                                size="lg"
                            >
                                Cancelar
                            </Button>
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TextInput from '@/Components/Form/TextInput.vue';
import SelectInput from '@/Components/Form/SelectInput.vue';
import Button from '@/Components/Button.vue';

const form = useForm({
    ci: '',
    nombre: '',
    telefono: '',
    direccion: '',
    estado: 'A'
});

const estadoOptions = [
    { value: 'A', label: 'Activo' },
    { value: 'I', label: 'Inactivo' }
];

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

const submit = () => form.post('/admin/clientes');
</script>

