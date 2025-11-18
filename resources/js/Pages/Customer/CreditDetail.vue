<template>
    <ShopLayout>
        <div class="container mx-auto px-4 py-8">
            <div class="mb-6">
                <Link href="/my-credits" class="text-blue-600 hover:text-blue-800 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Volver a Mis Créditos
                </Link>
            </div>

            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h1 class="text-3xl font-bold mb-6">Detalle de Crédito #{{ credito.id }}</h1>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-600 mb-1">Monto Total</p>
                        <p class="text-2xl font-bold text-blue-600">Bs. {{ Number(credito.monto_total).toFixed(2) }}</p>
                    </div>
                    <div class="bg-red-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-600 mb-1">Saldo Pendiente</p>
                        <p class="text-2xl font-bold text-red-600">Bs. {{ Number(credito.saldo).toFixed(2) }}</p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-600 mb-1">Pagado</p>
                        <p class="text-2xl font-bold text-green-600">Bs. {{ (credito.monto_total - credito.saldo).toFixed(2) }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Información del Crédito</h3>
                        <dl class="space-y-2">
                            <div>
                                <dt class="text-sm text-gray-600">Estado:</dt>
                                <dd>
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
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-600">Fecha Inicio:</dt>
                                <dd class="font-medium">{{ new Date(credito.fecha_inicio).toLocaleDateString('es-ES') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-600">Número de Cuotas:</dt>
                                <dd class="font-medium">{{ credito.numero_cuotas }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-600">Cuota Mensual:</dt>
                                <dd class="font-medium">Bs. {{ (credito.monto_total / credito.numero_cuotas).toFixed(2) }}</dd>
                            </div>
                        </dl>
                    </div>
                    <div v-if="credito.venta">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Venta Relacionada</h3>
                        <dl class="space-y-2">
                            <div>
                                <dt class="text-sm text-gray-600">Número de Venta:</dt>
                                <dd class="font-medium">{{ credito.venta.nro_venta }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-600">Fecha:</dt>
                                <dd class="font-medium">{{ new Date(credito.venta.fecha).toLocaleDateString('es-ES') }}</dd>
                            </div>
                            <div>
                                <Link
                                    :href="`/my-order/${credito.venta.id}`"
                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                >
                                    Ver detalle de la venta →
                                </Link>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Cuotas -->
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Cuotas</h2>
                <div v-if="credito.pagos && credito.pagos.length > 0" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cuota</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha Pago</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Método</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(pago, index) in credito.pagos" :key="pago.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    Cuota {{ index + 1 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">Bs. {{ Number(pago.monto).toFixed(2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span v-if="pago.fecha_pago">
                                        {{ new Date(pago.fecha_pago).toLocaleDateString('es-ES') }}
                                    </span>
                                    <span v-else class="text-gray-400">Pendiente</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span v-if="pago.fecha_pago">{{ pago.metodo }}</span>
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 py-1 rounded-full text-xs font-medium"
                                        :class="pago.fecha_pago ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                                    >
                                        {{ pago.fecha_pago ? 'Pagado' : 'Pendiente' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button
                                        v-if="!pago.fecha_pago"
                                        @click="showPaymentModal = true; selectedPago = pago"
                                        class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                    >
                                        Pagar
                                    </button>
                                    <span v-else class="text-gray-400 text-sm">-</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="text-center py-8 text-gray-500">
                    No hay cuotas registradas
                </div>
            </div>

            <!-- Modal de Pago -->
            <div v-if="showPaymentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                    <h3 class="text-xl font-bold mb-4">Registrar Pago</h3>
                    <form @submit.prevent="submitPayment">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Monto *</label>
                            <input
                                v-model.number="paymentForm.monto"
                                type="number"
                                step="0.01"
                                min="0"
                                :max="selectedPago?.monto"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                                required
                            />
                            <p class="text-xs text-gray-500 mt-1">
                                Monto de la cuota: Bs. {{ Number(selectedPago?.monto || 0).toFixed(2) }}
                            </p>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Método de Pago *</label>
                            <select
                                v-model="paymentForm.metodo"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                                required
                            >
                                <option value="">Seleccione</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="tarjeta">Tarjeta</option>
                                <option value="qr">QR / Transferencia</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Número de Transacción</label>
                            <input
                                v-model="paymentForm.nro_transaccion"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                            />
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Observación</label>
                            <textarea
                                v-model="paymentForm.observacion"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                            ></textarea>
                        </div>

                        <div class="flex gap-3">
                            <button
                                type="button"
                                @click="showPaymentModal = false"
                                class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="paymentForm.processing"
                                class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg disabled:opacity-50"
                            >
                                Registrar Pago
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';

const props = defineProps({
    credito: Object
});

const showPaymentModal = ref(false);
const selectedPago = ref(null);

const paymentForm = useForm({
    monto: 0,
    metodo: '',
    nro_transaccion: '',
    observacion: ''
});

const submitPayment = () => {
    paymentForm.post(`/credit/pay-cuota/${props.credito.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showPaymentModal.value = false;
            paymentForm.reset();
        }
    });
};

// Cuando se abre el modal, establecer el monto de la cuota
const openPaymentModal = (pago) => {
    selectedPago.value = pago;
    paymentForm.monto = pago.monto;
    showPaymentModal.value = true;
};
</script>
