<template>
    <MainLayout>
        <div class="container mx-auto px-4 py-8">
            <div class="mb-6">
                <Link href="/admin/ventas" class="text-blue-600 hover:text-blue-800 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Volver a Ventas
                </Link>
            </div>

            <h1 class="text-3xl font-bold mb-6">Nueva Venta de Licores</h1>

            <form @submit.prevent="submit" class="bg-white shadow rounded-lg p-6">
                <!-- Información básica -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Cliente *</label>
                        <div class="relative">
                            <input
                                v-model="clienteBusqueda"
                                @input="buscarClientes"
                                @focus="mostrarSugerencias = true"
                                @blur="ocultarSugerencias"
                                type="text"
                                placeholder="Escriba el nombre o CI del cliente..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                                required
                            />
                            <svg class="absolute right-3 top-3 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>

                            <!-- Dropdown de sugerencias -->
                            <div
                                v-if="mostrarSugerencias && clientesSugeridos.length > 0"
                                class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                            >
                                <div
                                    v-for="cliente in clientesSugeridos"
                                    :key="cliente.id"
                                    @mousedown="seleccionarCliente(cliente)"
                                    class="px-4 py-3 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors"
                                >
                                    <div class="font-medium text-gray-900">{{ cliente.nombre }}</div>
                                    <div class="text-sm text-gray-500">CI: {{ cliente.ci }} | Tel: {{ cliente.telefono || 'N/A' }}</div>
                                </div>
                            </div>

                            <!-- Mensaje cuando no hay resultados -->
                            <div
                                v-if="mostrarSugerencias && clienteBusqueda && clientesSugeridos.length === 0 && !buscandoClientes"
                                class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg px-4 py-3 text-gray-500 text-sm"
                            >
                                No se encontraron clientes
                            </div>
                        </div>
                        <input type="hidden" v-model="form.cliente_id" />
                        <div v-if="clienteSeleccionado" class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded-lg flex items-center justify-between">
                            <span class="text-sm text-blue-800">
                                <strong>✓ Cliente seleccionado:</strong> {{ clienteSeleccionado.nombre }} - CI: {{ clienteSeleccionado.ci }}
                            </span>
                            <button
                                type="button"
                                @click="limpiarCliente"
                                class="text-red-600 hover:text-red-800 text-sm font-medium"
                            >
                                Cambiar
                            </button>
                        </div>
                        <div v-if="!clienteSeleccionado && clienteBusqueda && clienteBusqueda.length >= 2 && !buscandoClientes && clientesSugeridos.length === 0" class="mt-2 p-2 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <span class="text-sm text-yellow-800">
                                ⚠️ No se encontraron clientes. Intenta con otro nombre o CI.
                            </span>
                        </div>
                        <span v-if="form.errors.cliente_id" class="text-red-500 text-sm mt-1 block">{{ form.errors.cliente_id }}</span>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Empleado *</label>
                        <div class="relative">
                            <input
                                :value="empleadoNombre"
                                type="text"
                                disabled
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed"
                            />
                            <div class="absolute right-3 top-2.5">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Asignado automáticamente según tu usuario</p>
                        <span v-if="form.errors.vendedor_id" class="text-red-500 text-sm">{{ form.errors.vendedor_id }}</span>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Fecha *</label>
                        <input
                            v-model="form.fecha"
                            type="date"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                            required
                        />
                        <span v-if="form.errors.fecha" class="text-red-500 text-sm">{{ form.errors.fecha }}</span>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Tipo de Pago *</label>
                        <select
                            v-model="form.tipo"
                            @change="onTipoChange"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                            required
                        >
                            <option value="contado">Contado</option>
                            <option value="credito">Crédito</option>
                        </select>
                        <span v-if="form.errors.tipo" class="text-red-500 text-sm">{{ form.errors.tipo }}</span>
                    </div>

                    <div v-if="form.tipo === 'credito'">
                        <label class="block text-gray-700 font-bold mb-2">Número de Cuotas *</label>
                        <select
                            v-model="form.numero_cuotas"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                            required
                        >
                            <option value="">Seleccione</option>
                            <option value="2">2 cuotas</option>
                            <option value="3">3 cuotas</option>
                            <option value="4">4 cuotas</option>
                            <option value="6">6 cuotas</option>
                        </select>
                        <span v-if="form.errors.numero_cuotas" class="text-red-500 text-sm">{{ form.errors.numero_cuotas }}</span>
                    </div>
                </div>

                <!-- Productos -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold">Productos de la Venta</h2>
                        <button
                            type="button"
                            @click="agregarProducto"
                            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-medium"
                        >
                            ➕ Agregar Producto
                        </button>
                    </div>

                    <div v-if="form.detalles.length === 0" class="text-center py-8 text-gray-500 border-2 border-dashed rounded-lg">
                        No hay productos agregados. Haz clic en "Agregar Producto" para comenzar.
                    </div>

                    <div v-else class="space-y-4">
                        <div
                            v-for="(detalle, index) in form.detalles"
                            :key="index"
                            class="border border-gray-300 rounded-lg p-4"
                        >
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Producto *</label>
                                    <select
                                        v-model="detalle.producto_id"
                                        @change="actualizarPrecio(index)"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                                        required
                                    >
                                        <option value="">Seleccione producto</option>
                                        <option v-for="producto in productos" :key="producto.id" :value="producto.id">
                                            {{ producto.nombre }} - {{ producto.codigo }} (Stock: {{ getStock(producto.id) }})
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad *</label>
                                    <input
                                        v-model.number="detalle.cantidad"
                                        @input="calcularSubtotal(index)"
                                        type="number"
                                        min="1"
                                        step="1"
                                        :max="getStock(detalle.producto_id)"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                                        required
                                    />
                                    <span v-if="detalle.producto_id && detalle.cantidad > getStock(detalle.producto_id)" class="text-red-500 text-xs">
                                        Stock insuficiente
                                    </span>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Precio Unit. (Bs.) *</label>
                                    <input
                                        v-model.number="detalle.precio_unitario"
                                        @input="calcularSubtotal(index)"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                                        required
                                    />
                                </div>
                            </div>

                            <div class="mt-2 flex justify-between items-center">
                                <div>
                                    <span class="text-sm text-gray-600">Subtotal: </span>
                                    <span class="font-semibold text-green-600">Bs. {{ (detalle.cantidad * detalle.precio_unitario).toFixed(2) }}</span>
                                </div>
                                <button
                                    type="button"
                                    @click="eliminarProducto(index)"
                                    class="text-red-600 hover:text-red-800 text-sm font-medium"
                                >
                                    Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total -->
                <div class="border-t pt-4 mb-6">
                    <div class="flex justify-between items-center">
                        <span class="text-xl font-bold">Total:</span>
                        <span class="text-2xl font-bold text-green-600">Bs. {{ total.toFixed(2) }}</span>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex gap-4">
                    <button
                        type="submit"
                        :disabled="form.processing || form.detalles.length === 0"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="form.processing">Guardando...</span>
                        <span v-else>Guardar Venta</span>
                    </button>
                    <Link
                        href="/admin/ventas"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium"
                    >
                        Cancelar
                    </Link>
                </div>
            </form>
        </div>
    </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useForm, Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import MainLayout from '@/Layouts/MainLayout.vue';

const page = usePage();
const props = defineProps({
    clientes: Array,
    vendedor_id: Number,
    productos: Array,
    stocks: Object
});

const form = useForm({
    cliente_id: '',
    vendedor_id: props.vendedor_id || '',
    fecha: new Date().toISOString().split('T')[0],
    tipo: 'contado',
    numero_cuotas: null,
    detalles: []
});

// Autocompletado de cliente
const clienteBusqueda = ref('');
const clientesSugeridos = ref([]);
const clienteSeleccionado = ref(null);
const mostrarSugerencias = ref(false);
const buscandoClientes = ref(false);
const timeoutBusqueda = ref(null);

// Obtener nombre del empleado desde el usuario autenticado
const empleadoNombre = computed(() => {
    return page.props.auth?.user?.nombre || 'Usuario actual';
});

const buscarClientes = () => {
    if (timeoutBusqueda.value) {
        clearTimeout(timeoutBusqueda.value);
    }

    if (!clienteBusqueda.value || clienteBusqueda.value.length < 2) {
        clientesSugeridos.value = [];
        mostrarSugerencias.value = false;
        return;
    }

    timeoutBusqueda.value = setTimeout(async () => {
        buscandoClientes.value = true;
        try {
            const response = await axios.get('/admin/ventas/buscar-clientes', {
                params: { q: clienteBusqueda.value }
            });
            clientesSugeridos.value = response.data;
            mostrarSugerencias.value = true;
        } catch (error) {
            console.error('Error al buscar clientes:', error);
            clientesSugeridos.value = [];
        } finally {
            buscandoClientes.value = false;
        }
    }, 300); // Debounce de 300ms
};

const seleccionarCliente = (cliente) => {
    clienteSeleccionado.value = cliente;
    form.cliente_id = cliente.id;
    clienteBusqueda.value = cliente.nombre;
    mostrarSugerencias.value = false;
    clientesSugeridos.value = [];
};

const ocultarSugerencias = () => {
    // Delay para permitir el click en las sugerencias
    setTimeout(() => {
        mostrarSugerencias.value = false;
    }, 200);
};

const limpiarCliente = () => {
    clienteSeleccionado.value = null;
    form.cliente_id = '';
    clienteBusqueda.value = '';
    clientesSugeridos.value = [];
};

onMounted(() => {
    // Si hay un vendedor_id, asegurarse de que esté en el form
    if (props.vendedor_id) {
        form.vendedor_id = props.vendedor_id;
    }
});

const agregarProducto = () => {
    form.detalles.push({
        producto_id: '',
        cantidad: 1,
        precio_unitario: 0
    });
};

const eliminarProducto = (index) => {
    form.detalles.splice(index, 1);
};

const actualizarPrecio = (index) => {
    const producto = props.productos.find(p => p.id === form.detalles[index].producto_id);
    if (producto) {
        form.detalles[index].precio_unitario = producto.precio;
    }
};

const calcularSubtotal = (index) => {
    // El subtotal se calcula automáticamente en el template
};

const getStock = (productoId) => {
    if (!productoId || !props.stocks) return 0;
    return props.stocks[productoId] || 0;
};

const onTipoChange = () => {
    if (form.tipo === 'contado') {
        form.numero_cuotas = null;
    }
};

const total = computed(() => {
    return form.detalles.reduce((sum, detalle) => {
        return sum + (detalle.cantidad * detalle.precio_unitario);
    }, 0);
});

const submit = () => {
    form.post('/admin/ventas');
};
</script>
