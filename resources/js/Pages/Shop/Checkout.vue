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

                    <RadioInput
                        v-model="form.tipo_pago"
                        label="Tipo de Pago"
                        name="tipo_pago"
                        :options="tipoPagoOptions"
                        option-value="value"
                        option-label="label"
                        option-description="description"
                        required
                    />

                    <!-- Opciones de cuotas dinámicas -->
                    <div v-if="form.tipo_pago === 'credito' && opcionesCredito.length > 0" class="mt-4 ml-8">
                        <SelectInput
                            v-model="form.numero_cuotas"
                            label="Número de Cuotas"
                            name="numero_cuotas"
                            placeholder="Seleccione cuotas"
                            required
                            :options="opcionesCredito.map(cuota => ({
                                value: cuota,
                                label: `${cuota} cuota${cuota > 1 ? 's' : ''} - Bs. ${(cart.total / cuota).toFixed(2)} c/u`
                            }))"
                            option-value="value"
                            option-label="label"
                            hint="Cuota mensual: Bs. {{ form.numero_cuotas ? (cart.total / form.numero_cuotas).toFixed(2) : '0.00' }}"
                        />
                    </div>

                    <div v-else-if="form.tipo_pago === 'credito'" class="mb-6 ml-8 p-4 rounded-lg border"
                         :class="{
                             'bg-yellow-50 border-yellow-200': !cliente.tieneDocumentosCompletos || cliente.estado_verificacion === 'pendiente',
                             'bg-blue-50 border-blue-200': cliente.estado_verificacion === 'en_revision',
                             'bg-red-50 border-red-200': cliente.estado_verificacion === 'rechazado'
                         }">
                        <div v-if="!cliente.tieneDocumentosCompletos || !cliente.estado_verificacion || cliente.estado_verificacion === 'pendiente'" class="space-y-2">
                            <p class="text-sm font-semibold text-yellow-800">
                                📋 Documentos Pendientes
                            </p>
                            <p class="text-sm text-yellow-700">
                                Para realizar compras a crédito, primero debes subir y verificar tus documentos.
                            </p>
                            <Link href="/verificar-credito" class="inline-block mt-2 px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg text-sm font-medium transition-colors">
                                Subir Documentos
                            </Link>
                        </div>
                        <div v-else-if="cliente.estado_verificacion === 'en_revision'" class="space-y-2">
                            <p class="text-sm font-semibold text-blue-800">
                                ⏳ En Revisión
                            </p>
                            <p class="text-sm text-blue-700">
                                Tus documentos están siendo revisados por un administrador. Te notificaremos cuando se complete la revisión.
                            </p>
                        </div>
                        <div v-else-if="cliente.estado_verificacion === 'rechazado'" class="space-y-2">
                            <p class="text-sm font-semibold text-red-800">
                                ❌ Solicitud Rechazada
                            </p>
                            <p class="text-sm text-red-700" v-if="cliente.observaciones_verificacion">
                                {{ cliente.observaciones_verificacion }}
                            </p>
                            <p class="text-sm text-red-700" v-else>
                                Tu solicitud de crédito fue rechazada. Contacta al administrador para más información.
                            </p>
                            <Link href="/verificar-credito" class="inline-block mt-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition-colors">
                                Actualizar Documentos
                            </Link>
                        </div>
                    </div>

                    <!-- Método de pago -->
                    <SelectInput
                        v-model="form.metodo_pago"
                        label="Método de Pago"
                        name="metodo_pago"
                        placeholder="Seleccione método"
                        required
                        :error="form.errors.metodo_pago"
                        :options="metodoPagoOptions"
                        option-value="value"
                        option-label="label"
                    />

                    <!-- Botones -->
                    <div class="flex gap-4 pt-4">
                        <Link href="/cart" class="flex-1">
                            <Button
                                type="button"
                                variant="outline"
                                full-width
                                size="lg"
                            >
                                Volver al Carrito
                            </Button>
                        </Link>
                        <Button
                            type="submit"
                            :loading="form.processing"
                            :disabled="form.processing || (form.tipo_pago === 'credito' && !form.numero_cuotas)"
                            variant="primary"
                            full-width
                            size="lg"
                        >
                            Confirmar Compra
                        </Button>
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
import { ref, computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import SelectInput from '@/Components/Form/SelectInput.vue';
import RadioInput from '@/Components/Form/RadioInput.vue';
import Button from '@/Components/Button.vue';

const metodoPagoOptions = [
    { value: 'efectivo', label: 'Efectivo' },
    { value: 'tarjeta', label: 'Tarjeta' },
    { value: 'qr', label: 'QR / Transferencia' },
    { value: 'cheque', label: 'Cheque' }
];

const tipoPagoOptions = computed(() => {
    const options = [
        {
            value: 'contado',
            label: 'Pago al Contado',
            description: 'Pago inmediato completo'
        }
    ];

    if (props.puedeCredito) {
        options.push({
            value: 'credito',
            label: 'Pago a Crédito',
            description: `Crédito disponible: Bs. ${props.cliente?.limite_credito || 0}`
        });
    }

    return options;
});

const props = defineProps({
    cart: Object,
    cliente: {
        type: Object,
        default: () => ({
            tieneDocumentosCompletos: false,
            estado_verificacion: null,
            observaciones_verificacion: null
        })
    },
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
