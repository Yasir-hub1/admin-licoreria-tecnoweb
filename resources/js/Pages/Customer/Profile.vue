<template>
    <ShopLayout>
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold mb-6">Mi Perfil</h1>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Información del Usuario -->
                <div class="lg:col-span-2">
                    <div class="bg-white shadow rounded-lg p-6 mb-6">
                        <h2 class="text-xl font-semibold mb-4">Información Personal</h2>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 mb-1">Nombre Completo</dt>
                                <dd class="text-sm text-gray-900 font-medium">{{ user?.nombre }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 mb-1">Email</dt>
                                <dd class="text-sm text-gray-900 font-medium">{{ user?.email }}</dd>
                            </div>
                            <div v-if="cliente">
                                <dt class="text-sm font-medium text-gray-500 mb-1">CI</dt>
                                <dd class="text-sm text-gray-900 font-medium">{{ cliente.ci }}</dd>
                            </div>
                            <div v-if="cliente">
                                <dt class="text-sm font-medium text-gray-500 mb-1">Teléfono</dt>
                                <dd class="text-sm text-gray-900 font-medium">{{ cliente.telefono || 'No especificado' }}</dd>
                            </div>
                            <div v-if="cliente" class="md:col-span-2">
                                <dt class="text-sm font-medium text-gray-500 mb-1">Dirección</dt>
                                <dd class="text-sm text-gray-900 font-medium">{{ cliente.direccion || 'No especificada' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div v-if="cliente" class="bg-white shadow rounded-lg p-6">
                        <h2 class="text-xl font-semibold mb-4">Información de Crédito</h2>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 mb-1">Estado de Crédito</dt>
                                <dd>
                                    <span
                                        class="px-3 py-1 rounded-full text-xs font-medium"
                                        :class="cliente.credito_aprobado ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                                    >
                                        {{ cliente.credito_aprobado ? 'Aprobado' : 'Pendiente' }}
                                    </span>
                                </dd>
                            </div>
                            <div v-if="cliente.credito_aprobado">
                                <dt class="text-sm font-medium text-gray-500 mb-1">Límite de Crédito</dt>
                                <dd class="text-sm text-gray-900 font-medium">Bs. {{ Number(cliente.limite_credito || 0).toFixed(2) }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                <div class="lg:col-span-1">
                    <div class="bg-white shadow rounded-lg p-6">
                        <h2 class="text-xl font-semibold mb-4">Acciones Rápidas</h2>
                        <div class="space-y-3">
                            <Link
                                href="/my-orders"
                                class="block w-full bg-blue-500 hover:bg-blue-600 text-white text-center px-4 py-2 rounded-lg font-medium transition-colors"
                            >
                                Mis Compras
                            </Link>
                            <Link
                                href="/my-credits"
                                class="block w-full bg-green-500 hover:bg-green-600 text-white text-center px-4 py-2 rounded-lg font-medium transition-colors"
                            >
                                Mis Créditos
                            </Link>
                            <Link
                                href="/shop"
                                class="block w-full bg-gray-500 hover:bg-gray-600 text-white text-center px-4 py-2 rounded-lg font-medium transition-colors"
                            >
                                Ir a la Tienda
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';

defineProps({
    user: Object,
    cliente: Object
});
</script>
