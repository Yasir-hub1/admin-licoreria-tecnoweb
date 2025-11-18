<template>
    <MainLayout>
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold mb-6">Inventario de Licores</h1>
            <div class="mb-6 flex gap-4">
                <Link href="/admin/inventario/movimientos" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium">
                    📊 Ver Movimientos
                </Link>
                <button
                    @click="showAjusteModal = true"
                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-medium"
                >
                    ➕ Registrar Movimiento
                </button>
            </div>
            <div class="bg-white shadow rounded-lg overflow-hidden">
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
                                <Link :href="`/admin/inventario/kardex/${producto.id}`" class="text-blue-600 hover:text-blue-800">Ver Kardex</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Modal de Movimiento de Inventario -->
            <div v-if="showAjusteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto">
                    <h3 class="text-xl font-bold mb-4">Registrar Movimiento de Inventario</h3>
                    <form @submit.prevent="submitAjuste">
                        <div v-if="ajusteForm.errors.producto_id || ajusteForm.errors.tipo_movimiento || ajusteForm.errors.cantidad || ajusteForm.errors.glosa" class="mb-4 p-3 bg-red-100 border border-red-400 rounded-lg">
                            <p class="text-red-700 text-sm font-medium mb-2">Errores:</p>
                            <ul class="list-disc list-inside text-sm text-red-600">
                                <li v-if="ajusteForm.errors.producto_id">{{ ajusteForm.errors.producto_id }}</li>
                                <li v-if="ajusteForm.errors.tipo_movimiento">{{ ajusteForm.errors.tipo_movimiento }}</li>
                                <li v-if="ajusteForm.errors.cantidad">{{ ajusteForm.errors.cantidad }}</li>
                                <li v-if="ajusteForm.errors.glosa">{{ ajusteForm.errors.glosa }}</li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Producto *</label>
                            <select
                                v-model="ajusteForm.producto_id"
                                @change="onProductoChange"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                                :class="{ 'border-red-500': ajusteForm.errors.producto_id }"
                                required
                            >
                                <option value="">Seleccione producto</option>
                                <option v-for="producto in productos" :key="producto.id" :value="producto.id">
                                    {{ producto.nombre }} (Stock: {{ Number(producto.stock_actual || 0).toFixed(0) }})
                                </option>
                            </select>
                            <span v-if="ajusteForm.errors.producto_id" class="text-red-500 text-xs mt-1">{{ ajusteForm.errors.producto_id }}</span>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Movimiento *</label>
                            <select
                                v-model="ajusteForm.tipo_movimiento"
                                @change="onTipoMovimientoChange"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                                :class="{
                                    'border-red-500': ajusteForm.errors.tipo_movimiento,
                                    'border-green-500': ajusteForm.tipo_movimiento === 'INGRESO',
                                    'border-orange-500': ajusteForm.tipo_movimiento === 'SALIDA'
                                }"
                                required
                            >
                                <option value="">Seleccione tipo de movimiento</option>
                                <option value="INGRESO">➕ Ingreso (Aumentar stock)</option>
                                <option value="SALIDA">➖ Salida (Reducir stock)</option>
                            </select>
                            <span v-if="ajusteForm.errors.tipo_movimiento" class="text-red-500 text-xs mt-1">{{ ajusteForm.errors.tipo_movimiento }}</span>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cantidad *</label>
                            <input
                                v-model.number="ajusteForm.cantidad"
                                type="number"
                                :min="1"
                                :max="ajusteForm.tipo_movimiento === 'SALIDA' ? stockActual : undefined"
                                step="1"
                                placeholder="0"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                                :class="{ 'border-red-500': ajusteForm.errors.cantidad || (ajusteForm.tipo_movimiento === 'SALIDA' && ajusteForm.cantidad > stockActual) }"
                                required
                            />
                            <span v-if="ajusteForm.errors.cantidad" class="text-red-500 text-xs mt-1">{{ ajusteForm.errors.cantidad }}</span>
                            <div v-if="ajusteForm.producto_id" class="mt-2 p-2 rounded-lg" :class="{
                                'bg-blue-50 border border-blue-200': ajusteForm.tipo_movimiento === 'INGRESO',
                                'bg-orange-50 border border-orange-200': ajusteForm.tipo_movimiento === 'SALIDA',
                                'bg-gray-50 border border-gray-200': !ajusteForm.tipo_movimiento
                            }">
                                <p class="text-xs font-medium mb-1">
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
                                <p v-if="ajusteForm.tipo_movimiento === 'SALIDA' && ajusteForm.cantidad > stockActual" class="text-xs text-red-600 font-medium mt-1">
                                    ⚠️ La cantidad a salir no puede ser mayor al stock disponible
                                </p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Motivo / Glosa *</label>
                            <textarea
                                v-model="ajusteForm.glosa"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                                :class="{ 'border-red-500': ajusteForm.errors.glosa }"
                                :placeholder="ajusteForm.tipo_movimiento === 'INGRESO' ? 'Ej: Compra de proveedor, Devolución de cliente, Ajuste por inventario físico, etc.' : 'Ej: Venta, Producto dañado, Pérdida, Ajuste por inventario físico, etc.'"
                                required
                            ></textarea>
                            <span v-if="ajusteForm.errors.glosa" class="text-red-500 text-xs mt-1">{{ ajusteForm.errors.glosa }}</span>
                            <p class="text-xs text-gray-500 mt-1">Máximo 200 caracteres</p>
                        </div>

                        <div class="flex gap-3">
                            <button
                                type="button"
                                @click="cerrarModal"
                                class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="ajusteForm.processing || (ajusteForm.tipo_movimiento === 'SALIDA' && ajusteForm.cantidad > stockActual)"
                                class="flex-1 text-white px-4 py-2 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed"
                                :class="{
                                    'bg-green-500 hover:bg-green-600': ajusteForm.tipo_movimiento === 'INGRESO',
                                    'bg-orange-500 hover:bg-orange-600': ajusteForm.tipo_movimiento === 'SALIDA',
                                    'bg-blue-500 hover:bg-blue-600': !ajusteForm.tipo_movimiento
                                }"
                            >
                                <span v-if="ajusteForm.processing">Procesando...</span>
                                <span v-else-if="ajusteForm.tipo_movimiento === 'INGRESO'">➕ Registrar Ingreso</span>
                                <span v-else-if="ajusteForm.tipo_movimiento === 'SALIDA'">➖ Registrar Salida</span>
                                <span v-else>Registrar Movimiento</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

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
