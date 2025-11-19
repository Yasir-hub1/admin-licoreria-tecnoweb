<template>
    <AdminLayout>
        <div class="container mx-auto px-4 py-8">
            <div class="mb-6">
                <Link href="/admin/compras" class="text-blue-600 hover:text-blue-800 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Volver a Compras
                </Link>
            </div>

            <h1 class="text-3xl font-bold mb-6">Editar Compra #{{ compra.nro_compra }}</h1>

            <div v-if="compraCancelada" class="mb-4 p-4 bg-red-100 border border-red-400 rounded-lg">
                <p class="text-red-800 font-medium">⚠️ Esta compra está cancelada y no puede ser editada.</p>
            </div>
            <div v-else-if="compraValidada" class="mb-4 p-4 bg-yellow-100 border border-yellow-400 rounded-lg">
                <p class="text-yellow-800 font-medium">⚠️ Esta compra ya fue validada y no puede ser editada.</p>
            </div>

            <form v-if="!compraValidada && !compraCancelada" @submit.prevent="submit" class="bg-white shadow rounded-lg p-6">
                <!-- Información básica -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Número de Compra *</label>
                        <input
                            v-model="form.nro_compra"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                            required
                        />
                        <span v-if="form.errors.nro_compra" class="text-red-500 text-sm">{{ form.errors.nro_compra }}</span>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Proveedor *</label>
                        <select
                            v-model="form.proveedor_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                            required
                        >
                            <option value="">Seleccione un proveedor</option>
                            <option v-for="proveedor in proveedores" :key="proveedor.id" :value="proveedor.id">
                                {{ proveedor.nombre }}
                            </option>
                        </select>
                        <span v-if="form.errors.proveedor_id" class="text-red-500 text-sm">{{ form.errors.proveedor_id }}</span>
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

                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-bold mb-2">Descripción</label>
                        <textarea
                            v-model="form.descripcion"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                        ></textarea>
                    </div>
                </div>

                <!-- Productos -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold">Productos de la Compra</h2>
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
                                            {{ producto.nombre }} - {{ producto.codigo }}
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
                        <span v-else>Actualizar Compra</span>
                    </button>
                    <Link
                        href="/admin/compras"
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
import { ref, computed, onMounted } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    compra: Object,
    proveedores: Array,
    productos: Array
});

const productos = ref(props.productos || []);
const compraValidada = computed(() => {
    return props.compra.estado === 'validado' || props.compra.detalles?.some(d => d.inventarios?.length > 0);
});
const compraCancelada = computed(() => {
    return props.compra.estado === 'cancelado';
});

const form = useForm({
    nro_compra: props.compra.nro_compra,
    proveedor_id: props.compra.proveedor_id,
    fecha: props.compra.fecha ? new Date(props.compra.fecha).toISOString().split('T')[0] : new Date().toISOString().split('T')[0],
    descripcion: props.compra.descripcion || '',
    detalles: props.compra.detalles?.map(d => ({
        producto_id: d.producto_id,
        cantidad: d.cantidad,
        precio_unitario: d.precio_unitario
    })) || []
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
    const producto = productos.value.find(p => p.id === form.detalles[index].producto_id);
    if (producto) {
        form.detalles[index].precio_unitario = producto.precio;
    }
};

const calcularSubtotal = (index) => {
    // El subtotal se calcula automáticamente en el template
};

const total = computed(() => {
    return form.detalles.reduce((sum, detalle) => {
        return sum + (detalle.cantidad * detalle.precio_unitario);
    }, 0);
});

const submit = () => {
    form.put(`/admin/compras/${props.compra.id}`);
};

// Los productos ya vienen en props
</script>

