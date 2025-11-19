<template>
    <AdminLayout title="Editar Rol" subtitle="Modifica el rol y sus permisos">
        <div class="space-y-6">
            <div v-motion-slide-bottom class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl p-6 border border-gray-100 dark:border-gray-700 max-w-4xl">
                <form @submit.prevent="submit">
                    <div class="space-y-6">
                        <!-- Información básica -->
                        <div>
                            <h3 class="text-lg font-bold mb-4 text-gray-800 dark:text-white">Información del Rol</h3>
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Nombre del Rol *
                                    </label>
                                    <input
                                        v-model="form.nombre"
                                        type="text"
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                        required
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Descripción
                                    </label>
                                    <textarea
                                        v-model="form.descripcion"
                                        rows="3"
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Permisos -->
                        <div>
                            <h3 class="text-lg font-bold mb-4 text-gray-800 dark:text-white">Permisos</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Selecciona los permisos que tendrá este rol</p>

                            <div class="space-y-4">
                                <div v-for="(permisosModulo, modulo) in permisos" :key="modulo" class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <h4 class="font-semibold text-gray-800 dark:text-white mb-3 flex items-center gap-2">
                                        <span class="text-lg">{{ getModuloIcon(modulo) }}</span>
                                        {{ modulo || 'General' }}
                                    </h4>
                                    <div class="space-y-2">
                                        <label
                                            v-for="permiso in permisosModulo"
                                            :key="permiso.id"
                                            class="flex items-start gap-3 p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors"
                                            :class="{
                                                'bg-blue-50 dark:bg-blue-900/20 border-blue-300 dark:border-blue-600': form.permisos.includes(permiso.id)
                                            }"
                                        >
                                            <input
                                                type="checkbox"
                                                :value="permiso.id"
                                                v-model="form.permisos"
                                                class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 flex-shrink-0"
                                            />
                                            <div class="flex-1 min-w-0">
                                                <div class="font-medium text-sm text-gray-900 dark:text-white">{{ permiso.nombre }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ permiso.descripcion || '-' }}</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="flex gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors disabled:opacity-50"
                            >
                                {{ form.processing ? 'Actualizando...' : 'Actualizar Rol' }}
                            </button>
                            <Link
                                href="/admin/roles"
                                class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-medium transition-colors"
                            >
                                Cancelar
                            </Link>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    rol: Object,
    permisos: Object,
    permisosSeleccionados: Array
});

const form = useForm({
    nombre: props.rol.nombre,
    descripcion: props.rol.descripcion || '',
    permisos: props.permisosSeleccionados || []
});

const submit = () => {
    form.put(`/admin/roles/${props.rol.id}`);
};

const getModuloIcon = (modulo) => {
    const icons = {
        'Productos': '📦',
        'Categorías': '🏷️',
        'Ventas': '💰',
        'Compras': '🛒',
        'Clientes': '👥',
        'Proveedores': '🏢',
        'Inventario': '📊',
        'Créditos': '💳',
        'Usuarios': '👤',
        'Roles': '🔐',
        'Empleados': '👔',
        'Estadísticas': '📈',
        'Dashboard': '📊',
        'General': '⚙️'
    };
    return icons[modulo] || '📋';
};
</script>
