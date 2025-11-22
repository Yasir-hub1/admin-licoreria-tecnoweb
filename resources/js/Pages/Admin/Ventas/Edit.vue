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

            <h1 class="text-3xl font-bold mb-6">Editar Venta #{{ venta.nro_venta }}</h1>

            <form @submit.prevent="submit" class="bg-white shadow rounded-lg p-6">
                <!-- Información básica -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Número de Venta *</label>
                        <input
                            v-model="form.nro_venta"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                            required
                        />
                        <span v-if="form.errors.nro_venta" class="text-red-500 text-sm">{{ form.errors.nro_venta }}</span>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Cliente *</label>
                        <select
                            v-model="form.cliente_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                            required
                        >
                            <option value="">Seleccione un cliente</option>
                            <option v-for="cliente in clientes" :key="cliente.id" :value="cliente.id">
                                {{ cliente.nombre }} - CI: {{ cliente.ci }}
                            </option>
                        </select>
                        <span v-if="form.errors.cliente_id" class="text-red-500 text-sm">{{ form.errors.cliente_id }}</span>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Usuario *</label>
                        <div class="relative">
                            <input
                                :value="usuarioNombre"
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
                        <p class="mt-1 text-xs text-gray-500">Usuario que realizó la venta</p>
                        <span v-if="form.errors.usuario_id" class="text-red-500 text-sm">{{ form.errors.usuario_id }}</span>
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

                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Estado *</label>
                        <select
                            v-model="form.estado"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                            required
                        >
                            <option value="pendiente">Pendiente</option>
                            <option value="completado">Completado</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
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

                    <div class="space-y-4">
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
                        <span v-if="form.processing">Actualizando...</span>
                        <span v-else>Actualizar Venta</span>
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
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const page = usePage();
const props = defineProps({
    venta: Object,
    clientes: Array,
    productos: Array,
    stocks: Object
});

const form = useForm({
    nro_venta: props.venta.nro_venta,
    cliente_id: props.venta.cliente_id,
    usuario_id: props.venta.usuario_id || page.props.auth?.user?.id || '',
    fecha: props.venta.fecha ? new Date(props.venta.fecha).toISOString().split('T')[0] : new Date().toISOString().split('T')[0],
    tipo: props.venta.tipo || 'contado',
    numero_cuotas: (props.venta.tipo === 'credito' && props.venta.numero_cuotas) ? props.venta.numero_cuotas : null,
    estado: props.venta.estado || 'pendiente',
    detalles: props.venta.detalles?.map(d => ({
        producto_id: d.producto_id,
        cantidad: d.cantidad,
        precio_unitario: d.precio_unitario
    })) || []
});

// Obtener nombre del usuario
const usuarioNombre = computed(() => {
    return props.venta.usuario?.nombre || page.props.auth?.user?.nombre || 'Usuario';
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
    } else if (form.tipo === 'credito' && !form.numero_cuotas) {
        // Si cambia a crédito y no tiene número de cuotas, establecer un valor por defecto
        form.numero_cuotas = 2;
    }
};

const total = computed(() => {
    return form.detalles.reduce((sum, detalle) => {
        return sum + (detalle.cantidad * detalle.precio_unitario);
    }, 0);
});

const submit = () => {
    // Asegurar que numero_cuotas sea null si el tipo es contado
    if (form.tipo === 'contado') {
        form.numero_cuotas = null;
    }
    
    form.put(`/admin/ventas/${props.venta.id}`);
};
</script>

