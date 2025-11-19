<template>
    <AdminLayout title="Editar Empleado" subtitle="Modifica la información del empleado">
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

                    <SelectInput
                        v-model="form.usuario_id"
                        label="Usuario"
                        name="usuario_id"
                        placeholder="Seleccione un usuario"
                        required
                        :error="form.errors.usuario_id"
                        :options="usuarios"
                        option-value="id"
                        option-label="nombre"
                        hint="Usuario que tendrá acceso al sistema"
                    />

                    <div class="flex gap-4 pt-4">
                        <Button
                            type="submit"
                            :loading="form.processing"
                            :disabled="form.processing"
                            variant="primary"
                            size="lg"
                        >
                            Actualizar Empleado
                        </Button>
                        <Link href="/admin/empleados">
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

const props = defineProps({ empleado: Object, usuarios: Array });

const form = useForm({
    ci: props.empleado.ci,
    nombre: props.empleado.nombre,
    usuario_id: props.empleado.usuario_id
});

const validateCI = (value) => {
    if (!value) return true;
    const ciRegex = /^\d{5,10}$/;
    return ciRegex.test(value) || 'CI debe tener entre 5 y 10 dígitos';
};

const submit = () => form.put(`/admin/empleados/${props.empleado.id}`);
</script>
