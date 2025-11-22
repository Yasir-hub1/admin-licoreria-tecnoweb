<template>
    <AdminLayout>
        <div class="container mx-auto px-4 py-8">
            <div class="mb-6">
                <Link href="/admin/creditos" class="text-blue-600 hover:text-blue-800 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Volver a Créditos
                </Link>
            </div>

            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <div class="flex justify-between items-start mb-6">
                    <h1 class="text-3xl font-bold">Detalle de Crédito #{{ credito.id }}</h1>
                    <div class="space-x-2">
                        <Link
                            v-if="puedeEditar"
                            :href="`/admin/creditos/${credito.id}/edit`"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium"
                        >
                            Editar
                        </Link>
                    </div>
                </div>

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
                                <dt class="text-sm text-gray-600">Cliente:</dt>
                                <dd class="font-medium">{{ credito.venta.cliente?.nombre || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-600">Fecha:</dt>
                                <dd class="font-medium">{{ new Date(credito.venta.fecha).toLocaleDateString('es-ES') }}</dd>
                            </div>
                            <div>
                                <Link
                                    v-if="puedeVerVenta"
                                    :href="`/admin/ventas/${credito.venta.id}`"
                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                >
                                    Ver detalle de la venta →
                                </Link>
                                <span v-else class="text-gray-400 text-sm">-</span>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Cuotas y Pagos -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">Cuotas y Pagos</h2>
                    <button
                        v-if="puedeRegistrarPago"
                        @click="openPaymentModal"
                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-medium"
                    >
                        ➕ Registrar Pago
                    </button>
                </div>

                <div v-if="credito.pagos && credito.pagos.length > 0" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cuota</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha Pago</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Método</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="pago in sortedPagos" :key="pago.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    {{ getCuotaNumber(pago) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">Bs. {{ Number(pago.monto).toFixed(2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span v-if="pago.fecha_pago">
                                        {{ new Date(pago.fecha_pago).toLocaleDateString('es-ES') }}
                                    </span>
                                    <span v-else class="text-gray-400">
                                        {{ getFechaVencimiento(pago) }}
                                    </span>
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
                        <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-sm text-blue-800">
                                <strong>ℹ️ Información:</strong> Se registrará el pago en la primera cuota pendiente.
                            </p>
                            <p v-if="sortedPagos.find(p => !p.fecha_pago)" class="text-xs text-blue-600 mt-1">
                                Próxima cuota: {{ getCuotaNumber(sortedPagos.find(p => !p.fecha_pago)) }} -
                                Monto: Bs. {{ Number(sortedPagos.find(p => !p.fecha_pago).monto).toFixed(2) }}
                            </p>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Monto (Bs.) *</label>
                            <input
                                v-model.number="paymentForm.monto"
                                type="number"
                                step="0.01"
                                :min="0"
                                :max="credito.saldo"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                                required
                            />
                            <p class="text-xs text-gray-500 mt-1">Saldo pendiente: Bs. {{ Number(credito.saldo).toFixed(2) }}</p>
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
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { usePermissions } from '@/composables/usePermissions';

const props = defineProps({
    credito: Object
});

const { tienePermiso } = usePermissions();

// Hacer reactivos los permisos usando computed
const puedeEditar = computed(() => tienePermiso('creditos.editar'));
const puedeRegistrarPago = computed(() => tienePermiso('creditos.pagos'));
const puedeVerVenta = computed(() => tienePermiso('ventas.ver'));

const showPaymentModal = ref(false);

// Obtener número de cuota - usar numero_cuota directamente (campo directo y confiable)
const getCuotaNumber = (pago) => {
    // Usar numero_cuota si está disponible (campo directo)
    if (pago.numero_cuota !== null && pago.numero_cuota !== undefined) {
        return `Cuota ${pago.numero_cuota}`;
    }
    // Fallback: intentar extraer del nro_transaccion
    if (pago.nro_transaccion) {
        const matchCuota = pago.nro_transaccion.match(/CUOTA-(\d+)/i);
        if (matchCuota) {
            return `Cuota ${matchCuota[1]}`;
        }
    }
    // Fallback: intentar de la observación
    if (pago.observacion) {
        const match = pago.observacion.match(/Cuota\s+(\d+)/i);
        if (match) {
            return `Cuota ${match[1]}`;
        }
    }
    // Último fallback: usar índice
    const index = sortedPagos.value.findIndex(p => p.id === pago.id);
    return index !== -1 ? `Cuota ${index + 1}` : 'Cuota';
};

const getCuotaNumberValue = (pago) => {
    // Usar numero_cuota directamente si está disponible
    if (pago.numero_cuota !== null && pago.numero_cuota !== undefined) {
        return parseInt(pago.numero_cuota, 10);
    }
    // Fallback: intentar extraer del nro_transaccion
    if (pago.nro_transaccion) {
        const matchCuota = pago.nro_transaccion.match(/CUOTA-(\d+)/i);
        if (matchCuota) {
            return parseInt(matchCuota[1], 10);
        }
    }
    // Fallback: intentar de la observación
    if (pago.observacion) {
        const match = pago.observacion.match(/Cuota\s+(\d+)/i);
        if (match) {
            return parseInt(match[1], 10);
        }
    }
    // Último fallback: usar ID + offset alto
    return (pago.id || 0) + 10000;
};

// Ordenar pagos por numero_cuota (ascendente) - ya vienen ordenados del backend, pero por seguridad
const sortedPagos = computed(() => {
    if (!props.credito.pagos) return [];

    return [...props.credito.pagos].sort((a, b) => {
        // Priorizar numero_cuota (campo directo)
        const numA = getCuotaNumberValue(a);
        const numB = getCuotaNumberValue(b);
        return numA - numB;
    });
});

// Calcular monto de cuota para el formulario
const montoCuota = computed(() => {
    if (!props.credito.pagos || props.credito.pagos.length === 0) {
        return 0;
    }
    const primeraCuotaPendiente = sortedPagos.value.find(p => !p.fecha_pago);
    return primeraCuotaPendiente ? Number(primeraCuotaPendiente.monto).toFixed(2) : 0;
});

const paymentForm = useForm({
    monto: '',
    metodo: '',
    nro_transaccion: '',
    observacion: ''
});

// Inicializar monto cuando se abre el modal
const openPaymentModal = () => {
    showPaymentModal.value = true;
    const primeraCuotaPendiente = sortedPagos.value.find(p => !p.fecha_pago);
    if (primeraCuotaPendiente) {
        paymentForm.monto = Number(primeraCuotaPendiente.monto).toFixed(2);
    } else {
        paymentForm.monto = 0;
    }
};

// Extraer fecha de vencimiento de la observación
const getFechaVencimiento = (pago) => {
    if (pago.observacion) {
        const match = pago.observacion.match(/Vence: (\d{4}-\d{2}-\d{2})/);
        if (match) {
            const fecha = new Date(match[1]);
            return fecha.toLocaleDateString('es-ES');
        }
    }
    return 'Pendiente';
};

const submitPayment = () => {
    // Validar que el monto no exceda el saldo
    if (Number(paymentForm.monto) > Number(props.credito.saldo)) {
        alert('El monto del pago no puede ser mayor al saldo pendiente');
        return;
    }

    paymentForm.post(`/admin/creditos/${props.credito.id}/registrar-pago`, {
        preserveScroll: true,
        onSuccess: () => {
            showPaymentModal.value = false;
            paymentForm.reset();
            // Actualizar el monto del formulario con la siguiente cuota
            const siguienteCuota = sortedPagos.value.find(p => !p.fecha_pago);
            if (siguienteCuota) {
                paymentForm.monto = Number(siguienteCuota.monto).toFixed(2);
            }
        }
    });
};
</script>
