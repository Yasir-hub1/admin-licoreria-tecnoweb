<template>
    <AdminLayout title="Editar Producto" subtitle="Modifica la información del producto">
        <div class="space-y-6">
            <div v-motion-slide-bottom class="bg-white shadow-xl rounded-2xl p-8 max-w-2xl border border-gray-100">
                <div class="mb-6 p-4 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-lg">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm text-amber-800">
                            <strong>Información:</strong> El código del producto no puede ser modificado.
                        </p>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <TextInput
                        :value="producto.codigo"
                        label="Código"
                        type="text"
                        name="codigo"
                        readonly
                        disabled
                        hint="El código del producto no puede ser modificado"
                    />

                    <TextInput
                        v-model="form.nombre"
                        label="Nombre"
                        type="text"
                        name="nombre"
                        placeholder="Ej: Whisky Johnnie Walker"
                        required
                        :error="form.errors.nombre"
                        :min-length="3"
                        hint="Mínimo 3 caracteres"
                    />

                    <TextareaInput
                        v-model="form.descripcion"
                        label="Descripción"
                        name="descripcion"
                        placeholder="Descripción detallada del producto..."
                        :rows="4"
                        :max-length="500"
                        hint="Máximo 500 caracteres"
                    />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <NumberInput
                            v-model="form.precio"
                            label="Precio"
                            name="precio"
                            placeholder="0.00"
                            required
                            :error="form.errors.precio"
                            :min="0"
                            :step="0.01"
                            hint="Precio en bolivianos"
                        />

                        <TextInput
                            v-model="form.marca"
                            label="Marca"
                            type="text"
                            name="marca"
                            placeholder="Ej: Johnnie Walker"
                        />
                    </div>

                    <SelectInput
                        v-model="form.categoria_id"
                        label="Categoría"
                        name="categoria_id"
                        placeholder="Seleccione una categoría"
                        required
                        :error="form.errors.categoria_id"
                        :options="categorias"
                        option-value="id"
                        option-label="nombre"
                    />

                    <div class="flex gap-4 pt-4">
                        <Button
                            type="submit"
                            :loading="form.processing"
                            :disabled="form.processing"
                            variant="primary"
                            size="lg"
                        >
                            Actualizar Producto
                        </Button>
                        <Link href="/admin/productos">
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
import TextareaInput from '@/Components/Form/TextareaInput.vue';
import NumberInput from '@/Components/Form/NumberInput.vue';
import SelectInput from '@/Components/Form/SelectInput.vue';
import Button from '@/Components/Button.vue';

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
