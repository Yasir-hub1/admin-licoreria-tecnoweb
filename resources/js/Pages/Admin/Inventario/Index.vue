<template>
    <AdminLayout title="Inventario de Licores" subtitle="Gestiona el stock de productos">
        <div class="space-y-6">

            <div class="mb-6 flex gap-4">
                <Link v-if="puedeVerMovimientos" href="/admin/inventario/movimientos">
                    <Button variant="primary" size="md">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Ver Movimientos
                    </Button>
                </Link>
                <Button
                    v-if="puedeRegistrarMovimiento"
                    @click="showAjusteModal = true"
                    variant="success"
                    size="md"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Registrar Movimiento
                </Button>
            </div>

            <div v-motion-slide-bottom class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50"><tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoría</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock Actual</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr></thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="producto in productos" :key="producto.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ producto.nombre }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ producto.categoria?.nombre || '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold"
                                :class="{
                                    'text-red-600': producto.stock_actual <= 0,
                                    'text-yellow-600': producto.stock_actual > 0 && producto.stock_actual <= 10,
                                    'text-green-600': producto.stock_actual > 10
                                }">
                                {{ producto.stock_actual || 0 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <Link v-if="puedeVerKardex" :href="`/admin/inventario/kardex/${producto.id}`" class="text-blue-600 hover:text-blue-800">Ver Kardex</Link>
                                <span v-else class="text-gray-400">-</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Modal de Movimiento de Inventario -->
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showAjusteModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="cerrarModal">
                    <div
                        v-motion-pop
                        class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full max-h-[90vh] overflow-y-auto border border-gray-100"
                    >
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                                Registrar Movimiento
                            </h3>
                            <button @click="cerrarModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <form @submit.prevent="submitAjuste" class="space-y-5">
                            <SelectInput
                                v-model="ajusteForm.producto_id"
                                label="Producto"
                                name="producto_id"
                                placeholder="Seleccione producto"
                                required
                                :error="ajusteForm.errors.producto_id"
                                :options="productos.map(p => ({
                                    id: p.id,
                                    nombre: `${p.nombre} (Stock: ${Number(p.stock_actual || 0).toFixed(0)})`
                                }))"
                                option-value="id"
                                option-label="nombre"
                                @change="onProductoChange"
                            />

                            <SelectInput
                                v-model="ajusteForm.tipo_movimiento"
                                label="Tipo de Movimiento"
                                name="tipo_movimiento"
                                placeholder="Seleccione tipo de movimiento"
                                required
                                :error="ajusteForm.errors.tipo_movimiento"
                                :options="tipoMovimientoOptions"
                                option-value="value"
                                option-label="label"
                                @change="onTipoMovimientoChange"
                            />

                            <NumberInput
                                v-model="ajusteForm.cantidad"
                                label="Cantidad"
                                name="cantidad"
                                placeholder="0"
                                required
                                :error="ajusteForm.errors.cantidad || (ajusteForm.tipo_movimiento === 'SALIDA' && ajusteForm.cantidad > stockActual ? 'La cantidad no puede ser mayor al stock disponible' : '')"
                                :min="1"
                                :max="ajusteForm.tipo_movimiento === 'SALIDA' ? stockActual : undefined"
                                :step="1"
                                hint="Cantidad de unidades"
                            />

                            <div v-if="ajusteForm.producto_id" class="p-4 rounded-lg border-2" :class="{
                                'bg-green-50 border-green-200': ajusteForm.tipo_movimiento === 'INGRESO',
                                'bg-orange-50 border-orange-200': ajusteForm.tipo_movimiento === 'SALIDA',
                                'bg-gray-50 border-gray-200': !ajusteForm.tipo_movimiento
                            }">
                                <p class="text-sm font-medium mb-1">
                                    <span v-if="ajusteForm.tipo_movimiento === 'INGRESO'" class="text-green-700">
                                        ➕ Stock actual: <strong>{{ stockActual }}</strong> → Stock final: <strong>{{ stockFinal }}</strong>
                                    </span>
                                    <span v-else-if="ajusteForm.tipo_movimiento === 'SALIDA'" class="text-orange-700">
                                        ➖ Stock actual: <strong>{{ stockActual }}</strong> → Stock final: <strong>{{ stockFinal }}</strong>
                                    </span>
                                    <span v-else class="text-gray-600">
                                        Stock actual: <strong>{{ stockActual }}</strong>
                                    </span>
                                </p>
                            </div>

                            <TextareaInput
                                v-model="ajusteForm.glosa"
                                label="Motivo / Glosa"
                                name="glosa"
                                :placeholder="ajusteForm.tipo_movimiento === 'INGRESO' ? 'Ej: Compra de proveedor, Devolución de cliente, Ajuste por inventario físico, etc.' : 'Ej: Venta, Producto dañado, Pérdida, Ajuste por inventario físico, etc.'"
                                required
                                :error="ajusteForm.errors.glosa"
                                :rows="3"
                                :max-length="200"
                                hint="Máximo 200 caracteres"
                            />

                            <div class="flex gap-3 pt-4">
                                <Button
                                    type="button"
                                    @click="cerrarModal"
                                    variant="outline"
                                    full-width
                                >
                                    Cancelar
                                </Button>
                                <Button
                                    type="submit"
                                    :loading="ajusteForm.processing"
                                    :disabled="ajusteForm.processing || (ajusteForm.tipo_movimiento === 'SALIDA' && ajusteForm.cantidad > stockActual)"
                                    :variant="ajusteForm.tipo_movimiento === 'INGRESO' ? 'success' : ajusteForm.tipo_movimiento === 'SALIDA' ? 'warning' : 'primary'"
                                    full-width
                                >
                                    <span v-if="ajusteForm.tipo_movimiento === 'INGRESO'">➕ Registrar Ingreso</span>
                                    <span v-else-if="ajusteForm.tipo_movimiento === 'SALIDA'">➖ Registrar Salida</span>
                                    <span v-else>Registrar Movimiento</span>
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SelectInput from '@/Components/Form/SelectInput.vue';
import NumberInput from '@/Components/Form/NumberInput.vue';
import TextareaInput from '@/Components/Form/TextareaInput.vue';
import Button from '@/Components/Button.vue';
import { usePermissions } from '@/composables/usePermissions';

const { tienePermiso } = usePermissions();

// Hacer reactivos los permisos usando computed
const puedeVerMovimientos = computed(() => tienePermiso('inventario.ver'));
const puedeRegistrarMovimiento = computed(() => tienePermiso('inventario.crear'));
const puedeVerKardex = computed(() => tienePermiso('inventario.ver'));

const tipoMovimientoOptions = [
    { value: 'INGRESO', label: '➕ Ingreso (Aumentar stock)' },
    { value: 'SALIDA', label: '➖ Salida (Reducir stock)' }
];

const props = defineProps({
    productos: Array
});

const showAjusteModal = ref(false);
const ajusteForm = useForm({
    producto_id: '',
    tipo_movimiento: '',
    cantidad: '',
    glosa: ''
});

// Calcular stock actual del producto seleccionado
const stockActual = computed(() => {
    if (!ajusteForm.producto_id) return 0;
    const producto = props.productos.find(p => p.id == ajusteForm.producto_id);
    // Asegurar que sea un número entero
    return Math.floor(Number(producto?.stock_actual || 0));
});

// Calcular stock final según el tipo de movimiento
const stockFinal = computed(() => {
    if (!ajusteForm.tipo_movimiento || !ajusteForm.cantidad) {
        return stockActual.value;
    }
    const cantidad = Number(ajusteForm.cantidad) || 0;
    if (ajusteForm.tipo_movimiento === 'INGRESO') {
        return stockActual.value + cantidad;
    } else if (ajusteForm.tipo_movimiento === 'SALIDA') {
        return Math.max(0, stockActual.value - cantidad);
    }
    return stockActual.value;
});

const onProductoChange = () => {
    // Resetear cantidad cuando cambia el producto
    ajusteForm.cantidad = '';
};

const onTipoMovimientoChange = () => {
    // Resetear cantidad cuando cambia el tipo de movimiento
    ajusteForm.cantidad = '';
};

const cerrarModal = () => {
    showAjusteModal.value = false;
    ajusteForm.reset();
};

const submitAjuste = () => {
    // Convertir cantidad a número antes de enviar
    const cantidadNum = ajusteForm.cantidad === '' || ajusteForm.cantidad === null ? 0 : Number(ajusteForm.cantidad);

    // Validar que los campos estén completos antes de enviar
    if (!ajusteForm.producto_id) {
        return;
    }

    if (!ajusteForm.tipo_movimiento) {
        return;
    }

    if (isNaN(cantidadNum) || cantidadNum <= 0) {
        return;
    }

    // Validar que las salidas no excedan el stock disponible
    if (ajusteForm.tipo_movimiento === 'SALIDA' && cantidadNum > stockActual.value) {
        return;
    }

    if (!ajusteForm.glosa || ajusteForm.glosa.trim() === '') {
        return;
    }

    // Asegurar que cantidad sea un número
    ajusteForm.cantidad = cantidadNum;

    ajusteForm.post('/admin/inventario/ajuste', {
        preserveScroll: true,
        onSuccess: () => {
            cerrarModal();
            // Recargar la página para actualizar los stocks
            setTimeout(() => {
                window.location.reload();
            }, 500);
        },
        onError: (errors) => {
            console.error('Error al registrar movimiento:', errors);
            // Los errores se mostrarán automáticamente en el formulario
        }
    });
};
</script>
