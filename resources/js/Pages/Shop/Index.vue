<template>
    <ShopLayout>
        <div class="container mx-auto px-4 py-8">
            <!-- Header y búsqueda -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-4">
                    {{ categoria_actual ? categoria_actual.nombre : 'Catálogo de Productos' }}
                </h1>

                <!-- Barra de búsqueda y filtros -->
                <div class="flex flex-col md:flex-row gap-4 mb-6">
                    <div class="flex-1">
                        <input
                            v-model="searchForm.search"
                            @input="search"
                            type="text"
                            placeholder="Buscar productos..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                        />
                    </div>

                    <select
                        v-model="searchForm.categoria_id"
                        @change="search"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                    >
                        <option value="">Todas las categorías</option>
                        <option v-for="cat in categorias" :key="cat.id" :value="cat.id">
                            {{ cat.nombre }}
                        </option>
                    </select>
                </div>

                <!-- Filtros de precio -->
                <div class="flex gap-4">
                    <input
                        v-model="searchForm.precio_min"
                        @input="search"
                        type="number"
                        placeholder="Precio mínimo"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                    />
                    <input
                        v-model="searchForm.precio_max"
                        @input="search"
                        type="number"
                        placeholder="Precio máximo"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                    />
                </div>
            </div>

            <!-- Grid de productos -->
            <div v-if="productos.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <ProductCard
                    v-for="producto in productos.data"
                    :key="producto.id"
                    :producto="producto"
                    @add-to-cart="addToCart"
                />
            </div>

            <!-- Sin resultados -->
            <div v-else class="text-center py-12">
                <div class="text-6xl mb-4">🔍</div>
                <p class="text-gray-500 text-lg">No se encontraron productos</p>
            </div>

            <!-- Paginación -->
            <div v-if="productos.links.length > 3" class="mt-8 flex justify-center">
                <nav class="flex gap-2">
                    <Link
                        v-for="(link, index) in productos.links"
                        :key="index"
                        :href="link.url || '#'"
                        v-html="link.label"
                        :class="[
                            'px-4 py-2 border rounded-lg',
                            link.active ? 'bg-blue-500 text-white border-blue-500' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                        ]"
                        preserve-scroll
                    />
                </nav>
            </div>
        </div>
    </ShopLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import ProductCard from '@/Components/ProductCard.vue';

const props = defineProps({
    productos: Object,
    categorias: Array,
    categoria_actual: Object,
    filters: Object
});

const searchForm = reactive({
    search: props.filters?.search || '',
    categoria_id: props.filters?.categoria_id || '',
    precio_min: props.filters?.precio_min || '',
    precio_max: props.filters?.precio_max || '',
});

let searchTimeout = null;

const search = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/shop', searchForm, {
            preserveState: true,
            preserveScroll: true
        });
    }, 500);
};

const addToCart = (productoId, cantidad = 1) => {
    router.post('/cart/add', {
        producto_id: productoId,
        cantidad: cantidad
    }, {
        preserveScroll: true,
        onSuccess: () => {
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
</script>
