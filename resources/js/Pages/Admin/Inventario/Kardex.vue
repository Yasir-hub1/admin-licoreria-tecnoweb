<template>
    <MainLayout>
        <div class="container mx-auto px-4 py-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold">Kardex de Producto</h1>
                    <p class="text-gray-600 mt-1">{{ producto?.nombre }} - {{ producto?.codigo }}</p>
                </div>
                <Link href="/admin/inventario" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium">Volver</Link>
            </div>
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50"><tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock Actual</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Glosa</th>
                    </tr></thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="mov in movimientos" :key="mov.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ new Date(mov.fecha).toLocaleDateString('es-ES') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span
                                    class="px-2 py-1 rounded-full text-xs font-medium"
                                    :class="mov.tipo_movimiento === 'INGRESO' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                >
                                    {{ mov.tipo_movimiento }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">{{ mov.cantidad }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">{{ mov.stock_actual }}</td>
                            <td class="px-6 py-4 text-sm">{{ mov.glosa }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </MainLayout>
</template>
<script setup>
import { Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
defineProps({ producto: Object, movimientos: Array });
</script>

