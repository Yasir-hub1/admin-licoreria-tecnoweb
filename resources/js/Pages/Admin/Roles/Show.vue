<template>
    <AdminLayout :title="`Rol: ${rol.nombre}`" subtitle="Detalles del rol y sus permisos">
        <div class="space-y-6">
            <!-- Información del Rol -->
            <div v-motion-slide-bottom class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl p-6 border border-gray-100 dark:border-gray-700">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ rol.nombre }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ rol.descripcion || 'Sin descripción' }}</p>
                    </div>
                    <Link
                        v-if="puedeEditar"
                        :href="`/admin/roles/${rol.id}/edit`"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors"
                    >
                        Editar Rol
                    </Link>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <div class="text-sm text-gray-600 dark:text-gray-400">Usuarios</div>
                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ rol.usuarios?.length || 0 }}</div>
                    </div>
                    <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <div class="text-sm text-gray-600 dark:text-gray-400">Permisos</div>
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ rol.permisos?.length || 0 }}</div>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="text-sm text-gray-600 dark:text-gray-400">Estado</div>
                        <div class="text-2xl font-bold text-gray-800 dark:text-gray-200 mt-1">Activo</div>
                    </div>
                </div>
            </div>

            <!-- Usuarios con este Rol -->
            <div v-motion-slide-bottom class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">Usuarios con este Rol</h3>
                </div>
                <div class="p-6">
                    <div v-if="rol.usuarios && rol.usuarios.length > 0" class="space-y-2">
                        <div
                            v-for="usuario in rol.usuarios"
                            :key="usuario.id"
                            class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
                        >
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">{{ usuario.nombre }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ usuario.email }}</div>
                            </div>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                :class="usuario.estado === 'activo' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'">
                                {{ usuario.estado }}
                            </span>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                        No hay usuarios con este rol
                    </div>
                </div>
            </div>

            <!-- Permisos del Rol -->
            <div v-motion-slide-bottom class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">Permisos Asignados</h3>
                </div>
                <div class="p-6">
                    <div v-if="rol.permisos && rol.permisos.length > 0" class="space-y-4">
                        <div
                            v-for="(permisosModulo, modulo) in permisosAgrupados"
                            :key="modulo"
                            class="border border-gray-200 dark:border-gray-700 rounded-lg p-4"
                        >
                            <h4 class="font-semibold text-gray-800 dark:text-white mb-3 flex items-center gap-2">
                                <span class="text-lg">{{ getModuloIcon(modulo) }}</span>
                                {{ modulo || 'General' }}
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                <div
                                    v-for="permiso in permisosModulo"
                                    :key="permiso.id"
                                    class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
                                >
                                    <div class="font-medium text-sm text-gray-900 dark:text-white">{{ permiso.nombre }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ permiso.descripcion || '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                        Este rol no tiene permisos asignados
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { usePermissions } from '@/composables/usePermissions';

const props = defineProps({
    rol: Object
});

const { tienePermiso } = usePermissions();

// Hacer reactivos los permisos usando computed
const puedeEditar = computed(() => tienePermiso('roles.editar'));

const permisosAgrupados = computed(() => {
    if (!props.rol.permisos || props.rol.permisos.length === 0) {
        return {};
    }

    return props.rol.permisos.reduce((acc, permiso) => {
        const modulo = permiso.modulo || 'General';
        if (!acc[modulo]) {
            acc[modulo] = [];
        }
        acc[modulo].push(permiso);
        return acc;
    }, {});
});

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
        'Estadísticas': '📈',
        'Dashboard': '📊',
        'General': '⚙️'
    };
    return icons[modulo] || '📋';
};
</script>
