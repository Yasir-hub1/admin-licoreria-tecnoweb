<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <Link href="/admin/dashboard" class="text-xl font-bold text-gray-800">
                                🏢 Sistema Tecnoweb
                            </Link>
                        </div>
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-1">
                            <Link
                                href="/admin/dashboard"
                                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-3 py-2 border-b-2 text-sm font-medium"
                            >
                                Dashboard
                            </Link>
                            <Link
                                href="/admin/productos"
                                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-3 py-2 border-b-2 text-sm font-medium"
                            >
                                Productos
                            </Link>
                            <Link
                                href="/admin/categorias"
                                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-3 py-2 border-b-2 text-sm font-medium"
                            >
                                Categorías
                            </Link>
                            <Link
                                href="/admin/clientes"
                                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-3 py-2 border-b-2 text-sm font-medium"
                            >
                                Clientes
                            </Link>
                            <Link
                                href="/admin/ventas"
                                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-3 py-2 border-b-2 text-sm font-medium"
                            >
                                Ventas
                            </Link>
                            <Link
                                href="/admin/compras"
                                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-3 py-2 border-b-2 text-sm font-medium"
                            >
                                Compras
                            </Link>
                            <Link
                                href="/admin/proveedores"
                                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-3 py-2 border-b-2 text-sm font-medium"
                            >
                                Proveedores
                            </Link>
                            <Link
                                href="/admin/inventario"
                                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-3 py-2 border-b-2 text-sm font-medium"
                            >
                                Inventario
                            </Link>
                            <Link
                                href="/admin/creditos"
                                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-3 py-2 border-b-2 text-sm font-medium"
                            >
                                Créditos
                            </Link>
                            <Link
                                v-if="$page.props.auth?.user?.rol?.nombre === 'propietario'"
                                href="/admin/usuarios"
                                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-3 py-2 border-b-2 text-sm font-medium"
                            >
                                Usuarios
                            </Link>
                            <Link
                                v-if="$page.props.auth?.user?.rol?.nombre === 'propietario'"
                                href="/admin/roles"
                                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-3 py-2 border-b-2 text-sm font-medium"
                            >
                                Roles
                            </Link>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <Link
                            href="/shop"
                            class="text-gray-600 hover:text-gray-900 text-sm font-medium"
                        >
                            Ver Tienda
                        </Link>
                        <div class="relative" @click.stop="showUserMenu = !showUserMenu">
                            <button class="flex items-center text-gray-700 hover:text-gray-900">
                                <span class="mr-2">{{ $page.props.auth?.user?.nombre }}</span>
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
                                    href="/logout"
                                    method="post"
                                    as="button"
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                >
                                    Cerrar Sesión
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main>
            <slot />
        </main>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const showUserMenu = ref(false);
</script>
