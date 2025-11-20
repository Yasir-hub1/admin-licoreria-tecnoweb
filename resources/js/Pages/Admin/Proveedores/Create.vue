<template>
    <AdminLayout title="Nuevo Proveedor" subtitle="Registra un nuevo proveedor en el sistema">
        <div class="space-y-6">
            <div v-if="$page.props.flash?.error" class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg flex items-start gap-3">
                <svg class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p>{{ $page.props.flash.error }}</p>
            </div>

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
                            :max-length="50"
                            hint="Máximo 50 caracteres"
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

                    <!-- Sección de Usuario -->
                    <div class="border-t pt-6 mt-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Acceso al Sistema (Opcional)</h3>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    v-model="form.crear_usuario"
                                    class="sr-only peer"
                                />
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                <span class="ml-3 text-sm font-medium text-gray-700">Crear usuario</span>
                            </label>
                        </div>

                        <div v-if="form.crear_usuario" class="space-y-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex items-center gap-4 mb-4">
                                <label class="flex items-center">
                                    <input
                                        type="radio"
                                        v-model="tipoUsuario"
                                        value="nuevo"
                                        class="mr-2"
                                    />
                                    <span class="text-sm font-medium">Crear Usuario Nuevo</span>
                                </label>
                                <label class="flex items-center">
                                    <input
                                        type="radio"
                                        v-model="tipoUsuario"
                                        value="existente"
                                        class="mr-2"
                                    />
                                    <span class="text-sm font-medium">Asignar Usuario Existente</span>
                                </label>
                            </div>

                            <!-- Crear Usuario Nuevo -->
                            <div v-if="tipoUsuario === 'nuevo'" class="space-y-4">
                                <TextInput
                                    v-model="form.usuario_email"
                                    label="Email del Usuario"
                                    type="email"
                                    name="usuario_email"
                                    placeholder="usuario@ejemplo.com"
                                    required
                                    :error="form.errors.usuario_email"
                                />

                                <TextInput
                                    v-model="form.usuario_password"
                                    label="Contraseña"
                                    type="password"
                                    name="usuario_password"
                                    placeholder="Mínimo 8 caracteres"
                                    required
                                    :error="form.errors.usuario_password"
                                    hint="Mínimo 8 caracteres"
                                />

                                <SelectInput
                                    v-model="form.usuario_rol_id"
                                    label="Rol"
                                    name="usuario_rol_id"
                                    placeholder="Seleccione un rol"
                                    required
                                    :error="form.errors.usuario_rol_id"
                                    :options="roles"
                                    option-value="id"
                                    option-label="nombre"
                                />
                            </div>

                            <!-- Asignar Usuario Existente -->
                            <div v-if="tipoUsuario === 'existente'" class="space-y-4">
                                <SelectInput
                                    v-model="form.usuario_id"
                                    label="Usuario Existente"
                                    name="usuario_id"
                                    placeholder="Seleccione un usuario"
                                    required
                                    :error="form.errors.usuario_id"
                                    :options="usuariosDisponibles.map(u => ({
                                        id: u.id,
                                        nombre: `${u.nombre} (${u.email}) - ${u.rol?.nombre || 'Sin rol'}`
                                    }))"
                                    option-value="id"
                                    option-label="nombre"
                                />
                                <p class="text-xs text-gray-500">Solo se muestran usuarios que no tienen proveedor asignado</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4 pt-4">
                        <Button
                            type="submit"
                            :loading="form.processing"
                            :disabled="form.processing"
                            variant="primary"
                            size="lg"
                        >
                            Guardar Proveedor
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
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TextInput from '@/Components/Form/TextInput.vue';
import SelectInput from '@/Components/Form/SelectInput.vue';
import Button from '@/Components/Button.vue';

const props = defineProps({
    usuariosDisponibles: {
        type: Array,
        default: () => []
    },
    roles: {
        type: Array,
        default: () => []
    }
});

const tipoUsuario = ref('nuevo');

const form = useForm({
    nombre: '',
    telefono: '',
    nit: '',
    correo: '',
    direccion: '',
    crear_usuario: false,
    usuario_id: null,
    usuario_email: '',
    usuario_password: '',
    usuario_rol_id: null
});

const submit = () => {
    // Limpiar campos no utilizados según el tipo de usuario
    if (!form.crear_usuario) {
        form.usuario_id = null;
        form.usuario_email = '';
        form.usuario_password = '';
        form.usuario_rol_id = null;
    } else if (tipoUsuario.value === 'nuevo') {
        form.usuario_id = null;
    } else {
        form.usuario_email = '';
        form.usuario_password = '';
        form.usuario_rol_id = null;
    }

    form.post('/admin/proveedores', {
        onSuccess: () => {
            // El mensaje de éxito se mostrará automáticamente desde el layout
        }
    });
};
</script>
