<template>
    <AdminLayout>
        <div class="container mx-auto px-4 py-8">
            <div class="mb-6">
                <Link href="/admin/ventas" class="text-blue-600 hover:text-blue-800 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Volver a Ventas
                </Link>
            </div>

            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h1 class="text-3xl font-bold mb-6">Detalle de Venta #{{ venta.nro_venta }}</h1>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Información de la Venta</h3>
                        <dl class="space-y-2">
                            <div>
                                <dt class="text-sm text-gray-600">Fecha:</dt>
                                <dd class="font-medium">{{ new Date(venta.fecha).toLocaleDateString('es-ES') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-600">Cliente:</dt>
                                <dd class="font-medium">{{ venta.cliente?.nombre || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-600">Usuario:</dt>
                                <dd class="font-medium">{{ venta.usuario?.nombre || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-600">Tipo:</dt>
                                <dd class="font-medium">{{ venta.tipo === 'contado' ? 'Contado' : 'Crédito' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-600">Estado:</dt>
                                <dd>
                                    <span
                                        class="px-3 py-1 rounded-full text-xs font-medium"
                                        :class="{
                                            'bg-green-100 text-green-800': venta.estado_real === 'completado',
                                            'bg-yellow-100 text-yellow-800': venta.estado_real === 'pendiente',
                                            'bg-orange-100 text-orange-800': venta.estado_real === 'en_credito',
                                            'bg-red-100 text-red-800': venta.estado_real === 'cancelado'
                                        }"
                                    >
                                        {{ venta.estado_real === 'en_credito' ? 'En Crédito' : venta.estado_real === 'completado' ? 'Completado' : venta.estado_real === 'pendiente' ? 'Pendiente' : 'Cancelado' }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Resumen Financiero</h3>
                        <dl class="space-y-2">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Monto Total:</dt>
                                <dd class="font-bold text-lg text-green-600">Bs. {{ totalCalculado.toFixed(2) }}</dd>
                            </div>
                            <div v-if="venta.tipo === 'credito'" class="space-y-2 pt-2 border-t">
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-600">Pagado:</dt>
                                    <dd class="font-medium text-green-600">Bs. {{ pagadoReal.toFixed(2) }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-600">Saldo Pendiente:</dt>
                                    <dd class="font-bold text-lg text-red-600">Bs. {{ saldoReal.toFixed(2) }}</dd>
                                </div>
                                <div class="mt-2">
                                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                                        <span>Progreso de pago</span>
                                        <span>{{ porcentajePagado.toFixed(1) }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div
                                            class="bg-green-600 h-2 rounded-full transition-all"
                                            :style="{ width: porcentajePagado + '%' }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </dl>
                    </div>
                </div>

                <div v-if="venta.credito" class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex justify-between items-center">
                        <div>
                            <h4 class="font-semibold text-blue-900 mb-1">💰 Venta a Crédito</h4>
                            <p class="text-sm text-blue-700">
                                <span v-if="saldoReal > 0">
                                    ⚠️ Esta venta aún tiene un saldo pendiente de <strong>Bs. {{ saldoReal.toFixed(2) }}</strong>
                                </span>
                                <span v-else class="text-green-700">
                                    ✅ Crédito completamente pagado
                                </span>
                            </p>
                            <p v-if="venta.credito.numero_cuotas" class="text-xs text-blue-600 mt-1">
                                {{ venta.credito.numero_cuotas }} cuotas -
                                <span v-if="pagosOrdenados.length > 0">
                                    {{ pagosOrdenados.filter(p => p.fecha_pago).length }} pagadas /
                                    {{ pagosOrdenados.filter(p => !p.fecha_pago).length }} pendientes
                                </span>
                            </p>
                        </div>
                        <Link
                            :href="`/admin/creditos/${venta.credito.id}`"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium text-sm"
                        >
                            Ver Detalle del Crédito →
                        </Link>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Productos Vendidos</h2>
                <div v-if="venta.detalles && venta.detalles.length > 0" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Precio Unit.</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="detalle in venta.detalles" :key="detalle.id">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="font-medium text-gray-900">{{ detalle.producto?.nombre }}</div>
                                        <div class="text-sm text-gray-500">{{ detalle.producto?.codigo }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ detalle.cantidad }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Bs. {{ Number(detalle.precio_unitario).toFixed(2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Bs. {{ Number(detalle.subtotal).toFixed(2) }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-right font-bold">Total:</td>
                                <td class="px-6 py-4 font-bold text-lg text-green-600">Bs. {{ totalCalculado.toFixed(2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div v-else class="text-center py-8 text-gray-500">
                    No hay productos en esta venta
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    venta: Object
});

// Calcular total desde los detalles como respaldo
const totalCalculado = computed(() => {
    if (!props.venta.detalles || props.venta.detalles.length === 0) {
        return Number(props.venta.monto_total || 0);
    }
    return props.venta.detalles.reduce((sum, detalle) => {
        return sum + Number(detalle.subtotal || 0);
    }, 0);
});

// Ordenar pagos del crédito si existen - usar numero_cuota directamente
const pagosOrdenados = computed(() => {
    if (!props.venta.credito?.pagos) return [];

    return [...props.venta.credito.pagos].sort((a, b) => {
        // Usar numero_cuota directamente (campo confiable)
        const numA = a.numero_cuota !== null && a.numero_cuota !== undefined
            ? parseInt(a.numero_cuota, 10)
            : ((a.id || 0) + 10000);
        const numB = b.numero_cuota !== null && b.numero_cuota !== undefined
            ? parseInt(b.numero_cuota, 10)
            : ((b.id || 0) + 10000);

        return numA - numB;
    });
});

// Calcular pagado desde los pagos reales (más preciso)
const pagadoReal = computed(() => {
    if (props.venta.tipo !== 'credito' || !props.venta.credito?.pagos) {
        return props.venta.tipo === 'contado' ? totalCalculado.value : Number(props.venta.pagado || 0);
    }
    // Sumar solo los pagos que tienen fecha_pago (pagos realizados)
    return pagosOrdenados.value
        .filter(p => p.fecha_pago)
        .reduce((sum, pago) => sum + Number(pago.monto || 0), 0);
});

// Calcular saldo desde el crédito (fuente de verdad)
const saldoReal = computed(() => {
    if (props.venta.tipo !== 'credito' || !props.venta.credito) {
        return 0;
    }
    // Usar el saldo del crédito directamente
    return Number(props.venta.credito.saldo || props.venta.saldo || 0);
});

// Calcular porcentaje pagado
const porcentajePagado = computed(() => {
    if (totalCalculado.value <= 0) return 0;
    return (pagadoReal.value / totalCalculado.value) * 100;
});
</script>
