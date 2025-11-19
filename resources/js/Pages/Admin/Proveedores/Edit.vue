<template>
    <AdminLayout title="Editar Proveedor" subtitle="Modifica la información del proveedor">
        <div class="space-y-6">
            <div v-motion-slide-bottom class="bg-white shadow-xl rounded-2xl p-8 max-w-2xl border border-gray-100">
                <form @submit.prevent="submit" class="space-y-6">
                    <TextInput
                        v-model="form.nombre"
                        label="Nombre"
                        type="text"
                        name="nombre"
                        placeholder="Ej: Distribuidora ABC"
                        required
                        :error="form.errors.nombre"
                        :max-length="50"
                        hint="Máximo 50 caracteres"
                    />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <TextInput
                            v-model="form.telefono"
                            label="Teléfono"
                            type="tel"
                            name="telefono"
                            placeholder="+591 12345678"
                            :error="form.errors.telefono"
                            :max-length="20"
                            hint="Máximo 20 caracteres"
                        />

                        <TextInput
                            v-model="form.nit"
                            label="NIT"
                            type="text"
                            name="nit"
                            placeholder="123456789"
                            :error="form.errors.nit"
                            :max-length="20"
                            hint="Máximo 20 caracteres"
                        />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <TextInput
                            v-model="form.correo"
                            label="Correo"
                            type="email"
                            name="correo"
                            placeholder="proveedor@ejemplo.com"
                            :error="form.errors.correo"
                            :max-length="20"
                            hint="Máximo 20 caracteres"
                        />

                        <TextInput
                            v-model="form.direccion"
                            label="Dirección"
                            type="text"
                            name="direccion"
                            placeholder="Calle, Número, Ciudad"
                            :error="form.errors.direccion"
                            :max-length="20"
                            hint="Máximo 20 caracteres"
                        />
                    </div>

                    <div class="flex gap-4 pt-4">
                        <Button
                            type="submit"
                            :loading="form.processing"
                            :disabled="form.processing"
                            variant="primary"
                            size="lg"
                        >
                            Actualizar Proveedor
                        </Button>
                        <Link href="/admin/proveedores">
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
import Button from '@/Components/Button.vue';

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
