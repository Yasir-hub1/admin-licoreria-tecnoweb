<template>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center">
        <div class="max-w-4xl mx-auto p-8">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h1 class="text-4xl font-bold text-gray-800 mb-4">
                    Bienvenido al Sistema Tecnoweb
                </h1>
                <p class="text-gray-600 mb-6">
                    Sistema de gestión integral con Laravel, Inertia.js y Vue 3
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <Link
                        :href="route('dashboard')"
                        class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 px-6 rounded-lg text-center transition duration-200"
                    >
                        Ir al Dashboard
                    </Link>

                    <a
                        href="/productos"
                        class="bg-green-500 hover:bg-green-600 text-white font-semibold py-3 px-6 rounded-lg text-center transition duration-200"
                    >
                        Ver Productos
                    </a>
                </div>

                <div class="mt-8 grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                    <div class="p-4 bg-gray-50 rounded">
                        <div class="text-2xl font-bold text-blue-600">14</div>
                        <div class="text-sm text-gray-600">Modelos</div>
                    </div>
                    <div class="p-4 bg-gray-50 rounded">
                        <div class="text-2xl font-bold text-green-600">14</div>
                        <div class="text-sm text-gray-600">Controladores</div>
                    </div>
                    <div class="p-4 bg-gray-50 rounded">
                        <div class="text-2xl font-bold text-purple-600">Vue 3</div>
                        <div class="text-sm text-gray-600">Framework</div>
                    </div>
                    <div class="p-4 bg-gray-50 rounded">
                        <div class="text-2xl font-bold text-red-600">Inertia</div>
                        <div class="text-sm text-gray-600">Stack</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineOptions({
    layout: null
});
</script>
