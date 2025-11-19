<template>
    <AdminLayout title="Editar Categoría" subtitle="Modifica la información de la categoría">
        <div class="space-y-6">
            <div v-motion-slide-bottom class="bg-white shadow-xl rounded-2xl p-8 max-w-md border border-gray-100">
                <form @submit.prevent="submit" class="space-y-6">
                    <TextInput
                        v-model="form.nombre"
                        label="Nombre"
                        type="text"
                        name="nombre"
                        placeholder="Ej: Whisky, Vodka, Ron..."
                        required
                        :error="form.errors.nombre"
                        :min-length="2"
                        hint="Mínimo 2 caracteres"
                    />

                    <div class="flex gap-4 pt-4">
                        <Button
                            type="submit"
                            :loading="form.processing"
                            :disabled="form.processing"
                            variant="primary"
                            size="lg"
                            full-width
                        >
                            Actualizar Categoría
                        </Button>
                        <Link href="/admin/categorias">
                            <Button
                                type="button"
                                variant="outline"
                                size="lg"
                                full-width
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

const props = defineProps({ categoria: Object });
const form = useForm({ nombre: props.categoria.nombre });
const submit = () => form.put(`/admin/categorias/${props.categoria.id}`);
</script>
