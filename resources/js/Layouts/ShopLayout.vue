<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50">
        <!-- Navigation -->
        <nav
            v-motion-slide-top
            class="bg-white/95 backdrop-blur-md shadow-md sticky top-0 z-50 border-b border-gray-200"
        >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <Link
                            href="/shop"
                            class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent hover:scale-105 transition-transform duration-200"
                        >
                            🍷 Licorería TecnoWeb
                        </Link>
                    </div>

                    <div class="flex items-center space-x-2">
                        <Link
                            href="/shop"
                            class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-blue-50"
                        >
                            Catálogo
                        </Link>

                        <template v-if="$page.props.auth?.user">
                            <Link
                                href="/my-orders"
                                class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-blue-50"
                            >
                                Mis Compras
                            </Link>
                            <Link
                                href="/my-credits"
                                class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-blue-50"
                            >
                                Mis Créditos
                            </Link>
                        </template>

                        <!-- Carrito -->
                        <Link
                            href="/cart"
                            class="relative text-gray-700 hover:text-blue-600 p-2 rounded-lg transition-all duration-200 hover:bg-blue-50"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <Transition
                                enter-active-class="transition ease-out duration-200"
                                enter-from-class="transform scale-0 opacity-0"
                                enter-to-class="transform scale-100 opacity-100"
                                leave-active-class="transition ease-in duration-150"
                                leave-from-class="transform scale-100 opacity-100"
                                leave-to-class="transform scale-0 opacity-0"
                            >
                                <span
                                    v-if="cartCount > 0"
                                    v-motion-pop
                                    class="absolute -top-1 -right-1 bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold shadow-lg"
                                >
                                    {{ cartCount }}
                                </span>
                            </Transition>
                        </Link>

                        <!-- Auth -->
                        <template v-if="!$page.props.auth?.user">
                            <Link
                                href="/login"
                                class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-blue-50"
                            >
                                Iniciar Sesión
                            </Link>
                            <Link
                                href="/register"
                                class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg"
                            >
                                Registrarse
                            </Link>
                        </template>
                        <template v-else>
                            <div class="relative" @click.stop="showUserMenu = !showUserMenu">
                                <button class="flex items-center text-gray-700 hover:text-blue-600 transition-colors duration-200 px-3 py-2 rounded-lg hover:bg-gray-100">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold text-sm mr-2">
                                        {{ userInitials }}
                                    </div>
                                    <span class="mr-2 font-medium">{{ $page.props.auth.user.nombre }}</span>
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': showUserMenu }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <Transition
                                    enter-active-class="transition ease-out duration-100"
                                    enter-from-class="transform opacity-0 scale-95"
                                    enter-to-class="transform opacity-100 scale-100"
                                    leave-active-class="transition ease-in duration-75"
                                    leave-from-class="transform opacity-100 scale-100"
                                    leave-to-class="transform opacity-0 scale-95"
                                >
                                    <div v-show="showUserMenu" @click.stop class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl py-1 z-50 border border-gray-100">
                                        <Link
                                            href="/profile"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150"
                                        >
                                            Mi Perfil
                                        </Link>
                                        <Link
                                            v-if="$page.props.auth.user.rol?.nombre !== 'cliente'"
                                            href="/admin/dashboard"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150"
                                        >
                                            Panel Admin
                                        </Link>
                                        <Link
                                            href="/logout"
                                            method="post"
                                            as="button"
                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors duration-150"
                                        >
                                            Cerrar Sesión
                                        </Link>
                                    </div>
                                </Transition>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Notificación -->
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="transform translate-x-full opacity-0"
            enter-to-class="transform translate-x-0 opacity-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="transform translate-x-0 opacity-100"
            leave-to-class="transform translate-x-full opacity-0"
        >
            <div
                v-if="showNotification"
                v-motion-slide-right
                :class="[
                    'fixed top-4 right-4 z-50 p-4 rounded-lg shadow-xl max-w-sm backdrop-blur-sm',
                    notificationType === 'success'
                        ? 'bg-gradient-to-r from-green-500 to-emerald-500 text-white'
                        : 'bg-gradient-to-r from-red-500 to-rose-500 text-white'
                ]"
            >
                <div class="flex items-center gap-2">
                    <span v-if="notificationType === 'success'" class="text-xl">✅</span>
                    <span v-else class="text-xl">❌</span>
                    <span class="font-medium">{{ notificationMessage }}</span>
                </div>
            </div>
        </Transition>

        <!-- Main Content -->
        <main v-motion-fade>
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-gradient-to-br from-gray-800 to-gray-900 text-white mt-12">
            <div class="max-w-7xl mx-auto px-4 py-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div v-motion-fade>
                        <h3 class="text-lg font-semibold mb-4">Licorería TecnoWeb</h3>
                        <p class="text-gray-400">Tu licorería de confianza online</p>
                    </div>
                    <div v-motion-fade :delay="100">
                        <h3 class="text-lg font-semibold mb-4">Enlaces</h3>
                        <ul class="space-y-2 text-gray-400">
                            <li><Link href="/shop" class="hover:text-white transition-colors duration-200">Catálogo</Link></li>
                            <li><Link href="#" class="hover:text-white transition-colors duration-200">Sobre Nosotros</Link></li>
                            <li><Link href="#" class="hover:text-white transition-colors duration-200">Contacto</Link></li>
                        </ul>
                    </div>
                    <div v-motion-fade :delay="200">
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
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const showUserMenu = ref(false);
const cartCount = ref(0);
const showNotification = ref(false);
const notificationMessage = ref('');
const notificationType = ref('success');
const page = usePage();

const userInitials = computed(() => {
    const nombre = page.props.auth?.user?.nombre || '';
    const parts = nombre.split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return nombre.substring(0, 2).toUpperCase();
});

const closeMenu = (event) => {
    if (!event.target.closest('.relative')) {
        showUserMenu.value = false;
    }
};

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
    document.addEventListener('click', closeMenu);
});

onUnmounted(() => {
    delete window.updateCartCount;
    document.removeEventListener('click', closeMenu);
});
</script>
