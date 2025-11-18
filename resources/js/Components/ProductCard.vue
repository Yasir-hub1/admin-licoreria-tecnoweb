<template>
    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300">
        <!-- Imagen del producto -->
        <Link :href="`/shop/product/${producto.id}`" class="block">
            <div class="h-48 bg-gradient-to-br from-blue-100 to-purple-100 flex items-center justify-center">
                <div class="text-6xl">🍷</div>
            </div>
        </Link>

        <!-- Información del producto -->
        <div class="p-4">
            <Link :href="`/shop/product/${producto.id}`">
                <h3 class="text-lg font-semibold text-gray-800 hover:text-blue-600 mb-1">
                    {{ producto.nombre }}
                </h3>
            </Link>

            <p class="text-sm text-gray-500 mb-2">{{ producto.marca }}</p>

            <div class="flex items-center justify-between mb-2">
                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                    {{ producto.categoria?.nombre }}
                </span>
                <span class="text-xs text-gray-500">{{ producto.codigo }}</span>
            </div>

            <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                {{ producto.descripcion }}
            </p>

            <div class="flex items-center justify-between">
                <span class="text-2xl font-bold text-green-600">
                    Bs. {{ Number(producto.precio).toFixed(2) }}
                </span>

                <button
                    @click="$emit('add-to-cart', producto.id, 1)"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Agregar
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    producto: {
        type: Object,
        required: true
    }
});

defineEmits(['add-to-cart']);
</script>
