<template>
    <ShopLayout>
        <div class="container mx-auto px-4 py-8 max-w-4xl">
            <div class="bg-white shadow rounded-lg p-6">
                <h1 class="text-3xl font-bold mb-6">Confirmación de Pago</h1>

                <!-- Estado del Pago -->
                <div class="mb-6 p-4 rounded-lg"
                     :class="{
                         'bg-yellow-50 border border-yellow-200': pago.estado === 'pendiente' || pago.estado === 'procesando',
                         'bg-green-50 border border-green-200': pago.estado === 'completado',
                         'bg-red-50 border border-red-200': pago.estado === 'rechazado' || pago.estado === 'cancelado'
                     }">
                    <div class="flex items-center gap-3">
                        <span class="text-3xl">
                            <span v-if="pago.estado === 'pendiente' || pago.estado === 'procesando'">⏳</span>
                            <span v-else-if="pago.estado === 'completado'">✅</span>
                            <span v-else>❌</span>
                        </span>
                        <div>
                            <h3 class="font-semibold text-lg">
                                <span v-if="pago.estado === 'pendiente'">Pago Pendiente</span>
                                <span v-else-if="pago.estado === 'procesando'">Procesando Pago</span>
                                <span v-else-if="pago.estado === 'completado'">Pago Completado</span>
                                <span v-else>Pago Rechazado</span>
                            </h3>
                            <p class="text-sm text-gray-600">
                                Número de pago: {{ pago.nro_pago }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Información de la Venta o Cuota -->
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-4">
                        <span v-if="pago.cuota_pago">Detalles del Pago de Cuota</span>
                        <span v-else>Detalles de la Compra</span>
                    </h2>
                    <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                        <div v-if="pago.cuota_pago && pago.cuota_pago.credito" class="flex justify-between">
                            <span class="text-gray-600">Crédito:</span>
                            <span class="font-medium">#{{ pago.cuota_pago.credito.id }}</span>
                        </div>
                        <div v-if="pago.cuota_pago" class="flex justify-between">
                            <span class="text-gray-600">Cuota:</span>
                            <span class="font-medium">Cuota {{ pago.cuota_pago.numero_cuota }}</span>
                        </div>
                        <div v-if="pago.venta && !pago.cuota_pago" class="flex justify-between">
                            <span class="text-gray-600">Venta:</span>
                            <span class="font-medium">{{ pago.venta?.nro_venta }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Monto Total:</span>
                            <span class="font-bold text-green-600">Bs. {{ Number(pago.monto).toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Método de Pago:</span>
                            <span class="font-medium">
                                <span v-if="pago.tipo_pago === 'qr'">📱 QR PagoFácil</span>
                                <span v-else-if="pago.tipo_pago === 'tigo_money'">📲 Tigo Money</span>
                                <span v-else>{{ pago.tipo_pago }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- QR Code -->
                <div v-if="pago.tipo_pago === 'qr' && pago.qr_image" class="mb-6">
                    <h2 class="text-xl font-semibold mb-4">Escanea el Código QR</h2>
                    <div class="flex justify-center">
                        <div class="bg-white p-4 rounded-lg border-2 border-gray-200">
                            <img :src="pago.qr_image" alt="Código QR" class="w-64 h-64 mx-auto" />
                        </div>
                    </div>
                    <p class="text-center text-sm text-gray-600 mt-4">
                        Usa la app de tu banco o billetera móvil para escanear este código y completar el pago
                    </p>
                </div>

                <!-- Tigo Money -->
                <div v-if="pago.tipo_pago === 'tigo_money'" class="mb-6">
                    <h2 class="text-xl font-semibold mb-4">Pago con Tigo Money</h2>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-sm text-blue-800 mb-2">
                            Se ha enviado una solicitud de pago a tu número de Tigo Money.
                        </p>
                        <p class="text-sm text-blue-700" v-if="pago.nro_transaccion">
                            Número de transacción: <strong>{{ pago.nro_transaccion }}</strong>
                        </p>
                        <p class="text-sm text-blue-700 mt-2">
                            Revisa tu teléfono y confirma el pago desde la app de Tigo Money.
                        </p>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="flex gap-4 mt-6">
                    <button
                        v-if="pago.estado === 'pendiente' || pago.estado === 'procesando'"
                        @click="checkStatus"
                        :disabled="checking"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium disabled:opacity-50"
                    >
                        <span v-if="checking">Verificando...</span>
                        <span v-else>Verificar Estado del Pago</span>
                    </button>
                    <Link
                        v-if="pago.estado === 'completado'"
                        :href="pago.cuota_pago && pago.cuota_pago.credito ? `/my-credit/${pago.cuota_pago.credito.id}` : '/my-orders'"
                        class="flex-1 bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium text-center"
                    >
                        <span v-if="pago.cuota_pago">Volver al Crédito</span>
                        <span v-else>Ver Mis Compras</span>
                    </Link>
                    <Link
                        href="/shop"
                        class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium text-center"
                    >
                        Continuar Comprando
                    </Link>
                </div>

                <!-- Auto-refresh para pagos pendientes -->
                <div v-if="(pago.estado === 'pendiente' || pago.estado === 'procesando') && autoCheck" class="mt-4 text-center">
                    <p class="text-sm text-gray-500">
                        Verificando automáticamente cada 10 segundos...
                    </p>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';

const props = defineProps({
    pago: Object
});

const checking = ref(false);
const autoCheck = ref(true);
let intervalId = null;

const checkStatus = async () => {
    checking.value = true;
    try {
        await router.get(`/payment/check-status/${props.pago.id}`, {}, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: (page) => {
                // El estado se actualizará automáticamente cuando se recargue la página
                if (page.props.pago?.estado === 'completado') {
                    autoCheck.value = false;
                    if (intervalId) {
                        clearInterval(intervalId);
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error verificando estado:', error);
    } finally {
        checking.value = false;
    }
};

onMounted(() => {
    // Auto-verificar cada 10 segundos si el pago está pendiente
    if (props.pago.estado === 'pendiente' || props.pago.estado === 'procesando') {
        intervalId = setInterval(() => {
            checkStatus();
        }, 10000);
    }
});

onUnmounted(() => {
    if (intervalId) {
        clearInterval(intervalId);
    }
});
</script>

