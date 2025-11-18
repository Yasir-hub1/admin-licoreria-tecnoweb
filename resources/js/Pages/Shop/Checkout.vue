<template>
    <ShopLayout>
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold mb-6">Finalizar Compra</h1>

            <div v-if="cart && cart.items && cart.items.length > 0" class="max-w-4xl mx-auto">
                <!-- Resumen del Pedido -->
                <div class="bg-white shadow rounded-lg p-6 mb-6">
                    <h2 class="text-xl font-semibold mb-4">Resumen del Pedido</h2>
                    <div class="space-y-3">
                        <div v-for="item in cart.items" :key="item.id" class="flex justify-between py-2 border-b">
                            <div>
                                <span class="font-medium">{{ item.nombre }}</span>
                                <span class="text-gray-500 text-sm ml-2">x {{ item.cantidad }}</span>
                            </div>
                            <span class="font-semibold">Bs. {{ (item.precio * item.cantidad).toFixed(2) }}</span>
                        </div>
                        <div class="mt-4 pt-4 border-t flex justify-between text-xl font-bold">
                            <span>Total:</span>
                            <span class="text-green-600">Bs. {{ Number(cart.total || 0).toFixed(2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Información del Cliente -->
                <div v-if="cliente" class="bg-white shadow rounded-lg p-6 mb-6">
                    <h2 class="text-xl font-semibold mb-4">Información de Entrega</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Nombre:</p>
                            <p class="font-medium">{{ cliente.nombre }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">CI:</p>
                            <p class="font-medium">{{ cliente.ci }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Teléfono:</p>
                            <p class="font-medium">{{ cliente.telefono || 'No especificado' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Dirección:</p>
                            <p class="font-medium">{{ cliente.direccion || 'No especificada' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Método de Pago -->
                <form @submit.prevent="submit" class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-xl font-semibold mb-4">Método de Pago</h2>

                    <!-- Opción Contado -->
                    <div class="mb-6">
                        <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50"
                            :class="form.tipo_pago === 'contado' ? 'border-blue-500 bg-blue-50' : 'border-gray-300'">
                            <input
                                v-model="form.tipo_pago"
                                type="radio"
                                value="contado"
                                class="mr-3"
                            />
                            <div class="flex-1">
                                <div class="font-semibold text-lg">Pago al Contado</div>
                                <div class="text-sm text-gray-600">Pago inmediato completo</div>
                            </div>
                        </label>
                    </div>

                    <!-- Opción Crédito -->
                    <div v-if="puedeCredito" class="mb-6">
                        <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50"
                            :class="form.tipo_pago === 'credito' ? 'border-blue-500 bg-blue-50' : 'border-gray-300'">
                            <input
                                v-model="form.tipo_pago"
                                type="radio"
                                value="credito"
                                class="mr-3"
                            />
                            <div class="flex-1">
                                <div class="font-semibold text-lg">Pago a Crédito</div>
                                <div class="text-sm text-gray-600">
                                    Crédito disponible: Bs. {{ cliente?.limite_credito || 0 }}
                                </div>
                            </div>
                        </label>

                        <!-- Opciones de cuotas dinámicas -->
                        <div v-if="form.tipo_pago === 'credito' && opcionesCredito.length > 0" class="mt-4 ml-8">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Número de Cuotas:
                            </label>
                            <select
                                v-model="form.numero_cuotas"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                                required
                            >
                                <option value="">Seleccione cuotas</option>
                                <option v-for="cuota in opcionesCredito" :key="cuota" :value="cuota">
                                    {{ cuota }} cuota{{ cuota > 1 ? 's' : '' }} -
                                    Bs. {{ (cart.total / cuota).toFixed(2) }} c/u
                                </option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">
                                Cuota mensual: Bs. {{ form.numero_cuotas ? (cart.total / form.numero_cuotas).toFixed(2) : '0.00' }}
                            </p>
                        </div>
                    </div>

                    <div v-else-if="form.tipo_pago === 'credito'" class="mb-6 ml-8 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-sm text-yellow-800">
                            No eres elegible para compra a crédito. Contacta al administrador para aprobar tu crédito.
                        </p>
                    </div>

                    <!-- Método de pago -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-bold mb-2">Método de Pago *</label>
                        <select
                            v-model="form.metodo_pago"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                            required
                        >
                            <option value="">Seleccione método</option>
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta</option>
                            <option value="qr">QR / Transferencia</option>
                            <option value="cheque">Cheque</option>
                        </select>
                        <span v-if="form.errors.metodo_pago" class="text-red-500 text-sm">{{ form.errors.metodo_pago }}</span>
                    </div>

                    <!-- Botones -->
                    <div class="flex gap-4">
                        <Link
                            href="/cart"
                            class="flex-1 text-center bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium transition-colors"
                        >
                            Volver al Carrito
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing || (form.tipo_pago === 'credito' && !form.numero_cuotas)"
                            class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-medium disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            <span v-if="form.processing">Procesando...</span>
                            <span v-else>Confirmar Compra</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Carrito vacío -->
            <div v-else class="text-center py-16">
                <div class="text-8xl mb-4">🛒</div>
                <h2 class="text-2xl font-semibold text-gray-800 mb-2">Tu carrito está vacío</h2>
                <Link
                    href="/shop"
                    class="inline-block bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-medium mt-4"
                >
                    Ir al Catálogo
                </Link>
            </div>
        </div>
    </ShopLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';

const props = defineProps({
    cart: Object,
    cliente: Object,
    puedeCredito: Boolean,
    opcionesCredito: {
        type: Array,
        default: () => []
    }
});

const form = useForm({
    tipo_pago: 'contado',
    metodo_pago: '',
    numero_cuotas: null
});

const submit = () => {
    form.post('/checkout/process');
};
</script>
