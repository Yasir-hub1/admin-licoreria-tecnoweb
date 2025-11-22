<template>
    <AdminLayout>
        <div class="container mx-auto px-4 py-8">
            <div class="mb-6 flex justify-between items-center">
                <Link href="/admin/compras" class="text-blue-600 hover:text-blue-800 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Volver a Compras
                </Link>
                <Link
                    v-if="!esProveedor && puedeEditar && compra.estado !== 'validado'"
                    :href="`/admin/compras/${compra.id}/edit`"
                    class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg font-medium"
                >
                    ✏️ Editar Compra
                </Link>
            </div>

            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h1 class="text-3xl font-bold mb-6">Detalle de Compra #{{ compra.nro_compra }}</h1>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Información de la Compra</h3>
                        <dl class="space-y-2">
                            <div>
                                <dt class="text-sm text-gray-600">Fecha:</dt>
                                <dd class="font-medium">{{ new Date(compra.fecha).toLocaleDateString('es-ES') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-600">Proveedor:</dt>
                                <dd class="font-medium">{{ compra.proveedor?.nombre || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-600">Estado:</dt>
                                <dd>
                                    <span
                                        class="px-3 py-1 rounded-full text-xs font-medium"
                                        :class="{
                                            'bg-green-100 text-green-800': compra.estado === 'validado',
                                            'bg-red-100 text-red-800': compra.estado === 'cancelado',
                                            'bg-yellow-100 text-yellow-800': compra.estado === 'pendiente' || !compra.estado
                                        }"
                                    >
                                        {{ compra.estado === 'validado' ? 'Validado' : compra.estado === 'cancelado' ? 'Cancelado' : 'Pendiente' }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Resumen Financiero</h3>
                        <dl class="space-y-2">
                            <div class="flex justify-between items-center border-t pt-3">
                                <dt class="text-lg font-bold text-gray-800">Total de la Compra:</dt>
                                <dd class="text-2xl font-bold text-green-600">Bs. {{ totalCompra.toFixed(2) }}</dd>
                            </div>
                            <div v-if="compra.detalles && compra.detalles.length > 0" class="text-xs text-gray-500 mt-2">
                                {{ compra.detalles.length }} {{ compra.detalles.length === 1 ? 'producto' : 'productos' }}
                            </div>
                        </dl>
                    </div>
                </div>

                <div v-if="!esProveedor" class="mb-6 flex gap-3">
                    <form v-if="puedeValidar && compra.estado !== 'validado' && compra.estado !== 'cancelado'" @submit.prevent="validarCompra" class="inline">
                        <button
                            type="submit"
                            :disabled="validating"
                            class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg font-medium disabled:opacity-50"
                        >
                            ✅ Validar Compra
                        </button>
                    </form>
                    <form v-if="puedeCancelar && compra.estado !== 'validado' && compra.estado !== 'cancelado'" @submit.prevent="cancelarCompra" class="inline">
                        <button
                            type="submit"
                            :disabled="cancelling"
                            class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg font-medium disabled:opacity-50"
                        >
                            ❌ Cancelar Compra
                        </button>
                    </form>
                    <div v-if="compra.estado === 'cancelado'" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-800">
                            <strong>⚠️ Compra Cancelada:</strong> Esta compra ha sido cancelada y no puede ser validada ni editada.
                        </p>
                    </div>
                    <div v-if="compra.estado === 'validado'" class="p-3 bg-green-50 border border-green-200 rounded-lg">
                        <p class="text-sm text-green-800">
                            <strong>✅ Compra Validada:</strong> Esta compra ha sido validada y el inventario ha sido actualizado.
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Productos Comprados</h2>
                <div v-if="compra.detalles && compra.detalles.length > 0" class="overflow-x-auto">
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
                            <tr v-for="detalle in compra.detalles" :key="detalle.id">
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
                                <td class="px-6 py-4 font-bold text-lg text-green-600">Bs. {{ totalCompra.toFixed(2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div v-else class="text-center py-8 text-gray-500">
                    No hay productos en esta compra
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { usePermissions } from '@/composables/usePermissions';

const props = defineProps({
    compra: Object,
    esProveedor: Boolean
});

const { tienePermiso } = usePermissions();

// Hacer reactivos los permisos usando computed
const puedeEditar = computed(() => tienePermiso('compras.editar'));
const puedeValidar = computed(() => tienePermiso('compras.validar'));
const puedeCancelar = computed(() => tienePermiso('compras.cancelar'));

// Calcular el total si no viene del backend o como respaldo
const totalCompra = computed(() => {
    if (props.compra.total !== undefined && props.compra.total !== null) {
        return Number(props.compra.total);
    }
    // Calcular sumando los subtotales de los detalles
    if (props.compra.detalles && props.compra.detalles.length > 0) {
        return props.compra.detalles.reduce((sum, detalle) => {
            const subtotal = detalle.subtotal || (detalle.cantidad * detalle.precio_unitario);
            return sum + Number(subtotal);
        }, 0);
    }
    return 0;
});

const validating = ref(false);
const cancelling = ref(false);

const validarCompra = () => {
    if (confirm('¿Está seguro de validar esta compra? Esto actualizará el inventario.')) {
        validating.value = true;
        router.post(`/admin/compras/${props.compra.id}/validar`, {}, {
            preserveScroll: true,
            onFinish: () => {
                validating.value = false;
            }
        });
    }
};

const cancelarCompra = () => {
    if (confirm('¿Está seguro de cancelar esta compra? Una vez cancelada, no podrá ser validada ni editada.')) {
        cancelling.value = true;
        router.post(`/admin/compras/${props.compra.id}/cancelar`, {}, {
            preserveScroll: true,
            onFinish: () => {
                cancelling.value = false;
            }
        });
    }
};
</script>
