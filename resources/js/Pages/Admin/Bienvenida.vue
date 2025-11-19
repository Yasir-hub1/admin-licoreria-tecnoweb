<template>
    <AdminLayout title="Bienvenido" subtitle="Panel de acceso">
        <div class="space-y-6">
            <!-- Mensaje de bienvenida -->
            <div v-motion-slide-bottom class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-2xl p-8 text-white shadow-xl">
                <div class="flex items-center gap-4">
                    <div class="bg-white/20 rounded-full p-4">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold mb-2">¡Bienvenido, {{ usuario.nombre }}!</h1>
                        <p class="text-blue-100 text-lg">
                            Has iniciado sesión como <span class="font-semibold">{{ usuario.rol || 'Usuario' }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Módulos disponibles -->
            <div v-motion-slide-bottom class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 border border-gray-200 dark:border-gray-700">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-6 flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Módulos Disponibles
                </h2>

                <div v-if="modulosDisponibles.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <Link
                        v-for="modulo in modulosDisponibles"
                        :key="modulo.slug"
                        :href="modulo.ruta"
                        class="group relative overflow-hidden bg-gradient-to-br from-white to-gray-50 dark:from-gray-700 dark:to-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-600 hover:border-blue-500 dark:hover:border-blue-400 transition-all duration-300 hover:shadow-lg hover:scale-105"
                    >
                        <div class="flex items-start gap-4">
                            <div class="text-4xl group-hover:scale-110 transition-transform duration-300">
                                {{ modulo.icon }}
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-1 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                    {{ modulo.nombre }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Acceder al módulo
                                </p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-500 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </Link>
                </div>

                <div v-else class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 text-lg">
                        No tienes módulos disponibles en este momento
                    </p>
                </div>
            </div>

            <!-- Información adicional -->
            <div v-motion-slide-bottom class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-6 border border-blue-200 dark:border-blue-800">
                <div class="flex items-start gap-4">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <h3 class="font-semibold text-blue-900 dark:text-blue-200 mb-2">Información</h3>
                        <p class="text-sm text-blue-800 dark:text-blue-300">
                            Solo puedes acceder a los módulos para los cuales tienes permisos asignados.
                            Si necesitas acceso a más funcionalidades, contacta al administrador del sistema.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { MotionDirective } from '@vueuse/motion';

const vMotionSlideBottom = MotionDirective({
    initial: { y: 20, opacity: 0 },
    enter: { y: 0, opacity: 1 },
    transition: { duration: 300 }
});

defineProps({
    usuario: {
        type: Object,
        required: true
    },
    modulosDisponibles: {
        type: Array,
        default: () => []
    }
});
</script>

