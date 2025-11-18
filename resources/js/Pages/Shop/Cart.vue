<template>
    <ShopLayout>
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Carrito de Compras</h1>

            <div v-if="cart.items.length > 0" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Lista de productos -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow-md">
                        <div v-for="(item, index) in cart.items" :key="index" class="p-6 border-b last:border-b-0">
                            <div class="flex gap-4">
                                <div class="w-24 h-24 bg-gradient-to-br from-blue-100 to-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <div class="text-4xl">🍷</div>
                                </div>

                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-800">{{ item.nombre }}</h3>
                                    <p class="text-sm text-gray-500">{{ item.marca }}</p>
                                    <p class="text-sm text-gray-500">Código: {{ item.codigo }}</p>
                                    <p class="text-lg font-bold text-green-600 mt-2">
                                        Bs. {{ Number(item.precio).toFixed(2) }}
                                    </p>
                                </div>

                                <div class="flex flex-col items-end justify-between">
                                    <button
                                        @click="removeItem(item.id)"
                                        class="text-red-500 hover:text-red-700"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>

                                    <div class="flex items-center border border-gray-300 rounded-lg">
                                        <button
                                            @click="updateQuantity(item.id, item.cantidad - 1)"
                                            class="px-3 py-1 text-gray-600 hover:bg-gray-100"
                                        >
                                            -
                                        </button>
                                        <span class="px-4 py-1 border-x border-gray-300">{{ item.cantidad }}</span>
                                        <button
                                            @click="updateQuantity(item.id, item.cantidad + 1)"
                                            class="px-3 py-1 text-gray-600 hover:bg-gray-100"
                                        >
                                            +
                                        </button>
                                    </div>

                                    <p class="font-semibold text-gray-800">
                                        Bs. {{ (item.precio * item.cantidad).toFixed(2) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resumen del pedido -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-md p-6 sticky top-20">
                        <h2 class="text-xl font-bold text-gray-800 mb-4">Resumen del Pedido</h2>

                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between text-gray-600">
                                <span>Productos ({{ cart.itemCount }})</span>
                                <span>Bs. {{ Number(cart.total).toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Envío</span>
                                <span>Gratis</span>
                            </div>
                            <div class="border-t pt-3 flex justify-between text-xl font-bold">
                                <span>Total</span>
                                <span class="text-green-600">Bs. {{ Number(cart.total).toFixed(2) }}</span>
                            </div>
                        </div>

                        <Link
                            v-if="$page.props.auth?.user"
                            href="/checkout"
                            class="block w-full bg-blue-500 hover:bg-blue-600 text-white text-center px-6 py-3 rounded-lg font-medium transition-colors duration-200 mb-3"
                        >
                            Proceder al Pago
                        </Link>
                        <Link
                            v-else
                            href="/login"
                            class="block w-full bg-blue-500 hover:bg-blue-600 text-white text-center px-6 py-3 rounded-lg font-medium transition-colors duration-200 mb-3"
                        >
                            Iniciar Sesión para Comprar
                        </Link>

                        <Link
                            href="/shop"
                            class="block w-full text-center text-blue-600 hover:text-blue-800 font-medium"
                        >
                            Continuar Comprando
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Carrito vacío -->
            <div v-else class="text-center py-16">
                <div class="text-8xl mb-4">🛒</div>
                <h2 class="text-2xl font-semibold text-gray-800 mb-2">Tu carrito está vacío</h2>
                <p class="text-gray-600 mb-6">Agrega productos para comenzar tu compra</p>
                <Link
                    href="/shop"
                    class="inline-block bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-medium"
                >
                    Ir al Catálogo
                </Link>
            </div>
        </div>
    </ShopLayout>
</template>

<script setup>
import { router, Link } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';

defineProps({
    cart: Object
});

const updateQuantity = (productoId, cantidad) => {
    if (cantidad < 1) {
        removeItem(productoId);
        return;
    }
    router.put('/cart/update', {
        producto_id: productoId,
        cantidad: cantidad
    }, {
        preserveScroll: true,
        onSuccess: () => {
            if (window.updateCartCount) {
                window.updateCartCount();
            }
        }
    });
};

const removeItem = (productoId) => {
    router.delete(`/cart/remove/${productoId}`, {
        preserveScroll: true,
        onSuccess: () => {
            if (window.updateCartCount) {
                window.updateCartCount();
            }
        }
    });
};
</script>
