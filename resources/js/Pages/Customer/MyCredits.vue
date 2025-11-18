<template>
    <ShopLayout>
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold mb-6">Mis Créditos</h1>

            <div v-if="creditos.data && creditos.data.length > 0" class="space-y-4">
                <div v-for="credito in creditos.data" :key="credito.id" class="bg-white shadow rounded-lg p-6 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-4 mb-2">
                                <h3 class="text-lg font-semibold text-gray-800">Crédito #{{ credito.id }}</h3>
                                <span
                                    class="px-3 py-1 rounded-full text-xs font-medium"
                                    :class="{
                                        'bg-green-100 text-green-800': credito.estado === 'pagado',
                                        'bg-blue-100 text-blue-800': credito.estado === 'activo',
                                        'bg-red-100 text-red-800': credito.estado === 'mora'
                                    }"
                                >
                                    {{ credito.estado }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 mb-2">
                                Fecha inicio: {{ new Date(credito.fecha_inicio).toLocaleDateString('es-ES') }}
                            </p>
                            <div class="grid grid-cols-2 gap-4 mt-3">
                                <div>
                                    <p class="text-sm text-gray-600">Monto Total:</p>
                                    <p class="text-lg font-bold text-gray-800">Bs. {{ Number(credito.monto_total).toFixed(2) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Saldo Pendiente:</p>
                                    <p class="text-lg font-bold text-red-600">Bs. {{ Number(credito.saldo).toFixed(2) }}</p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <p class="text-sm text-gray-600">
                                    Cuotas: {{ credito.numero_cuotas }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    Cuota mensual: Bs. {{ (credito.monto_total / credito.numero_cuotas).toFixed(2) }}
                                </p>
                            </div>
                        </div>
                        <Link
                            :href="`/my-credit/${credito.id}`"
                            class="ml-4 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                        >
                            Ver Detalle
                        </Link>
                    </div>
                </div>

                <!-- Paginación -->
                <div v-if="creditos.links && creditos.links.length > 3" class="mt-6 flex justify-center">
                    <nav class="flex gap-2">
                        <Link
                            v-for="(link, index) in creditos.links"
                            :key="index"
                            :href="link.url || '#'"
                            v-html="link.label"
                            :class="[
                                'px-3 py-2 border rounded-lg',
                                link.active ? 'bg-blue-500 text-white border-blue-500' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                            ]"
                        />
                    </nav>
                </div>
            </div>

            <div v-else class="text-center py-16">
                <div class="text-6xl mb-4">💳</div>
                <h2 class="text-2xl font-semibold text-gray-800 mb-2">No tienes créditos registrados</h2>
                <p class="text-gray-600 mb-6">Realiza una compra a crédito para ver tus cuotas aquí</p>
                <Link
                    href="/shop"
                    class="inline-block bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-medium"
                >
                    Ir al Catálogo
                </Link>
            </div>
        </div>
    </ShopLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';

defineProps({
    creditos: Object
});
</script>
