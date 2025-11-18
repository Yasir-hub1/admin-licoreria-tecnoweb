<template>
    <ShopLayout>
        <div class="container mx-auto px-4 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Imagen del producto -->
                <div>
                    <div class="bg-gradient-to-br from-blue-100 to-purple-100 rounded-lg h-96 flex items-center justify-center">
                        <div class="text-9xl">🍷</div>
                    </div>
                </div>

                <!-- Información del producto -->
                <div>
                    <nav class="text-sm mb-4">
                        <Link href="/shop" class="text-blue-600 hover:underline">Catálogo</Link>
                        <span class="mx-2">/</span>
                        <Link
                            v-if="producto.categoria"
                            :href="`/shop/category/${producto.categoria.id}`"
                            class="text-blue-600 hover:underline"
                        >
                            {{ producto.categoria.nombre }}
                        </Link>
                        <span class="mx-2">/</span>
                        <span class="text-gray-500">{{ producto.nombre }}</span>
                    </nav>

                    <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ producto.nombre }}</h1>
                    <p class="text-lg text-gray-600 mb-4">{{ producto.marca }}</p>

                    <div class="flex items-center gap-4 mb-6">
                        <span class="text-4xl font-bold text-green-600">
                            Bs. {{ Number(producto.precio).toFixed(2) }}
                        </span>
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                            {{ producto.categoria?.nombre }}
                        </span>
                    </div>

                    <div class="mb-6">
                        <p class="text-gray-500 text-sm mb-2">Código: {{ producto.codigo }}</p>
                        <p class="text-gray-700 leading-relaxed">
                            {{ producto.descripcion || 'Sin descripción disponible' }}
                        </p>
                    </div>

                    <!-- Cantidad y agregar al carrito -->
                    <div class="bg-gray-50 p-6 rounded-lg mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Cantidad
                        </label>
                        <div class="flex gap-4">
                            <div class="flex items-center border border-gray-300 rounded-lg">
                                <button
                                    @click="cantidad > 1 ? cantidad-- : null"
                                    class="px-4 py-2 text-gray-600 hover:bg-gray-100"
                                >
                                    -
                                </button>
                                <input
                                    v-model.number="cantidad"
                                    type="number"
                                    min="1"
                                    class="w-16 text-center border-x border-gray-300 py-2 focus:outline-none"
                                />
                                <button
                                    @click="cantidad++"
                                    class="px-4 py-2 text-gray-600 hover:bg-gray-100"
                                >
                                    +
                                </button>
                            </div>

                            <button
                                @click="addToCart"
                                class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center gap-2"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Agregar al Carrito
                            </button>
                        </div>
                    </div>

                    <!-- Botón ir al carrito -->
                    <Link
                        href="/cart"
                        class="block text-center text-blue-600 hover:text-blue-800 font-medium"
                    >
                        Ver mi carrito
                    </Link>
                </div>
            </div>

            <!-- Productos relacionados -->
            <div v-if="relacionados.length > 0" class="mt-16">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Productos Relacionados</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                    <ProductCard
                        v-for="prod in relacionados"
                        :key="prod.id"
                        :producto="prod"
                        @add-to-cart="addToCartRelated"
                    />
                </div>
            </div>
        </div>
    </ShopLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import ProductCard from '@/Components/ProductCard.vue';

const props = defineProps({
    producto: Object,
    relacionados: Array
});

const cantidad = ref(1);

const addToCart = () => {
    router.post('/cart/add', {
        producto_id: props.producto.id,
        cantidad: cantidad.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            cantidad.value = 1;
            // Recargar el contador del carrito si existe
            if (window.updateCartCount) {
                window.updateCartCount();
            }
        },
        onError: (errors) => {
            console.error('Error al agregar al carrito:', errors);
        }
    });
};

const addToCartRelated = (productoId) => {
    router.post('/cart/add', {
        producto_id: productoId,
        cantidad: 1
    }, {
        preserveScroll: true,
        onSuccess: () => {
            if (window.updateCartCount) {
                window.updateCartCount();
            }
        },
        onError: (errors) => {
            console.error('Error al agregar al carrito:', errors);
        }
    });
};
</script>
