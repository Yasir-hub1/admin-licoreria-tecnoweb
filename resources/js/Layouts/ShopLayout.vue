<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <Link href="/shop" class="text-2xl font-bold text-blue-600">
                            🍷 Licorería TecnoWeb
                        </Link>
                    </div>

                    <div class="flex items-center space-x-4">
                        <Link
                            href="/shop"
                            class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium"
                        >
                            Catálogo
                        </Link>

                        <template v-if="$page.props.auth?.user">
                            <Link
                                href="/my-orders"
                                class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium"
                            >
                                Mis Compras
                            </Link>
                            <Link
                                href="/my-credits"
                                class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium"
                            >
                                Mis Créditos
                            </Link>
                        </template>

                        <!-- Carrito -->
                        <Link
                            href="/cart"
                            class="relative text-gray-700 hover:text-blue-600"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span v-if="cartCount > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                {{ cartCount }}
                            </span>
                        </Link>

                        <!-- Auth -->
                        <template v-if="!$page.props.auth?.user">
                            <Link
                                href="/login"
                                class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium"
                            >
                                Iniciar Sesión
                            </Link>
                            <Link
                                href="/register"
                                class="bg-blue-500 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-600"
                            >
                                Registrarse
                            </Link>
                        </template>
                        <template v-else>
                            <div class="relative" @click.stop="showUserMenu = !showUserMenu">
                                <button class="flex items-center text-gray-700 hover:text-blue-600">
                                    <span class="mr-2">{{ $page.props.auth.user.nombre }}</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div v-show="showUserMenu" @click.stop class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                                    <Link
                                        href="/profile"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                    >
                                        Mi Perfil
                                    </Link>
                                    <Link
                                        v-if="$page.props.auth.user.rol?.nombre !== 'cliente'"
                                        href="/admin/dashboard"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                    >
                                        Panel Admin
                                    </Link>
                                    <Link
                                        href="/logout"
                                        method="post"
                                        as="button"
                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                    >
                                        Cerrar Sesión
                                    </Link>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Notificación -->
        <div
            v-if="showNotification"
            :class="[
                'fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm',
                notificationType === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
            ]"
        >
            <div class="flex items-center gap-2">
                <span v-if="notificationType === 'success'">✅</span>
                <span v-else>❌</span>
                <span>{{ notificationMessage }}</span>
            </div>
        </div>

        <!-- Main Content -->
        <main>
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-gray-800 text-white mt-12">
            <div class="max-w-7xl mx-auto px-4 py-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Licorería TecnoWeb</h3>
                        <p class="text-gray-400">Tu licorería de confianza online</p>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Enlaces</h3>
                        <ul class="space-y-2 text-gray-400">
                            <li><Link href="/shop" class="hover:text-white">Catálogo</Link></li>
                            <li><Link href="#" class="hover:text-white">Sobre Nosotros</Link></li>
                            <li><Link href="#" class="hover:text-white">Contacto</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Contacto</h3>
                        <p class="text-gray-400">Email: info@licoreria.com</p>
                        <p class="text-gray-400">Tel: +591 12345678</p>
                    </div>
                </div>
                <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                    <p>&copy; 2025 Licorería TecnoWeb. Todos los derechos reservados.</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const showUserMenu = ref(false);
const cartCount = ref(0);
const showNotification = ref(false);
const notificationMessage = ref('');
const notificationType = ref('success');

const loadCartCount = async () => {
    try {
        const response = await fetch('/cart/count');
        const data = await response.json();
        cartCount.value = data.count || 0;
    } catch (error) {
        console.error('Error al cargar contador del carrito:', error);
    }
};

// Función global para actualizar el contador (llamada desde otros componentes)
window.updateCartCount = loadCartCount;

const showNotif = (message, type = 'success') => {
    notificationMessage.value = message;
    notificationType.value = type;
    showNotification.value = true;
    setTimeout(() => {
        showNotification.value = false;
    }, 3000);
};

// Escuchar cambios en las props de Inertia
const page = usePage();

watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        showNotif(flash.success, 'success');
        loadCartCount();
    }
    if (flash?.error) {
        showNotif(flash.error, 'error');
    }
}, { deep: true, immediate: true });

onMounted(() => {
    loadCartCount();
    // Recargar contador cada vez que se navega
    router.on('finish', () => {
        loadCartCount();
    });
});

onUnmounted(() => {
    delete window.updateCartCount;
});
</script>
